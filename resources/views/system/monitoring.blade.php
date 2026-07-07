@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="fw-bold">System Monitoring & Backup</h2>
            <p class="text-muted">Pantau kesehatan server lokal (XAMPP/Node) dan amankan data sesi WhatsApp.</p>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="fa-brands fa-node-js text-success"></i> Status Node.js Engine
                </div>
                <div class="card-body text-center py-5">
                    @if($nodeStatus)
                        <div class="spinner-grow text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                        <h3 class="text-success mt-3 fw-bold">ENGINE ONLINE</h3>
                        <p class="text-muted">Jembatan WhatsApp Baileys berjalan lancar di Port 3000.</p>
                    @else
                        <i class="fa-solid fa-triangle-exclamation text-danger fa-4x"></i>
                        <h3 class="text-danger mt-3 fw-bold">ENGINE OFFLINE</h3>
                        <p class="text-muted">Silakan jalankan <code class="bg-light p-1 rounded">node index.js</code> di terminal.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="fa-solid fa-hard-drive text-secondary"></i> Kapasitas Penyimpanan Server Lokal
                </div>
                <div class="card-body">
                    <h5 class="card-title">Kapasitas Sisa: <span class="text-primary">{{ $diskFreeGB }} GB</span></h5>
                    <p class="text-muted small">Total Drive: {{ $diskTotalGB }} GB</p>
                    
                    <div class="progress mt-3" style="height: 25px;">
                        <div class="progress-bar bg-{{ $diskUsagePct > 80 ? 'danger' : 'success' }}" role="progressbar" style="width: {{ $diskUsagePct }}%;" aria-valuenow="{{ $diskUsagePct }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $diskUsagePct }}% Terpakai
                        </div>
                    </div>

                    <hr class="my-4">
                    <p class="mb-1"><i class="fa-brands fa-php text-primary"></i> RAM Framework (Laravel): <strong>{{ $phpMemory }} MB</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-shield-halved text-primary"></i> Backup Sesi WhatsApp</h5>
                    <p class="text-muted mb-3">Amankan seluruh folder sesi bot agar tidak perlu melakukan Scan/Pairing ulang jika server dipindahkan.</p>
                    <a href="{{ route('system.backup') }}" class="btn btn-primary">
                        <i class="fa-solid fa-file-zipper me-1"></i> Download Backup (.zip)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection