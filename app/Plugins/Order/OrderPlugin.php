<?php

namespace App\Plugins\Order;

use App\Plugins\PluginInterface;
use App\Modules\MessageRouter;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHistory;

class OrderPlugin implements PluginInterface
{
    public function getCommands(): array
    {
        return ['!form', 'form', 'ps', 'dn', 'cn', 'rf', '!history', '!grupid', '!help', '!list', '!promo', '!additem', '!addpromo', '!addevent', '!listdelete', '!listupdate', '!editform', '!listorder', '!order', '!promodelete', '!eventdelete'];
    }

    public function process($botId, $jid, $sender, $text, $pushName, $quotedText = '', $mediaPath = null)
    {
        $textLower = strtolower(trim($text));

        if ($textLower === '!form') {
            $this->sendForm($botId, $jid);
        } 
        elseif (str_starts_with($textLower, 'form')) {
            // Validasi apakah ini form terisi (mengandung field item/nama)
            if (preg_match('/(?:Nama\s*Item|Item)\s*:/i', $text)) {
                $this->processFilledForm($botId, $jid, $sender, $text, $pushName);
            }
        }
        elseif (in_array($textLower, ['ps', 'dn', 'cn', 'rf'])) {
            $this->updateOrderStatus($botId, $jid, $sender, $textLower, $quotedText);
        }
        // TAMBAHAN: Logika untuk Command !history
        elseif (str_starts_with($textLower, '!history')) {
            $this->checkHistory($botId, $jid, $textLower);
        }
        // TAMBAHAN: Logika untuk Command !grupid
        elseif ($textLower === '!grupid') {
            $this->sendGroupId($botId, $jid);
        }
        // TAMBAHAN: Logika untuk Command !help
        elseif (str_starts_with($textLower, '!help')) {
            $this->sendHelp($botId, $jid, $textLower);
        }
        // TAMBAHAN: Logika untuk Command !listdelete
        elseif (str_starts_with($textLower, '!listdelete')) {
            $this->processListDeleteCommand($botId, $jid, $text);
        }
        // TAMBAHAN: Logika untuk Command !listupdate
        elseif (str_starts_with($textLower, '!listupdate')) {
            $this->processListUpdateCommand($botId, $jid, $text);
        }
        // TAMBAHAN: Logika untuk Command !listorder
        elseif (str_starts_with($textLower, '!listorder')) {
            $this->processListOrderCommand($botId, $jid);
        }
        // TAMBAHAN: Logika untuk Command !list
        elseif (str_starts_with($textLower, '!list')) {
            $this->sendItemList($botId, $jid, $text);
        }
        // TAMBAHAN: Logika untuk Command !promo
        elseif ($textLower === '!promo') {
            $this->sendPromoList($botId, $jid);
        }
        // TAMBAHAN: Logika untuk Command !additem
        elseif (str_starts_with($textLower, '!additem')) {
            $this->processAddItemCommand($botId, $jid, $text);
        }
        // TAMBAHAN: Logika untuk Command !addpromo / !addevent
        elseif (str_starts_with($textLower, '!addpromo') || str_starts_with($textLower, '!addevent')) {
            $this->processAddPromoCommand($botId, $jid, $text, $mediaPath);
        }
        // TAMBAHAN: Logika untuk Command !editform
        elseif (str_starts_with($textLower, '!editform')) {
            $this->processEditFormCommand($botId, $jid, $text);
        }
        // TAMBAHAN: Logika untuk Command !order
        elseif (str_starts_with($textLower, '!order')) {
            $this->processOrderDetailCommand($botId, $jid, $text);
        }
        // TAMBAHAN: Logika untuk Command !promodelete / !eventdelete
        elseif (str_starts_with($textLower, '!promodelete') || str_starts_with($textLower, '!eventdelete')) {
            $this->processPromoDeleteCommand($botId, $jid, $text);
        }
    }

    private function sendForm($botId, $jid)
    {
        $reply = \App\Models\Bot::where('bot_id', $botId)->value('form_template');
        if (empty($reply)) {
            $reply = "FORM ORDER\nNick : \nID : \nNo HP : \nNama Item : \nJumlah : \nCatatan : \n\n*(Silakan copy pesan ini, isi datanya, lalu kirimkan kembali)*";
        }
        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    private function processFilledForm($botId, $jid, $sender, $text, $pushName)
    {
        preg_match('/Nick(?:name)?\s*:\s*(.*)/i', $text, $nickMatch);
        preg_match('/ID\s*:\s*(.*)/i', $text, $idMatch);
        preg_match('/(?:No\s*HP|Nomor\s*HP|Phone)\s*:\s*(.*)/i', $text, $phoneMatch);
        preg_match('/(?:Nama\s*Item|Item)\s*:\s*(.*)/i', $text, $itemMatch);
        preg_match('/(?:Jumlah|Qty)\s*:\s*(.*)/i', $text, $qtyMatch);
        preg_match('/(?:Catatan|Note)\s*:\s*(.*)/i', $text, $noteMatch);

        $nick = isset($nickMatch[1]) ? trim($nickMatch[1]) : '-';
        $gameId = isset($idMatch[1]) ? trim($idMatch[1]) : '-';
        $hp = isset($phoneMatch[1]) ? trim($phoneMatch[1]) : '-';
        $item = isset($itemMatch[1]) ? trim($itemMatch[1]) : '-';
        $qty = isset($qtyMatch[1]) ? (int)trim($qtyMatch[1]) : 1;
        $note = isset($noteMatch[1]) ? trim($noteMatch[1]) : '';

        if ($item == '-' || $item == '') {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal memproses pesanan. Pastikan baris 'Nama Item :' atau 'Item :' terisi dengan benar.");
            return;
        }

        // Simpan customer menggunakan $sender (JID asli pengirim)
        $customer = Customer::updateOrCreate(
            ['phone_number' => $sender],
            ['name' => $pushName, 'game_nick' => $nick, 'game_id' => $gameId]
        );

        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Cari order terakhir khusus untuk bot ini
        $lastOrder = Order::where('bot_id', $botIdDb)->latest('id')->first();
        $nextNumber = 1;
        if ($lastOrder && $lastOrder->ticket_id) {
            preg_match('/\d+/', $lastOrder->ticket_id, $matches);
            if (isset($matches[0])) {
                $nextNumber = (int)$matches[0] + 1;
            }
        }
        $ticketId = '#ORD' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Buat Order dengan menyertakan origin_jid
        $order = Order::create([
            'bot_id' => $botIdDb,
            'customer_id' => $customer->id,
            'origin_jid' => $jid,
            'ticket_id' => $ticketId,
            'item_name' => $item,
            'qty' => $qty,
            'notes' => "Nick: $nick | ID: $gameId | HP: $hp | Catatan: $note",
            'status' => 'pending'
        ]);

        OrderHistory::create(['order_id' => $order->id, 'status' => 'pending', 'note' => 'Order Dibuat via WA']);

        // Balas resi ke asal chat ($jid)
        if ($jid !== $sender) {
            // Group Chat - tag customer
            $phone = explode('@', $sender)[0];
            $receipt = "✅ *PESANAN DITERIMA*\n\nPelanggan: @$phone\nTicket ID: *$ticketId*\nItem: *$item (x$qty)*\nStatus: ⏳ *Menunggu Proses*\n\nPesanan Anda sedang masuk ke antrean admin kami.";
            MessageRouter::sendMessage($botId, $jid, $receipt, [$sender]);
        } else {
            // Personal Chat (PM)
            $receipt = "✅ *PESANAN DITERIMA*\n\nTicket ID: *$ticketId*\nItem: *$item (x$qty)*\nStatus: ⏳ *Menunggu Proses*\n\nPesanan Anda sedang masuk ke antrean admin kami.";
            MessageRouter::sendMessage($botId, $jid, $receipt);
        }

        // Cari grup admin (tipe seller) untuk bot ini
        $adminGroup = \App\Models\Group::where('bot_id', $botIdDb)->where('type', 'seller')->first();
        if ($adminGroup) {
            // Identifikasi asal pesanan
            $source = "PM / Japri";
            if ($jid !== $sender) {
                $groupRecord = \App\Models\Group::where('group_jid', $jid)->first();
                $source = $groupRecord ? "Grup: " . $groupRecord->group_name : "Grup (" . explode('@', $jid)[0] . ")";
            }

            $adminNotification = "🔔 *PESANAN BARU MASUK*\n\n" .
                "Ticket ID: *$ticketId*\n" .
                "Pelanggan: *$pushName* (@" . explode('@', $sender)[0] . ")\n" .
                "Sumber: *$source*\n\n" .
                "Item: *$item (x$qty)*\n" .
                "Detail:\n" .
                "- Nick: $nick\n" .
                "- ID: $gameId\n" .
                "- HP: $hp\n" .
                "- Catatan: $note\n\n" .
                "*(Balas pesan ini dengan command: ps / dn / cn / rf untuk memproses)*";

            MessageRouter::sendMessage($botId, $adminGroup->group_jid, $adminNotification);
        } else {
            \Illuminate\Support\Facades\Log::warning("Bot $botId tidak memiliki Admin Group (seller) yang terdaftar.");
        }
    }

    private function updateOrderStatus($botId, $jid, $sender, $command, $quotedText)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Cek apakah pengirim/chat berasal dari grup admin (seller)
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) {
            // Abaikan jika bukan dari grup admin
            return;
        }

        preg_match('/#ORD\d+/', $quotedText, $matches);
        
        if (empty($matches)) {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal mengupdate status. Reply struk yang memiliki Ticket ID.");
            return;
        }

        $ticketId = $matches[0];
        $order = Order::where('ticket_id', $ticketId)->first();
        if (!$order) return;

        $statusMap = [
            'ps' => ['db_status' => 'processing', 'icon' => '⏳', 'title' => 'PESANAN SEDANG DIPROSES', 'desc' => 'Estimasi: 1 - 15 menit'],
            'dn' => ['db_status' => 'done', 'icon' => '🎉', 'title' => 'PESANAN BERHASIL', 'desc' => 'Silakan cek akun Anda.'],
            'cn' => ['db_status' => 'cancelled', 'icon' => '❌', 'title' => 'PESANAN DIBATALKAN', 'desc' => 'Hubungi admin.'],
            'rf' => ['db_status' => 'revised', 'icon' => '🔄', 'title' => 'PESANAN DIREVISI', 'desc' => 'Admin sedang mengecek kembali.']
        ];

        $updateData = $statusMap[$command];

        $order->update(['status' => $updateData['db_status']]);
        OrderHistory::create([
            'order_id' => $order->id, 
            'status' => $updateData['db_status'], 
            'note' => "Diupdate via WA oleh Admin"
        ]);

        $customer = Customer::find($order->customer_id);
        if ($customer) {
            $originJid = $order->origin_jid ?? $customer->phone_number;
            
            if (str_contains($originJid, '@g.us')) {
                // Group - tag customer
                $phone = explode('@', $customer->phone_number)[0];
                $notifyText = "{$updateData['icon']} *{$updateData['title']}*\n\n" .
                    "Pelanggan: @$phone\n" .
                    "Ticket ID: *$ticketId*\n" .
                    "Item: *{$order->item_name}*\n\n" .
                    "_{$updateData['desc']}_";
                
                MessageRouter::sendMessage($botId, $originJid, $notifyText, [$customer->phone_number]);
            } else {
                // PM
                $notifyText = "{$updateData['icon']} *{$updateData['title']}*\n\n" .
                    "Ticket ID: *$ticketId*\n" .
                    "Item: *{$order->item_name}*\n\n" .
                    "_{$updateData['desc']}_";
                
                MessageRouter::sendMessage($botId, $originJid, $notifyText);
            }
        }

        MessageRouter::sendMessage($botId, $jid, "✅ Status *$ticketId* diubah menjadi *" . strtoupper($updateData['db_status']) . "*");
    }

    // FUNGSI BARU: CEK HISTORY
    private function checkHistory($botId, $jid, $text)
    {
        // Cari format #ORDXXXXX di dalam teks
        preg_match('/#ord\d+/i', $text, $matches);
        
        if (empty($matches)) {
            MessageRouter::sendMessage($botId, $jid, "❌ Format salah. Gunakan: *!history #ORD00001*");
            return;
        }

        $ticketId = strtoupper($matches[0]);
        $order = Order::where('ticket_id', $ticketId)->first();

        if (!$order) {
            MessageRouter::sendMessage($botId, $jid, "❌ Ticket *$ticketId* tidak ditemukan di sistem.");
            return;
        }

        $histories = OrderHistory::where('order_id', $order->id)->orderBy('created_at', 'asc')->get();
        
        $reply = "📜 *RIWAYAT STATUS ORDER*\nTicket: *$ticketId*\nItem: *{$order->item_name}*\n\n";

        foreach ($histories as $h) {
            $time = $h->created_at->format('H:i');
            $status = strtoupper($h->status);
            $reply .= "[$time] - *$status*\n";
            if ($h->note) {
                $reply .= "└ {$h->note}\n\n";
            }
        }

        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    // FUNGSI BARU: SEND GROUP ID
    private function sendGroupId($botId, $jid)
    {
        if (str_contains($jid, '@g.us')) {
            $reply = "🆔 *ID GRUP INI:*\n`$jid`\n\n*(Salin ID di atas untuk didaftarkan di website dashboard)*";
        } else {
            $reply = "❌ Perintah `!grupid` hanya dapat digunakan di dalam Grup WhatsApp.";
        }
        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    // FUNGSI BARU: SEND HELP
    private function sendHelp($botId, $jid, $text)
    {
        $text = strtolower(trim($text));
        
        if ($text === '!help pembeli') {
            $reply = "📖 *PANDUAN PEMBELI (BUYER HELP)*\n\n" .
                "Berikut adalah daftar perintah yang dapat digunakan oleh Pembeli:\n\n" .
                "1. *!list*\n" .
                "   Melihat daftar item jualan yang tersedia beserta harganya.\n" .
                "   _Contoh: ketik *!list*_\n\n" .
                "2. *!promo*\n" .
                "   Melihat daftar promo atau event terbaru yang sedang berlangsung.\n" .
                "   _Contoh: ketik *!promo*_\n\n" .
                "3. *!form*\n" .
                "   Untuk meminta formulir pesanan baru.\n" .
                "   _Contoh: ketik *!form*_\n\n" .
                "4. *form ... (form terisi)*\n" .
                "   Format untuk melakukan pemesanan (diisi lengkap lalu dikirim).\n" .
                "   _Contoh: salin, isi form, lalu kirim kembali_\n\n" .
                "5. *!history #ORD00001*\n" .
                "   Untuk mengecek riwayat perjalanan status pesanan Anda.\n" .
                "   _Contoh: ketik *!history #ORD00001*_";
        } 
        elseif ($text === '!help penjual') {
            $reply = "🛠️ *PANDUAN PENJUAL (ADMIN HELP)*\n\n" .
                "Perintah berikut hanya dapat dilakukan oleh Admin di dalam *Grup Admin (Command Center)* yang terdaftar:\n\n" .
                "*--- MONITORING & RINCIAN ORDER ---*\n" .
                "1. *!listorder*\n" .
                "   Melihat tabel daftar seluruh orderan aktif yang belum selesai.\n" .
                "   _Contoh: ketik *!listorder*_\n\n" .
                "2. *!order [ticket_id]*\n" .
                "   Melihat rincian detail pesanan dan data formulir pembeli.\n" .
                "   _Contoh: *!order #ORD00001*_\n\n" .
                "*--- PENGELOLAAN STATUS ORDER (Reply Struk) ---*\n" .
                "3. *ps* (Processing) : Mengubah status menjadi *SEDANG DIPROSES*.\n" .
                "   _Cara: Reply tiket order, ketik *ps*_\n" .
                "4. *dn* (Done) : Mengubah status menjadi *SELESAI*.\n" .
                "   _Cara: Reply tiket order, ketik *dn*_\n" .
                "5. *cn* (Cancelled) : Mengubah status menjadi *DIBATALKAN*.\n" .
                "   _Cara: Reply tiket order, ketik *cn*_\n" .
                "6. *rf* (Revised) : Mengubah status menjadi *DIREVISI*.\n" .
                "   _Cara: Reply tiket order, ketik *rf*_\n\n" .
                "*--- PENGELOLAAN DATA JUALAN ---*\n" .
                "7. *!additem Nama Item | Deskripsi*\n" .
                "   Menambahkan item jualan baru secara langsung.\n" .
                "   _Contoh: *!additem Capcut | Harga sharing 6k...*_\n\n" .
                "8. *!listupdate Nama Item | Deskripsi Baru*\n" .
                "   Memperbarui rincian harga/paket item jualan.\n" .
                "   _Contoh: *!listupdate Capcut | Paket Sharing Naik Harga...*_\n\n" .
                "9. *!listdelete Nama Item*\n" .
                "   Menghapus item jualan dari list.\n" .
                "   _Contoh: *!listdelete Capcut*_\n\n" .
                "10. *!editform [Isi Template Form]*\n" .
                "   Mengubah template form order (!form) pembeli.\n" .
                "   _Contoh: *!editform FORM ORDER RD\nNama:\nID:*_\n\n" .
                "11. *!addevent Judul | Deskripsi*\n" .
                "   Menambahkan Promo/Event baru secara langsung.\n" .
                "   _Contoh: *!addevent PROMO IMLEK | Cashback 10%*_\n\n" .
                "*--- UTILITY ---*\n" .
                "12. *!grupid*\n" .
                "   Menampilkan ID JID grup WhatsApp ini.\n" .
                "   _Contoh: ketik *!grupid*_";
        } 
        else {
            $reply = "💡 *RDS-BOT HELP CENTER*\n\n" .
                "Silakan ketik perintah bantuan berikut:\n" .
                "- *!help pembeli* : Untuk melihat panduan pembeli.\n" .
                "- *!help penjual* : Untuk melihat panduan admin/penjual.";
        }

        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    // FUNGSI BARU: SEND ITEM LIST
    private function sendItemList($botId, $jid, $text)
    {
        $textTrim = trim($text);
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');

        if (strtolower($textTrim) === '!list') {
            // Tampilkan DAFTAR NAMA PRODUK saja (Global + milik bot ini)
            $items = \App\Models\Item::where('is_active', true)
                ->where(function($q) use ($botIdDb) {
                    $q->whereNull('bot_id')->orWhere('bot_id', $botIdDb);
                })
                ->get();

            if ($items->isEmpty()) {
                $reply = "🛍️ *DAFTAR PRODUK*\n\nSaat ini belum ada produk aktif yang tersedia untuk dijual. Hubungi admin.";
            } else {
                $reply = "🛍️ *DAFTAR PRODUK*\n\nBerikut adalah produk yang tersedia:\n\n";
                $i = 1;
                foreach ($items as $item) {
                    $reply .= "$i. *{$item->name}*\n";
                    $i++;
                }
                $reply .= "\nKetik *!list (nama produk)* untuk melihat daftar harga, paket, dan detail selengkapnya.\n_Contoh: ketik *!list {$items->first()->name}*_";
            }
        } else {
            // User mencari detail produk, ketik: !list (nama produk)
            $query = $this->cleanQuery(substr($textTrim, 5)); // Potong "!list" dan bersihkan kurung

            // Cari item berdasarkan nama (case-insensitive)
            $item = \App\Models\Item::where('is_active', true)
                ->where(function($q) use ($botIdDb) {
                    $q->whereNull('bot_id')->orWhere('bot_id', $botIdDb);
                })
                ->where('name', 'LIKE', '%' . $query . '%')
                ->first();

            if ($item) {
                $reply = "🛍️ *DETAIL PRODUK: " . strtoupper($item->name) . "*\n\n" .
                         ($item->description ?? "_Tidak ada informasi detail untuk produk ini._");
            } else {
                $reply = "❌ Produk *\"$query\"* tidak ditemukan.\n\nKetik *!list* untuk melihat daftar produk yang tersedia.";
            }
        }

        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    // FUNGSI BARU: SEND PROMO LIST
    private function sendPromoList($botId, $jid)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');

        $promos = \App\Models\Promo::where('is_active', true)
            ->where(function($q) use ($botIdDb) {
                $q->whereNull('bot_id')->orWhere('bot_id', $botIdDb);
            })
            ->get();

        if ($promos->isEmpty()) {
            $reply = "🎁 *PROMO & EVENT*\n\nSaat ini belum ada promo atau event aktif yang sedang berlangsung. Hubungi admin.";
            MessageRouter::sendMessage($botId, $jid, $reply);
        } else {
            foreach ($promos as $i => $promo) {
                $caption = "🎁 *PROMO/EVENT " . ($i + 1) . ": {$promo->title}*\n\n" .
                           "_{$promo->description}_";

                if ($promo->image_path) {
                    MessageRouter::sendMessage($botId, $jid, $caption, [], $promo->image_path);
                } else {
                    MessageRouter::sendMessage($botId, $jid, $caption);
                }
            }
        }
    }

    // FUNGSI BARU: ADD ITEM VIA WA (ADMIN ONLY)
    private function processAddItemCommand($botId, $jid, $text)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Validasi Admin Group
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) return;

        // Format: !additem Nama Item | Deskripsi (dapat berupa multiline harga/paket)
        $paramsStr = trim(substr($text, 8)); // Hilangkan !additem
        
        // Pecah dengan limit = 2 agar seluruh teks deskripsi multiline tetap utuh
        $parts = explode('|', $paramsStr, 2);

        $name = trim($parts[0]);
        $description = isset($parts[1]) ? trim($parts[1]) : null;

        if (empty($name)) {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal. Pastikan Nama Item terisi.\nFormat:\n*!additem Nama Item | Detail/Harga*");
            return;
        }

        \App\Models\Item::create([
            'bot_id' => $botIdDb,
            'name' => $name,
            'price' => 0, // Default 0 karena daftar harga lengkap ada di deskripsi
            'description' => $description,
            'is_active' => true
        ]);

        $reply = "✅ *PRODUK BERHASIL DITAMBAHKAN*\n\nNama Produk: *$name*";
        if ($description) {
            $reply .= "\n\n*Detail Harga & Paket:*\n$description";
        }

        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    // FUNGSI BARU: ADD PROMO/EVENT VIA WA (ADMIN ONLY)
    private function processAddPromoCommand($botId, $jid, $text, $mediaPath = null)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Validasi Admin Group
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) return;

        // Format: !addpromo Judul | Deskripsi  ATAU  !addevent Judul | Deskripsi
        $isEvent = str_starts_with(strtolower($text), '!addevent');
        $prefixLength = $isEvent ? 9 : 9; // Panjang string command prefix

        $paramsStr = trim(substr($text, $prefixLength));
        $parts = explode('|', $paramsStr);

        if (count($parts) < 2) {
            $cmd = $isEvent ? '!addevent' : '!addpromo';
            MessageRouter::sendMessage($botId, $jid, "❌ Format salah. Gunakan:\n*$cmd Judul | Deskripsi Promo/Event*");
            return;
        }

        $title = trim($parts[0]);
        $description = trim($parts[1]);

        if (empty($title) || empty($description)) {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal. Pastikan Judul dan Deskripsi terisi.");
            return;
        }

        \App\Models\Promo::create([
            'bot_id' => $botIdDb,
            'title' => $title,
            'description' => $description,
            'image_path' => $mediaPath,
            'is_active' => true
        ]);

        $reply = "✅ *PROMO/EVENT BERHASIL DITAMBAHKAN*\n\nJudul: *$title*\nDeskripsi: _$description_";
        if ($mediaPath) {
            $reply .= "\n🖼️ _(Gambar Terlampir)_";
            MessageRouter::sendMessage($botId, $jid, $reply, [], $mediaPath);
        } else {
            MessageRouter::sendMessage($botId, $jid, $reply);
        }
    }

    // FUNGSI BARU: DELETE ITEM VIA WA (ADMIN ONLY)
    private function processListDeleteCommand($botId, $jid, $text)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Validasi Admin Group
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) return;

        $query = $this->cleanQuery(substr($text, 11)); // Hilangkan !listdelete dan bersihkan kurung
        if (empty($query)) {
            MessageRouter::sendMessage($botId, $jid, "❌ Format salah. Gunakan: *!listdelete (nama produk)*");
            return;
        }

        // Cari item berdasarkan nama
        $item = \App\Models\Item::where('name', 'LIKE', '%' . $query . '%')->first();
        if (!$item) {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal. Produk *\"$query\"* tidak ditemukan.");
            return;
        }

        $itemName = $item->name;
        $item->delete();

        MessageRouter::sendMessage($botId, $jid, "✅ *PRODUK BERHASIL DIHAPUS*\n\nProduk *\"$itemName\"* telah dihapus dari daftar penjualan.");
    }

    // FUNGSI BARU: UPDATE ITEM VIA WA (ADMIN ONLY)
    private function processListUpdateCommand($botId, $jid, $text)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Validasi Admin Group
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) return;

        $paramsStr = trim(substr($text, 11)); // Hilangkan !listupdate
        
        // Pecah menggunakan limit = 2
        $parts = explode('|', $paramsStr, 2);

        if (count($parts) < 2) {
            MessageRouter::sendMessage($botId, $jid, "❌ Format salah. Gunakan:\n*!listupdate Nama Produk | Deskripsi/Harga Baru*");
            return;
        }

        $query = $this->cleanQuery($parts[0]); // Bersihkan tanda kurung dari nama produk
        $newDescription = trim($parts[1]);

        if (empty($query) || empty($newDescription)) {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal. Pastikan nama produk dan isi deskripsi baru terisi.");
            return;
        }

        // Cari item berdasarkan nama
        $item = \App\Models\Item::where('name', 'LIKE', '%' . $query . '%')->first();
        if (!$item) {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal. Produk *\"$query\"* tidak ditemukan.");
            return;
        }

        $item->update([
            'description' => $newDescription
        ]);

        MessageRouter::sendMessage($botId, $jid, "✅ *PRODUK BERHASIL DIPERBARUI*\n\nProduk: *{$item->name}*\n\n*Detail Baru:*\n$newDescription");
    }

    // HELPER: CLEAN QUERY FROM PARENTHESES OR BRACKETS
    private function cleanQuery($query)
    {
        $query = trim($query);
        
        // Bersihkan tanda kurung biasa ()
        if (str_starts_with($query, '(') && str_ends_with($query, ')')) {
            $query = substr($query, 1, -1);
        }
        // Bersihkan tanda kurung siku []
        elseif (str_starts_with($query, '[') && str_ends_with($query, ']')) {
            $query = substr($query, 1, -1);
        }
        // Bersihkan kurung kurawal {}
        elseif (str_starts_with($query, '{') && str_ends_with($query, '}')) {
            $query = substr($query, 1, -1);
        }
        
        return trim($query);
    }

    // FUNGSI BARU: EDIT FORM TEMPLATE VIA WA (ADMIN ONLY)
    private function processEditFormCommand($botId, $jid, $text)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Validasi Admin Group
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) return;

        // Format: !editform [Isi Template Form Baru]
        $newTemplate = trim(substr($text, 9)); // Hilangkan !editform

        if (empty($newTemplate)) {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal. Pastikan template form terisi.\nFormat:\n*!editform [Isi Template Form Baru]*");
            return;
        }

        // Simpan ke database pada bot yang sesuai
        \App\Models\Bot::where('bot_id', $botId)->update([
            'form_template' => $newTemplate
        ]);

        $reply = "✅ *TEMPLATE FORM BERHASIL DIPERBARUI*\n\nKini ketika pembeli mengetik *!form*, bot akan membalas dengan format baru:\n\n$newTemplate";
        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    // FUNGSI BARU: LIST ACTIVE ORDERS
    private function processListOrderCommand($botId, $jid)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Validasi Admin Group
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) return;

        // Ambil order yang statusnya pending, processing, atau revised
        $orders = \App\Models\Order::with('customer')
            ->where('bot_id', $botIdDb)
            ->whereIn('status', ['pending', 'processing', 'revised'])
            ->orderBy('id', 'asc')
            ->get();

        if ($orders->isEmpty()) {
            MessageRouter::sendMessage($botId, $jid, "📋 *DAFTAR ORDERAN AKTIF*\n\nTidak ada orderan aktif yang belum diselesaikan saat ini. Kerja bagus! 🎉");
            return;
        }

        // Buat tampilan daftar list yang bersih, rapi, dan mudah dibaca di WhatsApp
        $reply = "📋 *DAFTAR ORDERAN AKTIF*\n\n";
        $i = 1;
        foreach ($orders as $order) {
            $statusLabel = '⏳ PENDING';
            if ($order->status === 'processing') {
                $statusLabel = '⚙️ PROSES';
            } elseif ($order->status === 'revised') {
                $statusLabel = '🔄 REVISI';
            }
            
            $customerName = $order->customer->name ?? 'Unknown';
            $reply .= "$i. *{$order->ticket_id}* - $customerName\n";
            $reply .= "   • Status: $statusLabel\n\n";
            $i++;
        }
        $reply .= "----------------------------------\n";
        $reply .= "Ketik *!order (ticket_id)* untuk melihat rincian detail pesanan.\n_Contoh: *!order " . $orders->first()->ticket_id . "*_";

        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    // FUNGSI BARU: ORDER DETAIL
    private function processOrderDetailCommand($botId, $jid, $text)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Validasi Admin Group
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) return;

        $query = $this->cleanQuery(substr($text, 6)); // Hilangkan !order

        if (empty($query)) {
            MessageRouter::sendMessage($botId, $jid, "❌ Format salah. Gunakan: *!order (ticket_id)*\n_Contoh: *!order #ORD00001*_");
            return;
        }

        $cleanQuery = ltrim($query, '#');
        $order = \App\Models\Order::with('customer')
            ->where('bot_id', $botIdDb)
            ->where(function($q) use ($cleanQuery) {
                $q->where('ticket_id', $cleanQuery)
                  ->orWhere('ticket_id', '#' . $cleanQuery);
            })
            ->first();

        if (!$order) {
            MessageRouter::sendMessage($botId, $jid, "❌ Gagal. Order dengan Ticket ID *\"$query\"* tidak ditemukan.");
            return;
        }

        $statusLabels = [
            'pending' => '⏳ PENDING (Belum Ditanggapi)',
            'processing' => '⚙️ PROCESSING (Sedang Diproses)',
            'done' => '✅ DONE (Selesai)',
            'cancelled' => '❌ CANCELLED (Dibatalkan)',
            'revised' => '🔄 REVISED (Direvisi)',
        ];
        $statusText = $statusLabels[$order->status] ?? strtoupper($order->status);

        $reply = "🔍 *RINCIAN DETAIL PESANAN: {$order->ticket_id}*\n\n" .
                 "• *Pelanggan:* {$order->customer->name} (wa.me/" . explode('@', $order->customer->phone_number)[0] . ")\n" .
                 "• *Nama Item:* {$order->item_name}\n" .
                 "• *Jumlah:* {$order->qty}\n" .
                 "• *Status:* {$statusText}\n" .
                 "• *Waktu Order:* " . $order->created_at->format('d M Y, H:i') . "\n\n" .
                 "📝 *Data Input Formulir & Catatan:*\n" .
                 "_{$order->notes}_";

        MessageRouter::sendMessage($botId, $jid, $reply);
    }

    // FUNGSI BARU: DELETE PROMO/EVENT VIA WA (ADMIN ONLY)
    private function processPromoDeleteCommand($botId, $jid, $text)
    {
        $botIdDb = \App\Models\Bot::where('bot_id', $botId)->value('id');
        if (!$botIdDb) return;

        // Validasi Admin Group
        $isAdminGroup = \App\Models\Group::where('bot_id', $botIdDb)
            ->where('group_jid', $jid)
            ->where('type', 'seller')
            ->exists();

        if (!$isAdminGroup) return;

        // Tentukan command prefix length
        $isEvent = str_starts_with(strtolower($text), '!eventdelete');
        $prefixLength = $isEvent ? 12 : 12; // !eventdelete dan !promodelete sama-sama 12 karakter

        $query = $this->cleanQuery(substr($text, $prefixLength)); // Bersihkan kurung
        
        if (empty($query)) {
            $cmd = $isEvent ? '!eventdelete' : '!promodelete';
            MessageRouter::sendMessage($botId, $jid, "❌ Format salah. Gunakan: *$cmd (judul promo/event)*");
            return;
        }

        // Cari promo milik bot ini (case-insensitive)
        $promo = \App\Models\Promo::where('bot_id', $botIdDb)
            ->where('title', 'LIKE', '%' . $query . '%')
            ->first();

        if ($promo) {
            $title = $promo->title;
            $promo->delete();
            MessageRouter::sendMessage($botId, $jid, "✅ Promo/Event *\"$title\"* berhasil dihapus.");
        } else {
            MessageRouter::sendMessage($botId, $jid, "❌ Promo/Event *\"$query\"* tidak ditemukan.");
        }
    }
}