@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold m-0">Order Management</h2>
            <p class="text-muted m-0">Pantau seluruh tiket pesanan dari pelanggan secara realtime.</p>
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
                            <th>Ticket ID</th>
                            <th>Pelanggan</th>
                            <th>Game Data</th>
                            <th>Item Pesanan</th>
                            <th>Status</th>
                            <th>Waktu Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td><strong class="text-primary">{{ $order->ticket_id }}</strong></td>
                            <td>
                                <div><i class="fa-solid fa-user text-muted me-1"></i> {{ $order->customer->name ?? 'Unknown' }}</div>
                                <div class="small text-muted"><i class="fa-brands fa-whatsapp text-success me-1"></i> {{ explode('@', $order->customer->phone_number)[0] }}</div>
                            </td>
                            <td>
                                <div><strong>Nick:</strong> {{ $order->customer->game_nick ?? '-' }}</div>
                                <div class="small"><strong>ID:</strong> {{ $order->customer->game_id ?? '-' }}</div>
                            </td>
                            <td>
                                {{ $order->item_name }} <span class="badge bg-secondary ms-1">x{{ $order->qty }}</span>
                            </td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock"></i> PENDING</span>
                                @elseif($order->status == 'processing')
                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-spinner"></i> PROSES</span>
                                @elseif($order->status == 'done')
                                    <span class="badge bg-success"><i class="fa-solid fa-check-double"></i> SELESAI</span>
                                @elseif($order->status == 'cancelled')
                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark"></i> BATAL</span>
                                @elseif($order->status == 'revised')
                                    <span class="badge bg-dark"><i class="fa-solid fa-rotate-right"></i> REVISI</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fa-solid fa-box-open fa-3x mb-3 text-light"></i><br>
                                Belum ada pesanan masuk.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection