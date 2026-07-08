const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const pino = require('pino');
const path = require('path');
const fs = require('fs');
const axios = require('axios');
const qrcode = require('qrcode');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, { cors: { origin: '*' } });

const PORT = 3000;
const LARAVEL_WEBHOOK_URL = 'http://localhost:8000/api/webhook/whatsapp';
const SESSIONS_DIR = path.join(__dirname, '../storage/sessions');

const activeSessions = new Map();

const cleanJid = (jid) => {
    if (!jid) return '';
    if (jid.includes('@g.us')) return jid;
    
    const [userWithDevice, domain] = jid.split('@');
    const user = userWithDevice.split(':')[0];
    const finalDomain = domain || 's.whatsapp.net';
    return `${user}@${finalDomain}`;
};

const cleanText = (str) => {
    if (typeof str !== 'string') return str;
    return str.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/g, '');
};

async function startBot(botId) {
    // Tutup koneksi sebelumnya jika ada untuk menghindari duplicate listeners & connection conflict
    if (activeSessions.has(botId)) {
        try {
            const oldSock = activeSessions.get(botId);
            if (oldSock) {
                oldSock.ev.removeAllListeners('connection.update');
                oldSock.ev.removeAllListeners('creds.update');
                oldSock.ev.removeAllListeners('messages.upsert');
                if (oldSock.ws) oldSock.ws.close();
            }
        } catch (e) {
            console.log(`[BOT: ${botId}] Gagal membersihkan koneksi lama:`, e.message);
        }
        activeSessions.delete(botId);
    }

    const sessionPath = path.join(SESSIONS_DIR, botId);
    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    
    let version = [2, 3000, 1015941307]; // Fallback version
    try {
        const versionResult = await fetchLatestBaileysVersion();
        version = versionResult.version;
    } catch (e) {
        console.log(`[BOT: ${botId}] Gagal fetch versi Baileys baru, menggunakan fallback:`, e.message);
    }

    console.log(`[BOT: ${botId}] Starting Engine...`);

    const sock = makeWASocket({
        version,
        logger: pino({ level: 'silent' }),
        printQRInTerminal: false,
        auth: state,
        browser: ['Mac OS', 'Chrome', '10.15.7'] // Wajib untuk dukungan Pairing & QR
    });

    activeSessions.set(botId, sock);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        // Jika QR Code tersedia, pancarkan ke Dashboard
        if (qr) {
            qrcode.toDataURL(qr, (err, url) => {
                if (!err) io.emit('qr', { botId, qrCodeUrl: url });
            });
        }

        if (connection === 'close') {
            const errorReason = lastDisconnect?.error?.output?.statusCode;
            console.log(`[BOT: ${botId}] Connection closed. Reason Code: ${errorReason}. Error:`, lastDisconnect?.error);
            const shouldReconnect = errorReason !== DisconnectReason.loggedOut;
            sendStatusToLaravel(botId, 'inactive');

            if (shouldReconnect) {
                setTimeout(() => startBot(botId), 5000);
            } else {
                console.log(`[BOT: ${botId}] Logged out. Deleting session files...`);
                activeSessions.delete(botId);
                fs.rmSync(sessionPath, { recursive: true, force: true });
                io.emit('status', { botId, status: 'logged_out' });
            }
        } else if (connection === 'open') {
            console.log(`[BOT: ${botId}] Successfully Connected!`);
            io.emit('status', { botId, status: 'connected' });
            sendStatusToLaravel(botId, 'active');
        }
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('messages.upsert', async ({ messages, type }) => {
        if (type === 'notify') {
            for (const msg of messages) {
                if (!msg.message || msg.key.fromMe || msg.key.remoteJid === 'status@broadcast') continue;

                // Cegah download & proses pesan lama (history sync) saat bot baru menyala
                let msgTime = msg.messageTimestamp;
                if (msgTime) {
                    if (typeof msgTime === 'object') {
                        msgTime = msgTime.low !== undefined ? msgTime.low : (typeof msgTime.toNumber === 'function' ? msgTime.toNumber() : parseInt(msgTime.toString()));
                    }
                    msgTime = Number(msgTime);
                    if (!isNaN(msgTime) && (Math.floor(Date.now() / 1000) - msgTime) > 60) {
                        continue;
                    }
                }

                const jid = cleanJid(msg.key.remoteJid);
                const sender = cleanJid(msg.key.participant || msg.key.remoteJid);
                const messageType = Object.keys(msg.message)[0];
                const text = msg.message?.conversation || 
                             msg.message?.extendedTextMessage?.text || 
                             msg.message?.imageMessage?.caption || 
                             msg.message?.videoMessage?.caption || 
                             '';
                const quotedMessage = msg.message?.extendedTextMessage?.contextInfo?.quotedMessage;
                const quotedText = quotedMessage?.conversation || quotedMessage?.extendedTextMessage?.text || '';

                let mediaPath = null;
                const hasImage = !!msg.message?.imageMessage;
                const isPromoCommand = text.toLowerCase().trim().startsWith('!addpromo') || 
                                       text.toLowerCase().trim().startsWith('!addevent');
                if (hasImage && isPromoCommand) {
                    try {
                        const { downloadContentFromMessage } = require('@whiskeysockets/baileys');
                        const imageMessage = msg.message.imageMessage;
                        const stream = await downloadContentFromMessage(imageMessage, 'image');
                        let buffer = Buffer.from([]);
                        for await (const chunk of stream) {
                            buffer = Buffer.concat([buffer, chunk]);
                        }
                        
                        const uploadsDir = path.join(__dirname, '../public/uploads/promos');
                        if (!fs.existsSync(uploadsDir)) {
                            fs.mkdirSync(uploadsDir, { recursive: true });
                        }
                        
                        const fileName = `${Date.now()}_${Math.random().toString(36).substring(2, 7)}.jpg`;
                        const filePath = path.join(uploadsDir, fileName);
                        fs.writeFileSync(filePath, buffer);
                        mediaPath = `/uploads/promos/${fileName}`;
                        console.log(`[BOT: ${botId}] Berhasil mengunduh gambar ke: ${mediaPath}`);
                    } catch (err) {
                        console.error('Gagal mengunduh gambar WhatsApp:', err);
                    }
                }

                sendWebhookToLaravel({ botId, jid, sender, pushName: msg.pushName, text, messageType, quotedText, mediaPath });
            }
        }
    });

    return sock;
}

async function sendStatusToLaravel(botId, status) {
    try { 
        await axios.post(`${LARAVEL_WEBHOOK_URL}/status`, { bot_id: botId, status: status, secret: 'RdsBotSecret2026' }); 
    } catch (e) {
        console.error(`[BOT: ${botId}] Gagal mengirim status ke Laravel:`, e.message);
    }
}

async function sendWebhookToLaravel(payload) {
    try {
        if (payload.raw) delete payload.raw;
        await axios.post(`${LARAVEL_WEBHOOK_URL}/message`, { ...payload, secret: 'RdsBotSecret2026' });
    } catch (e) {
        console.error(`[BOT: ${payload.botId}] Gagal mengirim webhook pesan ke Laravel:`, e.message);
        if (e.response) {
            console.error(`[BOT: ${payload.botId}] Response dari Laravel:`, e.response.status, e.response.data);
        }
    }
}

app.post('/api/bots/start', async (req, res) => {
    const { botId } = req.body;
    if (activeSessions.has(botId)) return res.status(200).json({ message: 'Running' });
    try {
        await startBot(botId);
        res.status(200).json({ message: `Started` });
    } catch (error) { res.status(500).json({ error: error.message }); }
});

// Endpoint Khusus untuk Request Kode Tautan Manual
app.post('/api/bots/pairing', async (req, res) => {
    const { botId, phoneNumber } = req.body;
    const sock = activeSessions.get(botId);
    
    if (!sock) return res.status(404).json({ error: 'Bot belum menyala. Gagal membuat kode.' });

    try {
        console.log(`[BOT: ${botId}] Meminta kode untuk nomor ${phoneNumber}`);
        let formattedNumber = phoneNumber.replace(/[^0-9]/g, '');
        let code = await sock.requestPairingCode(formattedNumber);
        
        // Memformat kode menjadi ABCD-1234
        code = code?.match(/.{1,4}/g)?.join("-") || code;
        io.emit('pairing_code', { botId, code });
        res.status(200).json({ success: true, code });
    } catch (error) {
        io.emit('pairing_code_error', { botId, message: 'Gagal membuat kode dari server Meta.' });
        res.status(500).json({ error: error.message });
    }
});

app.post('/api/bots/stop', async (req, res) => {
    const { botId } = req.body;
    const sock = activeSessions.get(botId);
    if (sock) {
        sock.ws.close();
        activeSessions.delete(botId);
        sendStatusToLaravel(botId, 'inactive');
        io.emit('status', { botId, status: 'inactive' });
        res.status(200).json({ message: `Stopped` });
    } else { res.status(404).json({ error: 'Not running' }); }
});

app.post('/api/messages/send', async (req, res) => {
    const { botId, jid, text, mentions, imageUrl } = req.body;
    console.log(`[BOT: ${botId}] Mengirim pesan ke JID: ${jid}. Teks:`, JSON.stringify(text));

    const sock = activeSessions.get(botId);
    if (!sock) return res.status(404).json({ error: `Not active` });
    try {
        const sanitizedText = cleanText(text);
        const messageOptions = {};
        if (imageUrl) {
            let imageSource = imageUrl;
            if (imageUrl.startsWith('/')) {
                imageSource = path.join(__dirname, '../public', imageUrl);
            }
            messageOptions.image = { url: imageSource };
            messageOptions.caption = sanitizedText;
        } else {
            messageOptions.text = sanitizedText;
        }

        if (mentions && Array.isArray(mentions)) {
            messageOptions.mentions = mentions;
        }
        await sock.sendMessage(jid, messageOptions);
        res.status(200).json({ success: true });
    } catch (error) { 
        console.error(`[BOT: ${botId}] Gagal mengirim pesan ke ${jid}:`, error.message);
        res.status(500).json({ error: error.message }); 
    }
});

server.listen(PORT, () => {
    console.log(`[RDS-BOT] Engine Baileys WhatsApp running on port ${PORT}`);
    if (fs.existsSync(SESSIONS_DIR)) {
        fs.readdirSync(SESSIONS_DIR).forEach(botId => {
            const sessionPath = path.join(SESSIONS_DIR, botId);
            if (fs.statSync(sessionPath).isDirectory()) startBot(botId);
        });
    }
});