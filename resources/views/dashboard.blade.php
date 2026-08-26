<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Analytics Dashboard - PLN Monitoring System</title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Leaflet Maps CSS & JS -->
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
        
        /* Enterprise Navbar */
        .navbar-pln { background-color: var(--pln-dark); border-bottom: 4px solid var(--pln-cyan); box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
        .nav-status { font-size: 0.8rem; background: rgba(255,255,255,0.1); padding: 4px 12px; border-radius: 20px; color: #e0e0e0; }
        .avatar-box { width: 35px; height: 35px; background: var(--pln-cyan); color: #fff; font-weight: bold; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        .card-custom { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-kpi { border-left: 4px solid var(--pln-blue); transition: transform 0.2s; }
        .card-kpi:hover { transform: translateY(-3px); }
        #map { height: 380px; border-radius: 8px; z-index: 1; }
        
        .badge-status { font-size: 0.75rem; padding: 5px 10px; border-radius: 6px; }
    </style>
</head>
<body>

<!-- 1. NAVBAR KOMPLEKS ENTERPRISE -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-pln sticky-top mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center text-white me-4" href="{{ route('dashboard') }}">
            <i class="bi bi-lightning-charge-fill text-warning fs-3 me-2"></i>
            <div>
                <span class="d-block lh-1" style="letter-spacing: 1px;">PLN EXECUTIVE MONITORING</span>
                <small class="text-white-50 fs-6 fw-normal">Sistem Manajemen Layanan EV & Pelanggan</small>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item me-3">
                    <span class="nav-status d-flex align-items-center">
                        <span class="spinner-grow spinner-grow-sm text-success me-2" style="width: 8px; height: 8px;"></span>
                        System Online | DB: Connected
                    </span>
                </li>
            </ul>

            <!-- Nav Right Profile & Quick Actions -->
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-danger btn-sm text-white border-white-50" data-bs-toggle="modal" data-bs-target="#modalResetAll">
                    <i class="bi bi-trash3-fill me-1"></i> Reset DB
                </button>
                <div class="vr text-white opacity-25"></div>
                <div class="d-flex align-items-center text-white gap-2">
                    <div class="avatar-box">EX</div>
                    <div class="d-none d-md-block lh-sm">
                        <div class="fw-bold small">Executive User</div>
                        <small class="text-white-50" style="font-size:0.75rem;">DIV-MONITORING</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">

    <!-- ALERT MESSAGES -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show card-custom" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show card-custom" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 2. SEARCH BAR & FILTER PANEL & IMPORT EXCEL -->
    <div class="row g-3 mb-4">
        <!-- Search & Dropdown Filter -->
        <div class="col-lg-8">
            <div class="card card-custom p-3 bg-white h-100">
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2">
                    <!-- Global Search Field -->
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari ID Order, Pemohon, UP3, ULP..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">-- Semua Status --</option>
                            @foreach($listStatus as $st)
                                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- UP3 Filter -->
                    <div class="col-md-2">
                        <select name="up3" class="form-select form-select-sm">
                            <option value="">-- Semua UP3 --</option>
                            @foreach($listUp3 as $up)
                                <option value="{{ $up }}" {{ request('up3') == $up ? 'selected' : '' }}>{{ $up }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Buttons -->
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill" style="background-color: var(--pln-blue);"><i class="bi bi-funnel me-1"></i> Filter</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- File Upload Form -->
        <div class="col-lg-4">
            <div class="card card-custom p-3 bg-white h-100">
                <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                    @csrf
                    <input type="file" name="file" class="form-control form-control-sm" required>
                    <button type="submit" class="btn btn-success btn-sm px-3 text-nowrap"><i class="bi bi-file-earmark-excel me-1"></i> Import</button>
                </form>
                <small class="text-muted mt-1" style="font-size:0.75rem;">Mendukung update & tambah otomatis file .xlsx / .csv</small>
            </div>
        </div>
    </div>

    <!-- 3. KPI METRIC WIDGETS -->
    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card card-custom card-kpi p-3 bg-white">
                <span class="text-muted small fw-bold">TOTAL ORDER</span>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($totalOrders) }}</h3>
            </div>
        </div>
        <div class="col">
            <div class="card card-custom card-kpi p-3 bg-white" style="border-left-color: #ffc107;">
                <span class="text-muted small fw-bold">TOTAL DAYA (VA)</span>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($totalDaya) }}</h3>
            </div>
        </div>
        <div class="col">
            <div class="card card-custom card-kpi p-3 bg-white" style="border-left-color: #0dcaf0;">
                <span class="text-muted small fw-bold">RATA-RATA DURASI</span>
                <h3 class="fw-bold mb-0 text-info">{{ number_format($avgDuration, 1) }} Hari</h3>
            </div>
        </div>
        <div class="col">
            <div class="card card-custom card-kpi p-3 bg-white" style="border-left-color: #198754;">
                <span class="text-muted small fw-bold">SELESAI</span>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($statusSelesai) }}</h3>
            </div>
        </div>
        <div class="col">
            <div class="card card-custom card-kpi p-3 bg-white" style="border-left-color: #dc3545;">
                <span class="text-muted small fw-bold">OVER SLA (>7 HARI)</span>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($overSlaCount) }}</h3>
            </div>
        </div>
    </div>

    <!-- 4. PETA GEOSPATIAL (MAPS) & CHARTS UTAMA -->
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card card-custom bg-white p-3 h-100">
                <h6 class="fw-bold text-pln mb-2" style="color:var(--pln-blue)"><i class="bi bi-geo-alt-fill me-2"></i>Peta Sebaran Permohonan Layanan (Per UP3)</h6>
                <!-- CONTAINER MAPS INTERAKTIF -->
                <div id="map"></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-custom bg-white p-3 h-100">
                <h6 class="fw-bold text-pln mb-2" style="color:var(--pln-blue)"><i class="bi bi-bar-chart-fill me-2"></i>Top Volume Permohonan UP3</h6>
                <div class="chart-container" style="height:320px;">
                    <canvas id="up3Chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. ANALYTICS CHARTS SECONDARY -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card card-custom bg-white p-3 h-100">
                <h6 class="fw-bold text-pln mb-2" style="color:var(--pln-blue)"><i class="bi bi-pie-chart-fill me-2"></i>Distribusi Status Order</h6>
                <div class="chart-container" style="height:230px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-custom bg-white p-3 h-100">
                <h6 class="fw-bold text-pln mb-2" style="color:var(--pln-blue)"><i class="bi bi-diagram-3-fill me-2"></i>Paket Layanan Terpopuler</h6>
                <div class="chart-container" style="height:230px;">
                    <canvas id="paketChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-custom bg-white p-3 h-100">
                <h6 class="fw-bold text-pln mb-2" style="color:var(--pln-blue)"><i class="bi bi-graph-up-arrow me-2"></i>Tren Permohonan Harian</h6>
                <div class="chart-container" style="height:230px;">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. TABEL MANAJEMEN DATA LENGKAP -->
    <div class="card card-custom bg-white p-3 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0" style="color:var(--pln-blue)"><i class="bi bi-table me-2"></i>Manajemen Data Permohonan Order</h6>
            <small class="text-muted">Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ number_format($orders->total()) }} Data</small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th>ID Order</th>
                        <th>No Agenda</th>
                        <th>Pemohon</th>
                        <th>Status</th>
                        <th>Daya</th>
                        <th>UP3 / ULP</th>
                        <th>Brand / Tipe</th>
                        <th>Pengajuan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="fw-bold" style="color:var(--pln-blue);">{{ $order->id_order }}</td>
                            <td><small class="text-muted">{{ $order->no_agenda ?? '-' }}</small></td>
                            <td>{{ $order->pemohon }}</td>
                            <td>
                                @if(str_contains(strtolower($order->status), 'selesai') || str_contains(strtolower($order->status), 'complete'))
                                    <span class="badge bg-success badge-status">{{ $order->status }}</span>
                                @elseif(str_contains(strtolower($order->status), 'batal') || str_contains(strtolower($order->status), 'cancel'))
                                    <span class="badge bg-danger badge-status">{{ $order->status }}</span>
                                @else
                                    <span class="badge bg-warning text-dark badge-status">{{ $order->status ?? 'Draft' }}</span>
                                @endif
                            </td>
                            <td>{{ number_format($order->daya) }} VA</td>
                            <td>
                                <div class="lh-sm">
                                    <strong class="d-block text-dark">{{ $order->up3 ?? '-' }}</strong>
                                    <small class="text-muted">{{ $order->ulp ?? '-' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="lh-sm">
                                    <span>{{ $order->brand ?? '-' }}</span>
                                    <small class="text-muted d-block">{{ $order->type ?? '' }}</small>
                                </div>
                            </td>
                            <td><small>{{ $order->tanggal_pengajuan ?? '-' }}</small></td>
                            <td class="text-center">
                                <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ID Order {{ $order->id_order }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Tidak ada data permohonan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Render Link Pagination Bootstrap -->
        <div class="d-flex justify-content-end mt-3">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- MODAL CONFIRMATION RESET ALL DATA -->
<div class="modal fade" id="modalResetAll" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Seluruh Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Tindakan ini akan <b>menghapus seluruh record data order</b> di database secara permanen. Apakah Anda yakin ingin mengosongkan database?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('orders.destroyAll') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm px-3">Ya, Bersihkan Database</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. INIT MAPS - TAMPILAN AWAL MENCAKUP SELURUH INDONESIA
    const map = L.map('map').setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap PLN Geospatial System'
    }).addTo(map);

    // Preset Data Koordinat UP3
    const up3Coordinates = {
        'UP3 CIRACAS': [-6.3232, 106.8687],
        'UP3 KRAMAT JATI': [-6.2629, 106.8683],
        'UP3 TANJUNG PRIOK': [-6.1321, 106.8715],
        'UP3 SERPONG': [-6.2886, 106.6715],
        'UP3 CIPUTAT': [-6.3129, 106.7485],
        'UP3 GUNUNG PUTRI': [-6.4428, 106.9038],
        'UP3 LENTENG AGUNG': [-6.3312, 106.8335],
        'UP3 BANDUNG': [-6.9175, 107.6191],
        'UP3 BEKASI': [-6.2383, 106.9756],
        'UP3 BOGOR': [-6.5971, 106.7996],
        'UP3 MEDAN': [3.5952, 98.6722],
        'UP3 KUPANG': [-10.1772, 123.6070],
        'UP3 SURABAYA': [-7.2575, 112.7521],
        'UP3 SEMARANG': [-6.9667, 110.4167],
        'UP3 MAKASSAR': [-5.1477, 119.4327],
        'UP3 DENPASAR': [-8.6705, 115.2126],
        'UP3 PALEMBANG': [-2.9761, 104.7754],
        'UP3 BALIKPAPAN': [-1.2379, 116.8529],
        'UP3 JAYAPURA': [-2.5489, 140.7181]
    };

    const rawMapData = {!! json_encode($mapData) !!};

    // Fungsi Pembantu Menambahkan Marker Peta
    function addMarkerToMap(lat, lng, up3Name, totalOrder, totalDaya) {
        const marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup(`
            <div style="font-family:sans-serif; padding:2px;">
                <b style="color:#005696; font-size:1rem;">${up3Name}</b><br/>
                <hr style="margin:4px 0;"/>
                <span>Total Permohonan: <b>${totalOrder} Order</b></span><br/>
                <span>Total Akumulasi Daya: <b>${Number(totalDaya).toLocaleString()} VA</b></span>
            </div>
        `);
    }

    // Dynamic Auto-Geocoding Loop
    rawMapData.forEach((item, index) => {
        if (!item.up3) return;

        const rawName = item.up3.trim();
        const upperKey = rawName.toUpperCase();

        // 1. Gunakan koordinat preset jika lokasi sudah ada di list
        if (up3Coordinates[upperKey]) {
            const [lat, lng] = up3Coordinates[upperKey];
            addMarkerToMap(lat, lng, rawName, item.total_order, item.total_daya);
        } else {
            // 2. Jika kota baru (seperti Kupang dll) belum ada di preset, cari otomatis via Nominatim API
            const cleanQuery = rawName.replace(/^UP3\s+/i, '') + ', Indonesia';
            
            setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(cleanQuery)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            addMarkerToMap(lat, lng, rawName, item.total_order, item.total_daya);
                        }
                    })
                    .catch(err => console.log('Geocoding Error:', err));
            }, index * 400); // Penundaan (delay) 400ms agar terhindar dari limit request API
        }
    });

    // 2. CHART CONFIGURATIONS
    new Chart(document.getElementById('up3Chart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($up3Chart->keys()) !!},
            datasets: [{ label: 'Jumlah Order', data: {!! json_encode($up3Chart->values()) !!}, backgroundColor: '#00a3e0' }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusChart->keys()) !!},
            datasets: [{ data: {!! json_encode($statusChart->values()) !!}, backgroundColor: ['#005696', '#00a3e0', '#ffc107', '#198754', '#dc3545'] }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('paketChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($paketChart->keys()) !!},
            datasets: [{ data: {!! json_encode($paketChart->values()) !!}, backgroundColor: ['#6f42c1', '#fd7e14', '#20c997', '#0d6efd'] }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyChart->keys()) !!},
            datasets: [{ label: 'Tren Harian', data: {!! json_encode($dailyChart->values()) !!}, borderColor: '#005696', backgroundColor: 'rgba(0, 86, 150, 0.1)', fill: true, tension: 0.3 }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
</body>
</html>