<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Enterprise Analytics') - PLN Executive System</title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Leaflet Maps CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --pln-blue: #005696;
            --pln-cyan: #00a3e0;
            --pln-dark: #0a2540;
            --pln-yellow: #ffc20e;
        }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Navbar Enterprise */
        .navbar-pln { background-color: var(--pln-dark); border-bottom: 4px solid var(--pln-cyan); box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
        .nav-link { font-weight: 500; color: rgba(255,255,255,0.8) !important; padding: 0.5rem 1rem !important; border-radius: 6px; }
        .nav-link:hover, .nav-link.active { color: #fff !important; background-color: rgba(255,255,255,0.15); }
        .avatar-box { width: 35px; height: 35px; background: var(--pln-cyan); color: #fff; font-weight: bold; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        .card-custom { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-kpi { border-left: 4px solid var(--pln-blue); transition: transform 0.2s; }
        .card-kpi:hover { transform: translateY(-3px); }
        .badge-status { font-size: 0.75rem; padding: 5px 10px; border-radius: 6px; }
    </style>
</head>
<body>

<!-- NAVBAR MULTI-PAGES -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-pln sticky-top mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center text-white me-4" href="{{ route('analytics') }}">
            <i class="bi bi-lightning-charge-fill text-warning fs-3 me-2"></i>
            <div>
                <span class="d-block lh-1" style="letter-spacing: 1px;">PLN EXECUTIVE MONITORING</span>
                <small class="text-white-50 fs-6 fw-normal">Sistem Analytics & Manajemen Layanan EV</small>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('analytics') || request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('analytics') }}">
                        <i class="bi bi-graph-up-arrow me-1"></i> Dashboard & Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                        <i class="bi bi-table me-1"></i> Manajemen Data Order
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3 text-white">
                <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-2">
                    <i class="bi bi-database-check me-1"></i> Enterprise DB Connected
                </span>
                <div class="vr bg-white opacity-25 d-none d-md-block" style="height:25px;"></div>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-box">EX</div>
                    <div class="d-none d-md-block lh-sm">
                        <div class="fw-bold small">Executive User</div>
                        <small class="text-white-50" style="font-size:0.72rem;">DIV-MONITORING</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mb-5">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>