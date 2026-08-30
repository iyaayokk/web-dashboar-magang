<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OrdersImport;
use Illuminate\Support\Facades\Cache; // Import Cache facade

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $up3    = $request->input('up3');
        $year   = $request->input('year');

        $query = Order::query();

        // 1. Pencarian Multi-Kolom
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id_order', 'LIKE', "%{$search}%")
                  ->orWhere('no_agenda', 'LIKE', "%{$search}%")
                  ->orWhere('pemohon', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%")
                  ->orWhere('ulp', 'LIKE', "%{$search}%");
            });
        }

        // 2. Filter Berdasarkan Status
        if ($status) {
            $query->where('status', $status);
        }

        // 3. Filter Berdasarkan UP3
        if ($up3) {
            $query->where('up3', $up3);
        }

        // 4. Filter Berdasarkan Tahun
        if ($year && $year !== 'all') {
            $query->where(function($q) use ($year) {
                $q->whereYear('tanggal_pengajuan', $year)
                  ->orWhere('tahun', $year);
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate(15)->appends($request->all());

        // Master List untuk Filter Dropdown
        $listStatus = Order::whereNotNull('status')->distinct()->pluck('status')->toArray();
        $listUp3    = Order::whereNotNull('up3')->distinct()->pluck('up3')->toArray();
        $availableYears = Order::selectRaw('YEAR(tanggal_pengajuan) as yr')
            ->whereNotNull('tanggal_pengajuan')
            ->groupBy('yr')
            ->orderBy('yr', 'desc')
            ->pluck('yr')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        return view('orders.index', compact('orders', 'listStatus', 'listUp3', 'availableYears', 'search', 'status', 'up3', 'year'));
    }

    // UPDATE DATA
    public function update(Request $request, $id)
    {
        $request->validate([
            'pemohon' => 'required|string|max:255',
            'status'  => 'required|string',
            'daya'    => 'required|numeric',
            'up3'     => 'nullable|string',
            'ulp'     => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);
        $order->update($request->all());

        Cache::flush(); // Flush cache dashboard agar data terbaru muncul

        return redirect()->back()->with('success', "Data Order {$order->id_order} berhasil diperbarui.");
    }

    // HAPUS SINGLE ORDER
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $idOrder = $order->id_order;
        $order->delete();

        Cache::flush(); // Flush cache dashboard

        return redirect()->back()->with('success', "Data Order {$idOrder} berhasil dihapus.");
    }

    // RESET DB / HAPUS SELURUH ORDER
    public function destroyAll()
    {
        Order::truncate();
        Cache::flush(); // Flush cache dashboard

        return redirect()->back()->with('success', "Seluruh database data order telah berhasil dibersihkan.");
    }

    // IMPORT EXCEL (Optimasi Ratusan Ribu Data)
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // Izinkan hingga 100MB, hapus mimes strict agar tidak gagal di Windows
        ]);

        // Naikkan batas RAM & Waktu Eksekusi khusus untuk proses Import
        ini_set('memory_limit', '2048M');
        set_time_limit(1800); // 30 Menit

        // Nonaktifkan log query agar memori tidak cepat penuh saat import ratusan ribu data
        \Illuminate\Support\Facades\DB::disableQueryLog();

        try {
            // Import file Excel / CSV
            Excel::import(new OrdersImport, $request->file('file'));

            // Hapus cache analytics dashboard agar statistik langsung ter-update
            Cache::flush();
            \Illuminate\Support\Facades\DB::enableQueryLog();

            return redirect()->back()->with('success', 'File Excel berhasil di-import ke database.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::enableQueryLog();
            return redirect()->back()->with('error', 'Gagal import file: ' . $e->getMessage());
        }
    }
}