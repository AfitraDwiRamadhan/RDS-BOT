@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-6">
            <h2 class="fw-bold">Bot Management</h2>
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBotModal">
                <i class="fa-solid fa-plus"></i> Tambah Bot
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Bot ID</th>
                            <th>Nama Bot</th>
                            <th>Nomor WA</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bots as $bot)
                        <tr>
                            <td><strong>{{ $bot->bot_id }}</strong></td>
                            <td>{{ $bot->name }}</td>
                            <td>{{ $bot->phone_number }}</td>
                            <td>
                                <span class="badge bg-{{ $bot->status == 'active' ? 'success' : 'secondary' }}" id="status-{{ $bot->bot_id }}">
                                    {{ strtoupper($bot->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success btn-start-bot" data-botid="{{ $bot->bot_id }}" data-phone="{{ $bot->phone_number }}">
                                    <i class="fa-solid fa-link"></i> Hubungkan
                                </button>
                                
                                <button class="btn btn-sm btn-warning btn-stop-bot" data-botid="{{ $bot->bot_id }}">
                                    <i class="fa-solid fa-stop"></i> Stop
                                </button>

                                <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#editTemplateModal-{{ $bot->id }}">
                                    <i class="fa-solid fa-file-signature"></i> Template Form
                                </button>

                                <form action="{{ route('bots.destroy', $bot->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bot ini? Semua sesi akan terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>

                                <!-- Modal Edit Template Form -->
                                <div class="modal fade" id="editTemplateModal-{{ $bot->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content text-start">
                                            <form action="{{ route('bots.update-template', $bot->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Template Form ({{ $bot->bot_id }})</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-muted small">Ubah template formulir pemesanan yang akan dikirim bot ketika pembeli mengetik <strong>!form</strong>.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Isi Template Form:</label>
                                                        <textarea name="form_template" rows="10" class="form-control font-monospace" placeholder="Masukkan format form order..." required>{{ $bot->form_template ?? "FORM ORDER\nNick : \nID : \nNo HP : \nNama Item : \nJumlah : \nCatatan : \n\n*(Silakan copy pesan ini, isi datanya, lalu kirimkan kembali)*" }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada bot yang ditambahkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addBotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bots.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Bot Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Bot ID</label>
                        <input type="text" name="bot_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nama Bot</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nomor WhatsApp (Harus 628...)</label>
                        <input type="number" name="phone_number" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="authModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center shadow">
            <div class="modal-body py-4">
                <h5 class="mb-2">Tautkan Perangkat</h5>
                
                <div id="qr-container" class="mb-3">
                    <div class="spinner-border text-primary my-4" role="status"></div>
                    <p class="text-muted">Menyiapkan Engine...</p>
                </div>

                <hr class="text-muted">
                
                <p class="small text-muted mb-2">Sulit memindai QR Code? Gunakan metode kode.</p>
                <button type="button" class="btn btn-dark btn-sm w-100" id="btn-request-pairing">
                    <i class="fa-solid fa-key"></i> Dapatkan Kode Tautan (8 Digit)
                </button>
            </div>
            <div class="modal-footer justify-content-center bg-light">
                <button type="button" class="btn btn-outline-danger btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const socket = io('http://localhost:3000');
    const authModal = new bootstrap.Modal(document.getElementById('authModal'));
    
    let currentBotId = '';
    let currentPhone = '';

    // 1. Klik Tombol Hubungkan (Otomatis Munculkan QR)
    document.querySelectorAll('.btn-start-bot').forEach(button => {
        button.addEventListener('click', function() {
            currentBotId = this.dataset.botid;
            currentPhone = this.dataset.phone;
            
            // Reset UI ke mode Loading
            document.getElementById('qr-container').innerHTML = `
                <div class="spinner-border text-primary my-4" role="status"></div>
                <p class="text-muted">Menyiapkan QR Code...</p>
            `;
            document.getElementById('btn-request-pairing').style.display = 'block';
            authModal.show();

            // Panggil API Node.js untuk start bot (otomatis generate QR)
            fetch('http://localhost:3000/api/bots/start', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ botId: currentBotId })
            });
        });
    });

    // 2. Klik Tombol "Dapatkan Kode Tautan"
    document.getElementById('btn-request-pairing').addEventListener('click', function() {
        this.style.display = 'none'; // Sembunyikan tombol setelah diklik
        
        // Ubah UI menjadi Loading Kode
        document.getElementById('qr-container').innerHTML = `
            <div class="spinner-border text-success my-4" role="status"></div>
            <p class="fw-bold text-success">Meminta kode dari server Meta...</p>
        `;

        // Panggil API khusus Pairing Code
        fetch('http://localhost:3000/api/bots/pairing', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ botId: currentBotId, phoneNumber: currentPhone })
        });
    });

    // 3. Stop Bot
    document.querySelectorAll('.btn-stop-bot').forEach(button => {
        button.addEventListener('click', function() {
            if(confirm('Yakin mematikan bot?')) {
                fetch('http://localhost:3000/api/bots/stop', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ botId: this.dataset.botid })
                });
            }
        });
    });

    // SOCKET LISTENERS
    socket.on('qr', (data) => {
        if(data.botId === currentBotId) {
            document.getElementById('qr-container').innerHTML = `
                <p class="text-muted small mb-1">Scan QR di bawah ini menggunakan WhatsApp</p>
                <img src="${data.qrCodeUrl}" alt="QR Code" class="img-fluid border p-2 rounded" style="max-width: 250px;">
            `;
        }
    });

    socket.on('pairing_code', (data) => {
        if(data.botId === currentBotId) {
            document.getElementById('qr-container').innerHTML = `
                <p class="text-muted small mb-2">Pilih <strong>Tautkan dengan Nomor Telepon Saja</strong> di HP kamu</p>
                <h2 class="fw-bold text-success bg-light py-3 border rounded border-success" style="letter-spacing: 4px;">
                    ${data.code}
                </h2>
            `;
        }
    });

    socket.on('pairing_code_error', (data) => {
        if(data.botId === currentBotId) {
            document.getElementById('qr-container').innerHTML = `
                <p class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> ${data.message}</p>
            `;
        }
    });

    socket.on('status', (data) => {
        const statusBadge = document.getElementById(`status-${data.botId}`);
        if(statusBadge) {
            if(data.status === 'connected') {
                statusBadge.className = 'badge bg-success';
                statusBadge.innerText = 'ACTIVE';
                authModal.hide();
                alert(`Koneksi Berhasil! Bot ${data.botId} Aktif.`);
            } else if (data.status === 'inactive' || data.status === 'logged_out') {
                statusBadge.className = 'badge bg-secondary';
                statusBadge.innerText = 'INACTIVE';
            }
        }
    });
</script>
@endpush