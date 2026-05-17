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
        $excelSummary = app(\App\Http\Controllers\TicController::class)->getExcelSummaryData();

        $data = [];
        foreach ($kalkulasi as $item) {
            $kode = $item->bahanBaku->kode;
            $nama = $item->bahanBaku->nama;

            if ($this->tahun == 2025 && isset($excelSummary[$kode])) {
                $row = $excelSummary[$kode];
                $q_aktual = $row['q_obs'];
                $tic_aktual = $row['tic_lama'];
                $q_eoq = $row['q_eoq'];
                $tic_eoq = $row['tic_eoq'];
                $efisiensi = $row['hemat'];
            } else {
                $D = $item->d_tahunan;
                $param = $item->bahanBaku->parameterTahun($this->tahun);
                if (!$param) continue;

                $S_eoq = $param->biaya_pesan;
                $H_eoq = $param->biaya_simpan;
                $SS_eoq = $item->safety_stock;

                $s_mult = 1.15;
                $h_mult = 1.08;
                if (isset($excelSummary[$kode])) {
                    if ($excelSummary[$kode]['s_eoq'] > 0) {
                        $s_mult = $excelSummary[$kode]['s_obs'] / $excelSummary[$kode]['s_eoq'];
                    }
                    if ($excelSummary[$kode]['h_eoq'] > 0) {
                        $h_mult = $excelSummary[$kode]['h_obs'] / $excelSummary[$kode]['h_eoq'];
                    }
                }

                $S_obs = round($S_eoq * $s_mult);
                $H_obs = round($H_eoq * $h_mult);

                $q_aktual = max(1, round($D / 12));
                $tic_aktual = round((($D / $q_aktual) * $S_obs) + (($q_aktual / 2) * $H_obs) + ($SS_eoq * $H_obs));

                $q_eoq = $item->eoq;
                $tic_eoq = $q_eoq > 0 ? round((($D / $q_eoq) * $S_eoq) + (($q_eoq / 2) * $H_eoq) + ($SS_eoq * $H_eoq)) : 0;
                $efisiensi = $tic_aktual - $tic_eoq;
            }

            $data[] = [
                $kode,
                $nama,
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
