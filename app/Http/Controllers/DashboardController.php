<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema; // Ditambahkan untuk pengecekan aman kolom database

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Parameter Filter Global untuk Analytics
        $selectedYear   = $request->input('year', date('Y'));
        $selectedUp3    = $request->input('up3');
        $selectedStatus = $request->input('status');

        // Cache Key Unik
        $cacheKey = "dashboard_analytics_{$selectedYear}_" . ($selectedUp3 ?? 'all') . "_" . ($selectedStatus ?? 'all');

        $analyticsData = Cache::remember($cacheKey, 600, function () use ($selectedYear, $selectedUp3, $selectedStatus) {
            
            // Query Base dengan Filter
            $queryBase = Order::query();

            if ($selectedYear && $selectedYear !== 'all') {
                $queryBase->where(function($q) use ($selectedYear) {
                    $q->whereYear('tanggal_pengajuan', $selectedYear)
                      ->orWhere('tahun', $selectedYear);
                });
            }

            if ($selectedUp3) {
                $queryBase->where('up3', $selectedUp3);
            }

            if ($selectedStatus) {
                $queryBase->where('status', $selectedStatus);
            }

            // Pengecekan Keberadaan Kolom Opsional
            $hasDurasiHari   = Schema::hasColumn('orders', 'durasi_hari');
            $hasPaketLayanan = Schema::hasColumn('orders', 'paket_layanan');

            // --- 1. KPI METRICS ---
            $totalOrders   = (clone $queryBase)->count();
            $totalDaya     = (clone $queryBase)->sum('daya');
            $statusSelesai = (clone $queryBase)->whereRaw('LOWER(status) LIKE ?', ['%selesai%'])->count();
            
            // Aman dari error Unknown Column 'durasi_hari'
            $overSlaCount  = $hasDurasiHari ? (clone $queryBase)->where('durasi_hari', '>', 7)->count() : 0;
            $avgDuration   = $hasDurasiHari ? ((clone $queryBase)->avg('durasi_hari') ?? 0) : 0;

            // --- 2. CHART ANALYTICS ---
            
            // A. Tren Bulanan per Tahun Terpilih (Jan - Des)
            $monthlyTrendRaw = (clone $queryBase)
                ->selectRaw('MONTH(tanggal_pengajuan) as month_num, COUNT(*) as total')
                ->whereNotNull('tanggal_pengajuan')
                ->groupBy('month_num')
                ->pluck('total', 'month_num')
                ->toArray();

            $monthlyTrend = [];
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $monthlyTrend[$monthNames[$m - 1]] = $monthlyTrendRaw[$m] ?? 0;
            }

            // B. Top UP3 Volume
            $up3Chart = (clone $queryBase)
                ->select('up3', DB::raw('count(*) as total'))
                ->whereNotNull('up3')
                ->groupBy('up3')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'up3');

            // C. Distribusi Status Order
            $statusChart = (clone $queryBase)
                ->select('status', DB::raw('count(*) as total'))
                ->whereNotNull('status')
                ->groupBy('status')
                ->pluck('total', 'status');

            // D. Distribusi Paket Layanan (Aman dari error Unknown Column 'paket_layanan')
            $paketChart = $hasPaketLayanan
                ? (clone $queryBase)
                    ->select('paket_layanan', DB::raw('count(*) as total'))
                    ->whereNotNull('paket_layanan')
                    ->groupBy('paket_layanan')
                    ->pluck('total', 'paket_layanan')
                : collect([]);

            // --- 3. MAP GEOSPATIAL DATA ---
            $mapData = (clone $queryBase)
                ->select('up3', DB::raw('count(*) as total_order'), DB::raw('sum(daya) as total_daya'))
                ->whereNotNull('up3')
                ->groupBy('up3')
                ->get();

            return compact(
                'totalOrders', 'totalDaya', 'statusSelesai', 'overSlaCount', 'avgDuration',
                'monthlyTrend', 'up3Chart', 'statusChart', 'paketChart', 'mapData'
            );
        });

        // --- 4. LIST DROPDOWN FILTER ---
        $availableYears = Order::selectRaw('YEAR(tanggal_pengajuan) as yr')
            ->whereNotNull('tanggal_pengajuan')
            ->groupBy('yr')
            ->orderBy('yr', 'desc')
            ->pluck('yr')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        $listUp3 = Order::whereNotNull('up3')->distinct()->pluck('up3')->toArray();
        $listStatus = Order::whereNotNull('status')->distinct()->pluck('status')->toArray();

        return view('analytics', array_merge($analyticsData, compact(
            'availableYears', 'selectedYear', 'listUp3', 'selectedUp3', 'listStatus', 'selectedStatus'
        )));
    }
}