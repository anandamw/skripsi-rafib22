<?php

namespace App\Exports;

use App\Models\KalkulasiEoq;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanEoqExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function collection()
    {
        return KalkulasiEoq::with('bahanBaku.stok')
            ->where('tahun', $this->tahun)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Bahan',
            'Nama Bahan',
            'D (Tahunan)',
            'd (Harian Rata-rata)',
            'Std Deviasi d',
            'EOQ (Q)',
            'Std Deviasi Lead Time',
            'Safety Stock (SS)',
            'Reorder Point (ROP)',
            'Stok Aktual',
            'Status'
        ];
    }

    public function map($kalkulasi): array
    {
        $stokAktual = $kalkulasi->bahanBaku->stok ? $kalkulasi->bahanBaku->stok->stok_aktual : 0;
        $status = 'Aman';
        if ($stokAktual <= $kalkulasi->safety_stock) {
            $status = 'Stockout';
        } elseif ($stokAktual <= $kalkulasi->rop) {
            $status = 'Reorder';
        }

        return [
            $kalkulasi->bahanBaku->kode,
            $kalkulasi->bahanBaku->nama,
            $kalkulasi->d_tahunan,
            $kalkulasi->d_harian_avg,
            $kalkulasi->sigma_d,
            round($kalkulasi->eoq),
            $kalkulasi->sigma_dl,
            round($kalkulasi->safety_stock),
            round($kalkulasi->rop),
            $stokAktual,
            $status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
