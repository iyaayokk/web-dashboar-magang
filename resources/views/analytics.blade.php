@extends('layouts.app')
@section('title', 'Executive Analytics')

@section('content')

<!-- FILTER ANALYTICS BAR (Termasuk Filter Tahun) -->
<div class="card card-custom p-3 bg-white mb-4">
    <form method="GET" action="{{ route('analytics') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <label class="form-label mb-1 small fw-bold text-muted"><i class="bi bi-calendar-range me-1"></i>Filter Tahun</label>
            <select name="year" class="form-select form-select-sm fw-bold text-primary" onchange="this.form.submit()">
                @foreach($availableYears as $yr)
                    <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1 small fw-bold text-muted"><i class="bi bi-building me-1"></i>Filter UP3</label>
            <select name="up3" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Semua UP3 --</option>
                @foreach($listUp3 as $up)
                    <option value="{{ $up }}" {{ $selectedUp3 == $up ? 'selected' : '' }}>{{ $up }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1 small fw-bold text-muted"><i class="bi bi-funnel me-1"></i>Filter Status</label>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                @foreach($listStatus as $st)
                    <option value="{{ $st }}" {{ $selectedStatus == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-1 mt-md-4">
            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="background-color: var(--pln-blue);"><i class="bi bi-search me-1"></i> Terapkan</button>
            <a href="{{ route('analytics') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
        </div>
    </form>
</div>

<!-- KPI METRICS WIDGETS -->
<div class="row g-3 mb-4">
    <div class="col">
        <div class="card card-custom card-kpi p-3 bg-white">
            <span class="text-muted small fw-bold">TOTAL ORDER ({{ $selectedYear }})</span>
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
            <span class="text-muted small fw-bold">ORDER SELESAI</span>
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

<!-- GRAFIK TREN BULANAN & PETA -->
<div class="row g-3 mb-4">
    <!-- Tren Bulanan Tahun Terpilih -->
    <div class="col-lg-7">
        <div class="card card-custom bg-white p-3 h-100">
            <h6 class="fw-bold mb-3" style="color:var(--pln-blue)"><i class="bi bi-graph-up me-2"></i>Tren Permohonan Bulanan Tahun {{ $selectedYear }}</h6>
            <div style="height: 330px;">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Top UP3 Volume -->
    <div class="col-lg-5">
        <div class="card card-custom bg-white p-3 h-100">
            <h6 class="fw-bold mb-3" style="color:var(--pln-blue)"><i class="bi bi-bar-chart-fill me-2"></i>Top 10 Volume Permohonan UP3</h6>
            <div style="height: 330px;">
                <canvas id="up3Chart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- PETA GEOSPATIAL & SECONDARY CHARTS -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card card-custom bg-white p-3 h-100">
            <h6 class="fw-bold mb-2" style="color:var(--pln-blue)"><i class="bi bi-geo-alt-fill me-2"></i>Peta Sebaran Permohonan Se-Indonesia</h6>
            <div id="map" style="height: 320px; border-radius: 8px;"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-custom bg-white p-3 mb-3">
            <h6 class="fw-bold mb-2" style="color:var(--pln-blue)"><i class="bi bi-pie-chart-fill me-2"></i>Distribusi Status Order</h6>
            <div style="height: 140px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        <div class="card card-custom bg-white p-3">
            <h6 class="fw-bold mb-2" style="color:var(--pln-blue)"><i class="bi bi-diagram-3-fill me-2"></i>Paket Layanan Terpopuler</h6>
            <div style="height: 140px;">
                <canvas id="paketChart"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // 1. CHART TREN BULANAN
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($monthlyTrend)) !!},
            datasets: [{
                label: 'Jumlah Order {{ $selectedYear }}',
                data: {!! json_encode(array_values($monthlyTrend)) !!},
                borderColor: '#005696',
                backgroundColor: 'rgba(0, 86, 150, 0.1)',
                fill: true,
                tension: 0.3,
                borderWidth: 2
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 2. CHART TOP UP3
    new Chart(document.getElementById('up3Chart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($up3Chart->keys()) !!},
            datasets: [{ label: 'Total Order', data: {!! json_encode($up3Chart->values()) !!}, backgroundColor: '#00a3e0' }]
        },
        options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
    });

    // 3. CHART STATUS & PAKET
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusChart->keys()) !!},
            datasets: [{ data: {!! json_encode($statusChart->values()) !!}, backgroundColor: ['#005696', '#00a3e0', '#ffc107', '#198754', '#dc3545'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });

    new Chart(document.getElementById('paketChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($paketChart->keys()) !!},
            datasets: [{ data: {!! json_encode($paketChart->values()) !!}, backgroundColor: ['#6f42c1', '#fd7e14', '#20c997', '#0d6efd'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });

    // 4. MAP GEOSPATIAL
    const map = L.map('map').setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

    const mapData = {!! json_encode($mapData) !!};
    mapData.forEach((item, idx) => {
        if (!item.up3) return;
        const cleanQuery = item.up3.replace(/^UP3\s+/i, '') + ', Indonesia';
        setTimeout(() => {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(cleanQuery)}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        L.marker([data[0].lat, data[0].lon]).addTo(map)
                            .bindPopup(`<b>${item.up3}</b><br/>Order: ${item.total_order}<br/>Daya: ${Number(item.total_daya).toLocaleString()} VA`);
                    }
                }).catch(e => {});
        }, idx * 300);
    });
</script>
@endsection