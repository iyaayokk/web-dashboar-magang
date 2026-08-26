<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 1. Tambah kolom tahun jika belum ada (opsional jika tanggal_pengajuan format date)
            if (!Schema::hasColumn('orders', 'tahun')) {
                $table->year('tahun')->nullable()->after('tanggal_pengajuan');
            }

            // 2. Tambahkan Database Indexing agar pencarian ratusan ribu data super cepat
            $table->index('id_order');
            $table->index('no_agenda');
            $table->index('pemohon');
            $table->index('status');
            $table->index('up3');
            $table->index('tanggal_pengajuan');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['id_order', 'no_agenda', 'pemohon', 'status', 'up3', 'tanggal_pengajuan', 'tahun']);
            $table->dropColumn('tahun');
        });
    }
};