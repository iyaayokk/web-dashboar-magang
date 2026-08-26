<?php

namespace App\Imports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class OrdersImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    /**
     * Mapping data per baris dari file Excel ke Model Database
     */
    public function model(array $row)
    {
        return new Order([
            'id_order'          => $row['id_order'] ?? null,
            'no_agenda'         => $row['no_agenda'] ?? null,
            'pemohon'           => $row['pemohon'] ?? null,
            'brand'             => $row['brand'] ?? null,
            'status'            => $row['status'] ?? null,
            'up3'               => $row['up3'] ?? null,
            'ulp'               => $row['ulp'] ?? null,
            'daya'              => $row['daya'] ?? 0,
            'durasi_hari'       => $row['durasi_hari'] ?? 0,
            'paket_layanan'     => $row['paket_layanan'] ?? null,
            'tanggal_pengajuan' => isset($row['tanggal_pengajuan']) 
                                    ? date('Y-m-d', strtotime($row['tanggal_pengajuan'])) 
                                    : null,
            'tahun'             => isset($row['tanggal_pengajuan']) 
                                    ? date('Y', strtotime($row['tanggal_pengajuan'])) 
                                    : date('Y'),
        ]);
    }

    /**
     * Jumlah baris yang di-insert sekaligus ke database per SQL Query (Hemat Memory)
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Jumlah baris Excel yang dibaca ke RAM per tahap (Mencegah Timeout/Memory Limit)
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}