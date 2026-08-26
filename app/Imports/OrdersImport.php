<?php

namespace App\Imports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithUpserts;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class OrdersImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, WithUpserts
{
    public function model(array $row)
    {
        $idOrder = $row['id_order'] ?? $row['idorder'] ?? null;

        if (empty($idOrder)) {
            return null;
        }

        // Hitung tahun dari tanggal pengajuan
        $tanggalPengajuan = $this->transformDate($row['tanggal_pengajuan'] ?? $row['tanggalpengajuan'] ?? null);
        $tahun = $tanggalPengajuan ? date('Y', strtotime($tanggalPengajuan)) : null;

        return new Order([
            'id_order'          => trim((string)$idOrder),
            'no_agenda'         => $row['no_agenda'] ?? $row['noagenda'] ?? null,
            'pemohon'           => $row['pemohon'] ?? 'N/A',
            'status'            => $row['status'] ?? 'Tanpa Status',
            'sub_status'        => $row['sub_status'] ?? $row['substatus'] ?? null,
            'tanggal_pengajuan' => $tanggalPengajuan,
            'tahun'             => $tahun, 
            'last_update'       => $this->transformDate($row['last_update'] ?? $row['lastupdate'] ?? null),
            'total_durasi_day'  => (int) ($row['total_durasi_day'] ?? $row['totaldurasiday'] ?? 0),
            'durasi_hari'       => (int) ($row['durasi_hari'] ?? $row['durasihari'] ?? $row['total_durasi_day'] ?? $row['totaldurasiday'] ?? 0), 
            'paket'             => $row['paket'] ?? null,
            'paket_layanan'     => $row['paket_layanan'] ?? $row['paketlayanan'] ?? $row['paket'] ?? null,
            'daya'              => (int) ($row['daya'] ?? 0),
            'brand'             => $row['brand'] ?? null,
            'charger'           => $row['charger'] ?? null,
            'type'              => $row['type'] ?? null,
            'tipe_saluran'      => $row['tipe_saluran'] ?? $row['tipesaluran'] ?? null,
            'provinsi'          => $row['provinsi'] ?? null,
            'distribusi'        => $row['distribusi'] ?? null,
            'up3'               => $row['up3'] ?? null,
            'ulp'               => $row['ulp'] ?? null,
        ]);
    }

    public function uniqueBy()
    {
        return 'id_order';
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function transformDate($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return date('Y-m-d', strtotime(str_replace('/', '-', $value)));
        } catch (\Exception $e) {
            return null;
        }
    }
}