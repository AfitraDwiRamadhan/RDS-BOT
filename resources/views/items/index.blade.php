@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col-md-4">
            <h2 class="fw-bold m-0">Item Management</h2>
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
                <i class="fa-solid fa-plus"></i> Tambah Item Baru
            </button>
        </div>
    </div>

    <!-- Alert Penjelasan Command -->
    <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
        <h5 class="alert-heading fw-bold"><i class="fa-solid fa-circle-info me-2"></i> Integrasi WhatsApp Bot</h5>
        <p class="mb-0">
            Seluruh item aktif yang ada di daftar ini dapat dipanggil oleh pelanggan melalui WhatsApp dengan mengetik perintah <strong>`!list`</strong>. Bot akan menampilkan daftar item beserta harganya secara otomatis.
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
                            <th>Nama Item</th>
                            <th>Harga (IDR)</th>
                            <th>Deskripsi</th>
                            <th>Status Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                @if($item->bot)
                                    <br><span class="badge bg-light text-dark border text-muted small mt-1"><i class="fa-solid fa-robot"></i> {{ $item->bot->name }}</span>
                                @else
                                    <br><span class="badge bg-light text-dark border text-muted small mt-1"><i class="fa-solid fa-globe"></i> Global</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-muted">{{ $item->description ?? '-' }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle-switch" type="checkbox" role="switch" 
                                           data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning text-white" onclick="openEditModal({{ json_encode($item) }})">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                
                                <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item ini dari daftar?');">
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
                            <td colspan="5" class="text-center text-muted py-4">Belum ada item yang ditambahkan ke daftar jualan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Item -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('items.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="item-id">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-title">Tambah Item Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Bot WhatsApp</label>
                        <select name="bot_id" id="item-bot-id" class="form-select">
                            <option value="">-- Semua Bot (Global) --</option>
                            @foreach($bots as $bot)
                                <option value="{{ $bot->id }}">{{ $bot->name }} ({{ $bot->bot_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Item</label>
                        <input type="text" name="name" id="item-name" class="form-control" placeholder="Contoh: 1000 Diamond Mobile Legends" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Harga Jual (Rupiah)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="price" id="item-price" class="form-control" placeholder="Contoh: 150000" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi Item</label>
                        <textarea name="description" id="item-description" class="form-control" rows="3" placeholder="Informasi tambahan atau estimasi proses..."></textarea>
                    </div>
                    <div class="mb-3 form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="item-status" checked value="1">
                        <label class="form-check-label fw-bold" for="item-status">Aktifkan Item saat disimpan</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const itemModal = new bootstrap.Modal(document.getElementById('itemModal'));

    function openAddModal() {
        document.getElementById('item-id').value = '';
        document.getElementById('item-bot-id').value = '';
        document.getElementById('item-name').value = '';
        document.getElementById('item-price').value = '';
        document.getElementById('item-description').value = '';
        document.getElementById('item-status').checked = true;
        document.getElementById('modal-title').innerText = 'Tambah Item Baru';
        document.getElementById('btn-save').innerText = 'Simpan Item';
        itemModal.show();
    }

    function openEditModal(item) {
        document.getElementById('item-id').value = item.id;
        document.getElementById('item-bot-id').value = item.bot_id || '';
        document.getElementById('item-name').value = item.name;
        document.getElementById('item-price').value = item.price;
        document.getElementById('item-description').value = item.description || '';
        document.getElementById('item-status').checked = item.is_active ? true : false;
        document.getElementById('modal-title').innerText = 'Edit Item Penjualan';
        document.getElementById('btn-save').innerText = 'Simpan Perubahan';
        itemModal.show();
    }

    // Toggle status item via AJAX
    document.querySelectorAll('.status-toggle-switch').forEach(switchEl => {
        switchEl.addEventListener('change', function() {
            const itemId = this.dataset.id;
            const isActive = this.checked ? 1 : 0;
            const labelEl = this.nextElementSibling;

            fetch('{{ route("items.update-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: itemId,
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
                                    <i class="fa-solid fa-check-double me-2"></i> ${data.message}
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.remove();
                    }, 2500);
                } else {
                    alert('Gagal memperbarui status item.');
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
