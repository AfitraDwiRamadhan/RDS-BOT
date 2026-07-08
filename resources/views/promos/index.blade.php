@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col-md-4">
            <h2 class="fw-bold m-0">Promo & Event Management</h2>
        </div>
        <div class="col-md-8 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
            <form action="" method="GET" class="d-inline-block m-0" id="filterForm">
                <div class="input-group input-group-sm shadow-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-robot"></i></span>
                    <select name="bot_id" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Semua Bot --</option>
                        @foreach($bots as $b)
                            <option value="{{ $b->id }}" {{ isset($botId) && $botId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->bot_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
            <button class="btn btn-sm btn-primary shadow-sm" onclick="openAddModal()">
                <i class="fa-solid fa-plus"></i> Tambah Promo/Event Baru
            </button>
        </div>
    </div>

    <!-- Alert Penjelasan Command -->
    <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
        <h5 class="alert-heading fw-bold"><i class="fa-solid fa-circle-info me-2"></i> Integrasi WhatsApp Bot</h5>
        <p class="mb-0">
            Daftar Promo atau Event aktif di halaman ini dapat dipanggil oleh pelanggan melalui WhatsApp dengan mengetik perintah <strong>`!promo`</strong>. Bot akan mengirimkan daftar promo/event terbaru secara otomatis.
        </p>
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
                            <th>Gambar</th>
                            <th>Judul Promo/Event</th>
                            <th>Bot Terkait</th>
                            <th>Deskripsi / Isi Promo</th>
                            <th>Status Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promos as $promo)
                        <tr>
                            <td>
                                @if($promo->image_path)
                                <a href="{{ asset($promo->image_path) }}" target="_blank">
                                    <img src="{{ asset($promo->image_path) }}" alt="Promo Image" class="img-thumbnail" style="max-height: 50px; max-width: 80px; object-fit: cover;">
                                </a>
                                @else
                                <span class="badge bg-secondary text-white">Tanpa Gambar</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $promo->title }}</strong>
                            </td>
                            <td>
                                @if($promo->bot)
                                    <span class="badge bg-light text-dark border text-muted small"><i class="fa-solid fa-robot"></i> {{ $promo->bot->name }} ({{ $promo->bot->bot_id }})</span>
                                @else
                                    <span class="badge bg-light text-dark border text-muted small"><i class="fa-solid fa-globe"></i> Global</span>
                                @endif
                            </td>
                            <td>{!! nl2br(e($promo->description)) !!}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle-switch" type="checkbox" role="switch" 
                                           data-id="{{ $promo->id }}" {{ $promo->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}</label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning text-white" onclick="openEditModal({{ json_encode($promo) }})">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                
                                <form action="{{ route('promos.destroy', $promo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Promo/Event ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada Promo/Event yang ditambahkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Promo -->
<div class="modal fade" id="promoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('promos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="promo-id">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-title">Tambah Promo/Event Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Bot WhatsApp</label>
                        <select name="bot_id" id="promo-bot-id" class="form-select">
                            <option value="">-- Semua Bot (Global) --</option>
                            @foreach($bots as $bot)
                                <option value="{{ $bot->id }}">{{ $bot->name }} ({{ $bot->bot_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul Promo / Nama Event</label>
                        <input type="text" name="title" id="promo-title" class="form-control" placeholder="Contoh: PROMO AKHIR PEKAN DISKON 10%!" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Isi Deskripsi Promo</label>
                        <textarea name="description" id="promo-description" class="form-control" rows="5" placeholder="Contoh: Dapatkan diskon 10% untuk Top Up minimal Rp 100.000 dengan kode promo WEEKEND. Berlaku hingga Minggu malam!" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gambar Promo/Event (Opsional)</label>
                        <input type="file" name="image" id="promo-image" class="form-control" accept="image/*">
                        <div class="form-text">Maksimal 2MB (format: JPG, PNG, JPEG, GIF)</div>
                    </div>
                    <div class="mb-3 form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="promo-status" checked value="1">
                        <label class="form-check-label fw-bold" for="promo-status">Aktifkan Promo saat disimpan</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save">Simpan Promo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const promoModal = new bootstrap.Modal(document.getElementById('promoModal'));

    function openAddModal() {
        document.getElementById('promo-id').value = '';
        document.getElementById('promo-bot-id').value = '';
        document.getElementById('promo-title').value = '';
        document.getElementById('promo-description').value = '';
        document.getElementById('promo-image').value = '';
        document.getElementById('promo-status').checked = true;
        document.getElementById('modal-title').innerText = 'Tambah Promo/Event Baru';
        document.getElementById('btn-save').innerText = 'Simpan Promo';
        promoModal.show();
    }

    function openEditModal(promo) {
        document.getElementById('promo-id').value = promo.id;
        document.getElementById('promo-bot-id').value = promo.bot_id || '';
        document.getElementById('promo-title').value = promo.title;
        document.getElementById('promo-description').value = promo.description;
        document.getElementById('promo-image').value = '';
        document.getElementById('promo-status').checked = promo.is_active ? true : false;
        document.getElementById('modal-title').innerText = 'Edit Promo/Event';
        document.getElementById('btn-save').innerText = 'Simpan Perubahan';
        promoModal.show();
    }

    // Toggle status promo via AJAX
    document.querySelectorAll('.status-toggle-switch').forEach(switchEl => {
        switchEl.addEventListener('change', function() {
            const promoId = this.dataset.id;
            const isActive = this.checked ? 1 : 0;
            const labelEl = this.nextElementSibling;

            fetch('{{ route("promos.update-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: promoId,
                    is_active: isActive
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    labelEl.innerText = isActive ? 'Aktif' : 'Nonaktif';
                    
                    // Toast notification
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed bottom-0 end-0 p-3';
                    toast.style.zIndex = '9999';
                    toast.innerHTML = `
                        <div class="toast show align-items-center text-white bg-success border-0" role="alert">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="fa-solid fa-gift me-2"></i> ${data.message}
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.remove();
                    }, 2500);
                } else {
                    alert('Gagal memperbarui status promo.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            });
        });
    });
</script>
@endpush
