<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDS-SYSTEM | Multi Bot Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar a { color: #c2c7d0; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: #fff; }
        .sidebar-brand { color: #fff; font-size: 1.5rem; font-weight: bold; padding: 20px; text-align: center; border-bottom: 1px solid #4b545c; }
        .content-wrapper { padding: 20px; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar" style="width: 250px;">
            <div class="sidebar-brand">
                <i class="fa-solid fa-robot"></i> RDS-SYSTEM
            </div>
            <div class="mt-3">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge me-2"></i> Dashboard
                </a>
                <a href="{{ route('bots.index') }}" class="{{ request()->routeIs('bots.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-mobile-screen me-2"></i> Bot Management
                </a>
                <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cart-shopping me-2"></i> Order Management
                </a>
                <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users me-2"></i> Customer Management
                </a>
                <a href="{{ route('groups.index') }}" class="{{ request()->routeIs('groups.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-rectangle me-2"></i> Group Management
                </a>
                <a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tags me-2"></i> Item Management
                </a>
                <a href="{{ route('promos.index') }}" class="{{ request()->routeIs('promos.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gift me-2"></i> Promo & Event
                </a>
                
                <hr class="border-secondary my-2 mx-3">
                <a href="{{ route('system.monitoring') }}" class="{{ request()->routeIs('system.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-server me-2"></i> System & Monitoring
                </a>
            </div>
        </div>

        <div class="flex-grow-1">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3">
                <span class="navbar-brand mb-0 h1">Control Panel V1.0</span>
            </nav>

            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost:3000/socket.io/socket.io.js"></script>
    @stack('scripts')
</body>
</html>