@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold m-0 text-primary"><i class="fa-solid fa-gauge-high"></i> Dashboard Control Center</h2>
            <p class="text-muted m-0">Selamat datang di pusat pemantauan dan pengelolaan multi-bot WhatsApp Anda.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="badge bg-light text-success border border-success p-2 shadow-sm">
                <i class="fa-solid fa-circle-check spinner-border-sm me-1"></i> System Online
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                <div class="card-body text-white position-relative p-4">
                    <div style="opacity: 0.15;" class="position-absolute end-0 bottom-0 mb-n2 me-n2">
                        <i class="fa-solid fa-robot fa-6x text-white"></i>
                    </div>
                    <h5 class="card-title fw-semibold text-white-50"><i class="fa-solid fa-robot me-2"></i> Total Bot Terdaftar</h5>
                    <h2 class="display-4 fw-bold m-0">{{ $totalBots }}</h2>
                    <p class="text-white-50 small mt-2 mb-0">Total seluruh profil bot di sistem</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
                <div class="card-body text-white position-relative p-4">
                    <div style="opacity: 0.15;" class="position-absolute end-0 bottom-0 mb-n2 me-n2">
                        <i class="fa-solid fa-wifi fa-6x text-white"></i>
                    </div>
                    <h5 class="card-title fw-semibold text-white-50"><i class="fa-solid fa-check-circle me-2"></i> Bot Berstatus Aktif</h5>
                    <h2 class="display-4 fw-bold m-0">{{ $activeBots }}</h2>
                    <p class="text-white-50 small mt-2 mb-0">Bot yang saat ini terhubung ke WhatsApp</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                <div class="card-body text-white position-relative p-4">
                    <div style="opacity: 0.15;" class="position-absolute end-0 bottom-0 mb-n2 me-n2">
                        <i class="fa-solid fa-ban fa-6x text-white"></i>
                    </div>
                    <h5 class="card-title fw-semibold text-white-50"><i class="fa-solid fa-times-circle me-2"></i> Bot Berstatus Offline</h5>
                    <h2 class="display-4 fw-bold m-0">{{ $offlineBots }}</h2>
                    <p class="text-white-50 small mt-2 mb-0">Bot yang memerlukan scan QR / pairing ulang</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Panels -->
    <div class="row">
        <!-- WhatsApp Bot Capabilities -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <h5 class="m-0 fw-bold"><i class="fa-brands fa-whatsapp me-2 text-success"></i> Fitur & Perintah WhatsApp Bot</h5>
                    <span class="badge bg-success">WhatsApp Client</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">Setiap bot WhatsApp yang Anda aktifkan dapat secara otomatis melayani pelanggan dan menerima instruksi admin dengan perintah-perintah berikut:</p>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary"><i class="fa-solid fa-users me-2"></i> Layanan Pembeli (Japri / PM & Grup)</h6>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!list</code></div>
                                    Melihat daftar katalog produk aktif yang dikhususkan untuk bot tersebut.
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!promo</code></div>
                                    Melihat daftar diskon, event, atau promo berjalan (dilengkapi gambar jika ada).
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!form</code></div>
                                    Mendapatkan formulir pemesanan otomatis sesuai kustomisasi masing-masing bot.
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!history [ticket_id]</code></div>
                                    Melacak alur status pesanan pembeli (misal: <code>!history #ORD00001</code>).
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h6 class="fw-bold text-danger"><i class="fa-solid fa-lock-open me-2"></i> Pengelolaan Admin (Grup Admin / Seller Only)</h6>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!listorder</code></div>
                                    Melihat daftar antrean seluruh orderan aktif yang belum selesai dalam format poin rapi.
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!order [ticket_id]</code></div>
                                    Melihat detail data formulir pembeli, catatan, dan nomor telepon.
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>ps</code> / <code>dn</code> / <code>cn</code> / <code>rf</code> (Reply Tiket)</div>
                                    Memperbarui status pesanan menjadi Proses (ps), Selesai (dn), Batal (cn), atau Revisi (rf).
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!additem [Nama] | [Harga/Detail]</code></div>
                                    Menambahkan item jualan baru ke database. Pisahkan Nama dan Detail menggunakan karakter <code>|</code>.
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!addevent</code> / <code>!addpromo [Judul] | [Deskripsi]</code></div>
                                    Menambahkan Event/Promo baru. Kirim pesan ini sebagai <strong>caption gambar</strong> untuk menyertakan poster promo otomatis.
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0 bg-transparent border-bottom">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><code>!promodelete [Judul]</code> / <code>!listdelete [Nama]</code></div>
                                    Menghapus data promo/event jualan atau item katalog produk secara instan dari database.
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Web Dashboard Features -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-desktop me-2 text-info"></i> Fungsi Pengelolaan Web Dashboard</h5>
                    <span class="badge bg-info">Web Administrator</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">Dasbor admin web ini memegang kontrol pusat database dan mempermudah Anda melakukan aktivitas manajemen tingkat tinggi berikut:</p>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item bg-transparent px-0 border-0 mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-light-primary text-primary me-2"><i class="fa-solid fa-robot"></i></span>
                                <h6 class="fw-bold m-0">Manajemen Bot & Pemindaian QR</h6>
                            </div>
                            <p class="text-muted small ps-4 mb-0">Menambah profil bot baru, menghubungkan nomor WA lewat pemindaian QR code atau pairing code secara realtime, serta mengubah template form order pembeli per bot.</p>
                        </div>

                        <div class="list-group-item bg-transparent px-0 border-0 mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-light-success text-success me-2"><i class="fa-solid fa-cart-shopping"></i></span>
                                <h6 class="fw-bold m-0">Pemantauan Order & Filter Multi-Bot</h6>
                            </div>
                            <p class="text-muted small ps-4 mb-0">Memantau tiket pesanan pembeli secara langsung, melihat waktu order, status pembayaran/proses, serta memfilter data transaksi khusus untuk bot tertentu.</p>
                        </div>

                        <div class="list-group-item bg-transparent px-0 border-0 mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-light-info text-info me-2"><i class="fa-solid fa-box-open"></i></span>
                                <h6 class="fw-bold m-0">Katalog Item & Event Promosi (Multi-Tenant)</h6>
                            </div>
                            <p class="text-muted small ps-4 mb-0">Mengelola daftar produk (harga, status aktif/nonaktif) dan promo/event promosi dengan mengunggah gambar. Tiap item/promo dapat dikaitkan dengan bot spesifik atau diatur secara Global.</p>
                        </div>

                        <div class="list-group-item bg-transparent px-0 border-0 mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-light-warning text-warning me-2"><i class="fa-solid fa-users-gear"></i></span>
                                <h6 class="fw-bold m-0">Manajemen Grup & Database Customer</h6>
                            </div>
                            <p class="text-muted small ps-4 mb-0">Mendaftarkan grup WhatsApp dengan mengatur tipenya (General, Buyer/Pelanggan, Seller/Admin). Database nomor HP dan nickname pemain tercatat rapi di halaman pelanggan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status & Webhook -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="background-color: #f8fafc;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-network-wired text-muted me-2"></i> Environment Status & Integrasi API</h5>
                    <div class="row text-center text-md-start">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <span class="text-muted small d-block">LARAVEL WEBHOOK ENDPOINT</span>
                            <code class="d-block mt-1 font-monospace text-primary bg-white p-2 rounded border border-light shadow-sm">http://localhost:8000/api/webhook/whatsapp</code>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <span class="text-muted small d-block">NODE.JS BAILEYS SERVER STATUS</span>
                            <span class="d-inline-block mt-2 badge bg-success p-2"><i class="fa-solid fa-circle spinner-border-sm me-1"></i> Running on Port 3000</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">DATABASE ENGINE STATUS</span>
                            <span class="d-inline-block mt-2 badge bg-dark p-2"><i class="fa-solid fa-database me-1"></i> MySQL (XAMPP) Connected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-primary { background-color: rgba(59, 130, 246, 0.1) !important; }
    .bg-light-success { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-light-info { background-color: rgba(6, 182, 212, 0.1) !important; }
    .bg-light-warning { background-color: rgba(245, 158, 11, 0.1) !important; }
    .badge { font-weight: 600; }
</style>
@endsection