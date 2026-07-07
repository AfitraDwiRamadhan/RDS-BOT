@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col-md-4">
            <h2 class="fw-bold m-0">Group Management</h2>
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
            <button class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                <i class="fa-solid fa-plus"></i> Tambah Grup Baru
            </button>
        </div>
    </div>

    <!-- Alert / Petunjuk cara mendapatkan ID Grup -->
    <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
        <h5 class="alert-heading fw-bold"><i class="fa-solid fa-circle-info me-2"></i> Cara Mengetahui ID Grup WhatsApp</h5>
        <p class="mb-0">
            Anda dapat dengan mudah mengetahui ID Grup WhatsApp dengan cara mengirim pesan perintah <strong>`!grupid`</strong> di dalam grup WhatsApp yang sudah terhubung dengan bot. Bot akan otomatis membalas dengan ID Grup tersebut (contoh: `120363023908@g.us`).
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
                            <th>Bot Terkait</th>
                            <th>Nama Grup</th>
                            <th>JID Grup WA</th>
                            <th>Tipe Grup</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                        <tr>
                            <td>
                                <strong>{{ $group->bot->name ?? 'N/A' }}</strong> 
                                <small class="text-muted">({{ $group->bot->bot_id ?? '' }})</small>
                            </td>
                            <td>{{ $group->group_name }}</td>
                            <td><code>{{ $group->group_jid }}</code></td>
                            <td>
                                <select class="form-select form-select-sm group-type-select" data-id="{{ $group->id }}" style="max-width: 180px;">
                                    <option value="general" {{ $group->type == 'general' ? 'selected' : '' }}>General (Biasa)</option>
                                    <option value="buyer" {{ $group->type == 'buyer' ? 'selected' : '' }}>Buyer (Grup Pelanggan)</option>
                                    <option value="seller" {{ $group->type == 'seller' ? 'selected' : '' }}>Seller (Grup Admin/Command)</option>
                                    <option value="monitoring" {{ $group->type == 'monitoring' ? 'selected' : '' }}>Monitoring</option>
                                </select>
                            </td>
                            <td>
                                <form action="{{ route('groups.destroy', $group->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus grup ini?');">
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
                            <td colspan="5" class="text-center text-muted py-4">Belum ada grup yang ditambahkan. Silakan gunakan perintah <strong>`!grupid`</strong> di WA atau tambah manual.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Grup -->
<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('groups.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Grup WhatsApp Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Bot WhatsApp</label>
                        <select name="bot_id" class="form-select" required>
                            <option value="">-- Pilih Bot --</option>
                            @foreach($bots as $bot)
                                <option value="{{ $bot->id }}">{{ $bot->name }} ({{ $bot->bot_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Grup</label>
                        <input type="text" name="group_name" class="form-control" placeholder="Contoh: Grup Admin RD SHOP" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">JID Grup WA (Group ID)</label>
                        <input type="text" name="group_jid" class="form-control" placeholder="Contoh: 120363023908@g.us" required>
                        <small class="text-muted">Gunakan perintah <code>!grupid</code> di WhatsApp untuk mendapatkan ID ini.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe Grup</label>
                        <select name="type" class="form-select" required>
                            <option value="general">General (Grup Biasa)</option>
                            <option value="buyer">Buyer (Grup Pelanggan / Tempat Order)</option>
                            <option value="seller">Seller (Grup Admin / Command Center)</option>
                            <option value="monitoring">Monitoring</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Grup</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.group-type-select').forEach(select => {
        select.addEventListener('change', function() {
            const groupId = this.dataset.id;
            const newType = this.value;

            fetch('{{ route("groups.update-type") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    group_id: groupId,
                    type: newType
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Berikan feedback visual kecil (toast atau alert kecil)
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed bottom-0 end-0 p-3';
                    toast.style.zIndex = '9999';
                    toast.innerHTML = `
                        <div class="toast show align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="fa-solid fa-circle-check me-2"></i> ${data.message}
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                } else {
                    alert('Gagal mengupdate tipe grup.');
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
