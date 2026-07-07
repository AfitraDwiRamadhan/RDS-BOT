@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold m-0">Customer Management</h2>
            <p class="text-muted m-0">Database pelanggan yang pernah berinteraksi dengan bot.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form action="" method="GET" class="d-inline-block" id="filterForm">
                <div class="input-group input-group-sm shadow-sm" style="max-width: 250px; display: inline-flex;">
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
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No. WhatsApp</th>
                            <th>Nama Profil WA</th>
                            <th>Nick In-Game</th>
                            <th>Game ID</th>
                            <th>Bot Terkait</th>
                            <th>Terdaftar Sejak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td>
                                <strong class="text-success"><i class="fa-brands fa-whatsapp"></i> {{ explode('@', $customer->phone_number)[0] }}</strong>
                            </td>
                            <td>{{ $customer->name ?? 'Unknown' }}</td>
                            <td>{{ $customer->game_nick ?? '-' }}</td>
                            <td>{{ $customer->game_id ?? '-' }}</td>
                            <td>
                                @php
                                    $associatedBots = $customer->orders->pluck('bot')->filter()->unique('id');
                                @endphp
                                @forelse($associatedBots as $bot)
                                    <span class="badge bg-light text-dark border text-muted small mb-1 me-1">
                                        <i class="fa-solid fa-robot"></i> {{ $bot->name }} ({{ $bot->bot_id }})
                                    </span>
                                @empty
                                    <span class="text-muted small">-</span>
                                @endforelse
                            </td>
                            <td class="text-muted small">{{ $customer->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data pelanggan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection