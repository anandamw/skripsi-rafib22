<?php

namespace App\Exports;

use App\Models\KalkulasiEoq;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanTicExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function array(): array
    {
        $kalkulasi = KalkulasiEoq::with('bahanBaku')
            ->where('tahun', $this->tahun)
            ->get();

        $data = [];
        foreach ($kalkulasi as $item) {
            $D = $item->d_tahunan;
            $S = $item->bahanBaku->s_biaya_pesan;
            $H = $item->bahanBaku->h_biaya_simpan;
            
            // Konvensional (Asumsi pesan tiap bulan = D/12)
            $q_aktual = $D / 12;
            if ($q_aktual <= 0) $q_aktual = 1;
            $tic_aktual = (($D / $q_aktual) * $S) + (($q_aktual / 2) * $H);

            // EOQ
            $q_eoq = $item->eoq;
            $tic_eoq = 0;
            if ($q_eoq > 0) {
                $tic_eoq = (($D / $q_eoq) * $S) + (($q_eoq / 2) * $H);
            }

            $efisiensi = $tic_aktual - $tic_eoq;

            $data[] = [
                $item->bahanBaku->kode,
                $item->bahanBaku->nama,
                round($q_aktual),
                round($tic_aktual),
                round($q_eoq),
                round($tic_eoq),
                round($efisiensi)
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Kode Bahan',
            'Nama Bahan',
            'Q Aktual (Asumsi 12x/thn)',
            'TIC Konvensional (Rp)',
            'Q Optimal (EOQ)',
            'TIC Metode EOQ (Rp)',
            'Efisiensi / Penghematan (Rp)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
