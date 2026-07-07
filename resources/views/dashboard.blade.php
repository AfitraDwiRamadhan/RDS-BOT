@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Dashboard Overview</h2>
            <p class="text-muted">Selamat datang di Pusat Kontrol RDS-SYSTEM.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-robot"></i> Total Bot</h5>
                    <h2 class="display-4 fw-bold">{{ $totalBots }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-check-circle"></i> Bot Aktif</h5>
                    <h2 class="display-4 fw-bold">{{ $activeBots }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card bg-danger text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-times-circle"></i> Bot Offline</h5>
                    <h2 class="display-4 fw-bold">{{ $offlineBots }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <i class="fa-solid fa-circle-info"></i> <strong>Sistem Berjalan:</strong> XAMPP dan Node.js saling terhubung. Siap mengelola fitur Order Management System untuk WhatsApp Anda.
            </div>
        </div>
    </div>
</div>
@endsection