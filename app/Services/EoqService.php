<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\KalkulasiEoq;
use App\Models\PemakaianBulanan;
use App\Models\ParameterBahan;

class EoqService
{
    /**
     * Calculate EOQ, SS, ROP for a specific material and year.
     * Returns true if successful, false if not enough data.
     */
    public function calculateForYear(BahanBaku $bahan, int $tahun): bool
    {
        $pemakaian = PemakaianBulanan::where('bahan_baku_id', $bahan->id)
            ->where('tahun', $tahun)
            ->get();

        // Require at least some data (ideally 12 months, but we calculate with what we have)
        if ($pemakaian->isEmpty()) {
            return false;
        }

        // Fetch S and H from parameter_bahan for this year
        $parameter = ParameterBahan::where('bahan_baku_id', $bahan->id)
            ->where('tahun', $tahun)
            ->first();

        if (!$parameter) {
            return false;
        }

        // 1. Calculate D (Total Tahunan)
        $d_tahunan = $pemakaian->sum('pemakaian');

        // 2. Calculate d bar (Average of d_harian)
        $d_harian_avg = $pemakaian->avg('d_harian');

        // 3. Calculate Sigma_d (Standard Deviation of d_harian)
        $d_harian_values = $pemakaian->pluck('d_harian')->toArray();
        $sigma_d = $this->calculateStandardDeviation($d_harian_values);

        // 4. Calculate EOQ: sqrt(2 * D * S / H)
        $s = $parameter->biaya_pesan;
        $h = $parameter->biaya_simpan;
        
        $eoq = 0;
        if ($h > 0) {
            $eoq = sqrt((2 * $d_tahunan * $s) / $h);
        }

        // 5. Calculate Sigma_DL: sigma_d * sqrt(LT)
        $lt = $bahan->lead_time;
        $sigma_dl = $sigma_d * sqrt($lt);

        // 6. Calculate Safety Stock (SS): Z * Sigma_DL (Z=1.65 for 95% service level)
        $ss = 1.65 * $sigma_dl;

        // 7. Calculate ROP: (d_avg * LT) + SS
        $rop = ($d_harian_avg * $lt) + $ss;

        // 8. Calculate CV (Coefficient of Variation)
        $cv = 0;
        if ($d_harian_avg > 0) {
            $cv = $sigma_d / $d_harian_avg;
        }

        // Save or update calculation
        KalkulasiEoq::updateOrCreate(
            ['bahan_baku_id' => $bahan->id, 'tahun' => $tahun],
            [
                'd_tahunan' => $d_tahunan,
                'd_harian_avg' => $d_harian_avg,
                'sigma_d' => $sigma_d,
                'eoq' => $eoq,
                'sigma_dl' => $sigma_dl,
                'safety_stock' => $ss,
                'rop' => $rop,
                'cv' => $cv,
            ]
        );

        return true;
    }

    /**
     * Calculate Sample Standard Deviation
     */
    private function calculateStandardDeviation(array $a): float
    {
        $n = count($a);
        if ($n <= 1) {
            return 0.0;
        }
        
        $mean = array_sum($a) / $n;
        $carry = 0.0;
        foreach ($a as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        };
        return sqrt($carry / ($n - 1));
    }
}
