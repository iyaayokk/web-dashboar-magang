@extends('layouts.app')
@section('title', 'Executive Analytics')

@section('content')

<!-- FILTER ANALYTICS BAR (Termasuk Filter Tahun) -->
<div class="card card-custom p-3 bg-white mb-4">
    <form method="GET" action="{{ route('analytics') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <label class="form-label mb-1 small fw-bold text-muted"><i class="bi bi-calendar-range me-1"></i>Filter Tahun</label>
            <select name="year" class="form-select form-select-sm fw-bold text-primary" onchange="this.form.submit()">
                <option value="all" {{ $selectedYear == 'all' ? 'selected' : '' }}>-- Semua Tahun --</option>
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
            <span class="text-muted small fw-bold">TOTAL ORDER ({{ $selectedYear == 'all' ? 'SEMUA TAHUN' : $selectedYear }})</span>
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
            <h6 class="fw-bold mb-3" style="color:var(--pln-blue)"><i class="bi bi-graph-up me-2"></i>Tren Permohonan Bulanan {{ $selectedYear == 'all' ? '(Semua Tahun)' : 'Tahun ' . $selectedYear }}</h6>
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
                label: 'Jumlah Order {{ $selectedYear == "all" ? "(Semua Tahun)" : $selectedYear }}',
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
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap PLN Geospatial System' }).addTo(map);

    const up3Coordinates = {
        // Jakarta & Banten & West Java
        'UP3 CIRACAS': [-6.3232, 106.8687],
        'UP3 KRAMAT JATI': [-6.2629, 106.8683],
        'UP3 TANJUNG PRIOK': [-6.1321, 106.8715],
        'UP3 BANDENGAN': [-6.1350, 106.8080],
        'UP3 CEMPAKA PUTIH': [-6.1833, 106.8710],
        'UP3 CENGKARENG': [-6.1500, 106.7350],
        'UP3 JATINEGARA': [-6.2248, 106.8672],
        'UP3 KEBON JERUK': [-6.1915, 106.7640],
        'UP3 LENTENG AGUNG': [-6.3312, 106.8335],
        'UP3 MARUNDA': [-6.1100, 106.9550],
        'UP3 MENTENG': [-6.1950, 106.8360],
        'UP3 PONDOK KOPI': [-6.2230, 106.9450],
        'UP3 BINTARO': [-6.2780, 106.7460],
        'UP3 SERPONG': [-6.2886, 106.6715],
        'UP3 CIPUTAT': [-6.3129, 106.7485],
        'UP3 CIKOKOL': [-6.1880, 106.6290],
        'UP3 CIKUPA': [-6.2410, 106.5220],
        'UP3 TELUK NAGA': [-6.0960, 106.6380],
        'UP3 BANTEN SELATAN': [-6.3580, 106.2480],
        'UP3 BANTEN UTARA': [-6.1200, 106.1500],
        'UP3 BEKASI': [-6.2383, 106.9756],
        'UP3 CIKARANG': [-6.3060, 107.1700],
        'UP3 PONDOK GEDE': [-6.2850, 106.9110],
        'UP3 BOGOR': [-6.5971, 106.7996],
        'UP3 GUNUNG PUTRI': [-6.4428, 106.9038],
        'UP3 DEPOK': [-6.4025, 106.7942],
        'UP3 KARAWANG': [-6.3044, 107.3075],
        'UP3 PURWAKARTA': [-6.5569, 107.4433],
        'UP3 INDRAMAYU': [-6.3264, 108.3200],
        'UP3 CIREBON': [-6.7320, 108.5523],
        'UP3 BANDUNG': [-6.9175, 107.6191],
        'UP3 MAJALAYA': [-7.0500, 107.7500],
        'UP3 CIMAHI': [-6.8722, 107.5422],
        'UP3 SUMEDANG': [-6.8586, 107.9164],
        'UP3 GARUT': [-7.2278, 107.9086],
        'UP3 TASIKMALAYA': [-7.3274, 108.2207],
        'UP3 CIANJUR': [-6.8247, 107.1415],
        'UP3 SUKABUMI': [-6.9277, 106.9300],

        // Central Java & DIY
        'UP3 SEMARANG': [-6.9667, 110.4167],
        'UP3 DEMAK': [-6.8946, 110.6388],
        'UP3 KUDUS': [-6.8048, 110.8405],
        'UP3 SALATIGA': [-7.3305, 110.5084],
        'UP3 SURAKARTA': [-7.5755, 110.8243],
        'UP3 SUKOHARJO': [-7.6833, 110.8333],
        'UP3 KLATEN': [-7.7058, 110.6017],
        'UP3 YOGYAKARTA': [-7.7956, 110.3695],
        'ULP YOGYAKARTA DAN WONOSARI 57100': [-7.7956, 110.3695],
        'UP SEDAYU 57200': [-7.8100, 110.2700],
        'UP SLEMAN 57300': [-7.7167, 110.3500],
        'UP3 MAGELANG': [-7.4706, 110.2178],
        'UP3 PEKALONGAN': [-6.8886, 109.6753],
        'UP3 TEGAL': [-6.8694, 109.1402],
        'UP3 CILACAP': [-7.7279, 109.0060],
        'UP3 PURWOKERTO': [-7.4243, 109.2391],

        // East Java
        'UP3 SURABAYA BARAT': [-7.2650, 112.6800],
        'UP3 SURABAYA SELATAN': [-7.3100, 112.7350],
        'UP3 SURABAYA UTARA': [-7.2100, 112.7400],
        'UP3 SIDOARJO': [-7.4478, 112.7183],
        'UP3 GRESIK': [-7.1566, 112.6555],
        'UP3 MOJOKERTO': [-7.4726, 112.4381],
        'UP3 BOJONEGORO': [-7.1502, 111.8818],
        'UP3 PASURUAN': [-7.6453, 112.9075],
        'UP3 MALANG': [-7.9666, 112.6326],
        'UP3 SITUBONDO': [-7.7063, 113.9998],
        'UP3 BANYUWANGI': [-8.2192, 114.3691],
        'UP3 JEMBER': [-8.1845, 113.6681],
        'UP3 MADIUN': [-7.6298, 111.5239],
        'UP3 PONOROGO': [-7.8671, 111.4657],
        'UP3 KEDIRI': [-7.8480, 112.0178],
        'UP3 PAMEKASAN': [-7.1564, 113.4746],

        // Sumatra & Islands
        'UP3 BANDA ACEH': [5.5483, 95.3238],
        'UP3 LHOKSEUMAWE': [5.1804, 97.1407],
        'UP3 LANGSA': [4.4683, 97.9683],
        'UP3 MEULABOH': [4.1436, 96.1285],
        'UP3 SIGLI': [5.3842, 95.9609],
        'UP3 MEDAN': [3.5952, 98.6722],
        'UP3 MEDAN UTARA': [3.6800, 98.6700],
        'UP3 BINJAI': [3.6000, 98.4857],
        'UP3 LUBUK PAKAM': [3.5594, 98.8778],
        'UP3 PEMATANGSIANTAR': [2.9556, 99.0683],
        'UP3 RANTAU PRAPAT': [2.0970, 99.8290],
        'UP3 SIBOLGA': [1.7388, 98.7797],
        'UP3 PADANG SIDIMPUAN': [1.3734, 99.2694],
        'UP3 NIAS': [1.2842, 97.6217],
        'UP3 PEKANBARU': [0.5071, 101.4478],
        'UP3 DUMAI': [1.6667, 101.4500],
        'UP3 RENGAT': [-0.3700, 102.5500],
        'UP3 BUKIT BARISAN': [0.2200, 100.6300],
        'UP3 BUKITTINGGI': [-0.3056, 100.3692],
        'UP3 PAYAKUMBUH': [0.2200, 100.6300],
        'UP3 PADANG': [-0.9471, 100.4172],
        'UP3 SOLOK': [-0.7967, 100.6575],
        'UP3 JAMBI': [-1.6101, 103.6131],
        'UP3 MUARA BUNGO': [-1.4883, 102.1311],
        'UP3 BENGKULU': [-3.7928, 102.2608],
        'UP3 PALEMBANG': [-2.9761, 104.7754],
        'UP3 OGAN ILIR': [-3.2280, 104.6464],
        'UP3 LAHAT': [-3.7876, 103.5412],
        'UP3 BANGKA': [-2.1316, 106.1169],
        'UP3 BELITUNG': [-2.7411, 107.6384],
        'UP3 TANJUNG KARANG': [-5.4292, 105.2625],
        'UP3 METRO': [-5.1131, 105.3069],
        'UP3 KOTABUMI': [-4.8273, 104.8872],
        'UP3 TANJUNGPINANG': [0.9167, 104.4500],

        // Kalimantan
        'UP3 PONTIANAK': [-0.0263, 109.3425],
        'UP3 SINGKAWANG': [0.9071, 108.9858],
        'UP3 SANGGAU': [0.1170, 110.5890],
        'UP3 KETAPANG': [-1.8481, 109.9730],
        'UP3 PALANGKA RAYA': [-2.2161, 113.9139],
        'UP3 PANGKALAN BUN 2260': [-2.6833, 111.6167],
        'UP3 KUALA KAPUAS': [-3.0092, 114.3857],
        'UP3 BANJARMASIN': [-3.3194, 114.5908],
        'UP3 BARABAI': [-2.5833, 115.3833],
        'UP3 KOTABARU': [-3.2422, 116.2269],
        'UP3 BALIKPAPAN': [-1.2379, 116.8529],
        'UP3 SAMARINDA': [-0.5022, 117.1536],
        'UP3 BONTANG': [0.1333, 117.5000],
        'UP3 BULUNGAN': [2.9000, 117.3600],
        'UP3 KALIMANTAN UTARA': [2.9000, 117.3600],

        // Sulawesi, Maluku, Nusa Tenggara, Papua
        'UP3 MANADO': [1.4748, 124.8428],
        'UP3 KOTAMOBAGU': [0.7228, 124.3144],
        'UP3 GORONTALO': [0.5407, 123.0595],
        'UP3 PALU': [-0.9003, 119.8779],
        'UP3 MAKASSAR SELATAN': [-5.1600, 119.4327],
        'UP3 MAKASSAR UTARA': [-5.1200, 119.4327],
        'UP3 BULUKUMBA': [-5.5606, 120.1947],
        'UP3 WATAMPONE': [-4.5386, 120.3283],
        'UP3 PARE PARE': [-4.0133, 119.6247],
        'UP3 PINRANG': [-3.7892, 119.6506],
        'UP3 PALOPO': [-2.9945, 120.1963],
        'UP3 KENDARI': [-3.9985, 122.5127],
        'UP3 BAU BAU': [-5.4667, 122.6000],
        'UP3 BALI SELATAN': [-8.6705, 115.2126],
        'UP3 BALI TIMUR': [-8.5400, 115.3200],
        'UP3 BALI UTARA': [-8.1120, 115.0880],
        'UP3 MATARAM': [-8.5833, 116.1167],
        'UP3 SELAPARANG': [-8.5833, 116.1167],
        'UP3 BIMA': [-8.4603, 118.7256],
        'UP3 SUMBAWA': [-8.5000, 117.4333],
        'UP3 KUPANG': [-10.1772, 123.6070],
        'UP3 FLORES BAGIAN BARAT': [-8.4900, 119.8800],
        'UP3 SUMBA': [-9.6561, 120.2642],
        'UP3 AMBON': [-3.6954, 128.1814],
        'UP3 TERNATE': [0.7900, 127.3800],
        'UP3 TOBELO': [1.7283, 128.0078],
        'UP3 JAYAPURA': [-2.5489, 140.7181],
        'UP3 MANOKWARI': [-0.8615, 134.0620],
        'UP3 SORONG': [-0.8762, 131.2558],
        'UP3 TIMIKA': [-4.5467, 136.8833]
    };

    function getCoordsForUP3(up3Raw) {
        if (!up3Raw) return null;
        const cleanKey = up3Raw.trim().toUpperCase();
        if (up3Coordinates[cleanKey]) return up3Coordinates[cleanKey];

        for (const key in up3Coordinates) {
            const cityName = key.replace(/^UP3\s+/i, '').trim();
            if (cleanKey.includes(cityName) || cityName.includes(cleanKey.replace(/^UP3\s+/i, '').trim())) {
                return up3Coordinates[key];
            }
        }

        let hash = 0;
        for (let i = 0; i < cleanKey.length; i++) {
            hash = cleanKey.charCodeAt(i) + ((hash << 5) - hash);
        }
        const latOffset = ((Math.abs(hash) % 80) / 10) - 4;
        const lngOffset = (((Math.abs(hash) >> 3) % 160) / 10) - 8;
        return [-2.5 + latOffset, 118.0 + lngOffset];
    }

    const mapData = {!! json_encode($mapData) !!};
    mapData.forEach((item) => {
        if (!item.up3) return;
        const coords = getCoordsForUP3(item.up3);
        if (coords) {
            L.marker([coords[0], coords[1]]).addTo(map)
                .bindPopup(`
                    <div style="font-family:sans-serif; padding:2px;">
                        <b style="color:#005696; font-size:0.95rem;">${item.up3}</b><br/>
                        <hr style="margin:4px 0;"/>
                        <span>Total Order: <b>${Number(item.total_order).toLocaleString()} Order</b></span><br/>
                        <span>Total Daya: <b>${Number(item.total_daya).toLocaleString()} VA</b></span>
                    </div>
                `);
        }
    });
</script>
@endsection