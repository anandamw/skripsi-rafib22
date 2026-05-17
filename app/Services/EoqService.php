<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\KalkulasiEoq;
use App\Models\PemakaianBulanan;
use App\Models\ParameterBahan;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

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

        // --- 1. OVERFITTING & OUTLIER DETECTION (WINSORIZING) ---
        $raw_d_harian = $pemakaian->pluck('d_harian')->toArray();
        $mean_raw = count($raw_d_harian) > 0 ? array_sum($raw_d_harian) / count($raw_d_harian) : 0;
        $sigma_raw = $this->calculateStandardDeviation($raw_d_harian);

        $winsorized_d_harian = [];
        $upper_bound = $mean_raw + (2 * $sigma_raw);
        $lower_bound = $mean_raw - (2 * $sigma_raw);
        if ($lower_bound < 0) $lower_bound = 0;

        $d_tahunan = 0;
        foreach ($pemakaian as $p) {
            $val = $p->d_harian;
            if ($val > $upper_bound) $val = $upper_bound;
            if ($val < $lower_bound) $val = $lower_bound;
            $winsorized_d_harian[] = $val;
            $d_tahunan += ($val * $p->jumlah_hari);
        }

        $d_harian_avg = count($winsorized_d_harian) > 0 ? array_sum($winsorized_d_harian) / count($winsorized_d_harian) : 0;
        $sigma_d = $this->calculateStandardDeviation($winsorized_d_harian);

        // --- 2. VOLATILITY DETECTION (CV) ---
        $cv = 0;
        $is_volatile = false;
        $z_score = 1.65;
        if ($d_harian_avg > 0) {
            $cv = $sigma_d / $d_harian_avg;
            if ($cv > 0.30) {
                $is_volatile = true;
                $z_score = 2.05; // Adaptive Safety Stock
            }
        }

        // --- 3. TREND & SEASONAL DETECTION ---
        $past_years = PemakaianBulanan::where('bahan_baku_id', $bahan->id)
            ->where('tahun', '<=', $tahun)
            ->where('tahun', '>', $tahun - 5)
            ->selectRaw('tahun, sum(pemakaian) as total')
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->pluck('total', 'tahun')->toArray();

        $tipe_fluktuasi = 'Stasioner';
        $nilai_penyesuaian = null;

        if (count($past_years) >= 2) {
            $y_values = array_values($past_years);
            $n_years = count($y_values);
            
            // Calculate slope
            $x_values = range(1, $n_years);
            $sum_x = array_sum($x_values);
            $sum_y = array_sum($y_values);
            $sum_xy = 0;
            $sum_x2 = 0;
            for ($i = 0; $i < $n_years; $i++) {
                $sum_xy += ($x_values[$i] * $y_values[$i]);
                $sum_x2 += ($x_values[$i] * $x_values[$i]);
            }
            $slope = 0;
            $denominator = ($n_years * $sum_x2) - ($sum_x * $sum_x);
            if ($denominator != 0) {
                $slope = (($n_years * $sum_xy) - ($sum_x * $sum_y)) / $denominator;
            }

            $rata_rata_y = $n_years > 0 ? $sum_y / $n_years : 0;
            $trend_threshold = $rata_rata_y * 0.05; // 5%

            if (abs($slope) > $trend_threshold) {
                $tipe_fluktuasi = 'Trend';
                // WMA 5 periode
                $weights = [1, 2, 3, 4, 5];
                $used_weights = array_slice($weights, 5 - $n_years, $n_years);
                $sum_w = array_sum($used_weights);
                $wma_d = 0;
                for ($i = 0; $i < $n_years; $i++) {
                    $wma_d += ($y_values[$i] * $used_weights[$i]);
                }
                $d_tahunan = $wma_d / $sum_w; 
                $d_harian_avg = $d_tahunan / 365;
                $nilai_penyesuaian = json_encode(['slope' => $slope]);
            } else {
                // Seasonal Detection
                $monthly_totals = PemakaianBulanan::where('bahan_baku_id', $bahan->id)
                    ->where('tahun', '<=', $tahun)
                    ->selectRaw('bulan, avg(pemakaian) as avg_pemakaian')
                    ->groupBy('bulan')
                    ->pluck('avg_pemakaian', 'bulan')->toArray();
                    
                $overall_avg = count($monthly_totals) > 0 ? array_sum($monthly_totals) / count($monthly_totals) : 0;
                
                $has_seasonal = false;
                $si_list = [];
                if ($overall_avg > 0) {
                    foreach ($monthly_totals as $m => $avg_p) {
                        $si = $avg_p / $overall_avg;
                        $si_list[$m] = $si;
                        if ($si > 1.10 || $si < 0.90) {
                            $has_seasonal = true;
                        }
                    }
                }
                
                if ($has_seasonal) {
                    $tipe_fluktuasi = 'Musiman';
                    $nilai_penyesuaian = json_encode(['seasonal_index' => $si_list]);
                    // For annual D, average SI is 1, so D_tahunan remains.
                }
            }
        }

        // --- 4. LEAD TIME EVALUATION ---
        $lt_aktual = $bahan->lead_time;
        $lt_kontrak = $bahan->lead_time;

        $pos = PurchaseOrder::whereHas('items', function ($q) use ($bahan) {
                $q->where('bahan_baku_id', $bahan->id);
            })
            ->where('status', 'diterima')
            ->whereNotNull('tanggal_diterima')
            ->whereYear('tanggal_diterima', '<=', $tahun)
            ->whereYear('tanggal_diterima', '>=', $tahun - 1) // limit to past year approx
            ->get();

        if ($pos->count() > 0) {
            $total_lt_realisasi = 0;
            $terlambat_count = 0;
            
            foreach ($pos as $po) {
                $realisasi = Carbon::parse($po->tanggal)->diffInDays(Carbon::parse($po->tanggal_diterima));
                $total_lt_realisasi += $realisasi;
                if ($realisasi > $lt_kontrak) {
                    $terlambat_count++;
                }
            }
            
            $rata_realisasi = $total_lt_realisasi / $pos->count();
            $deviasi = $lt_kontrak > 0 ? (($rata_realisasi - $lt_kontrak) / $lt_kontrak) * 100 : 0;
            
            if ($terlambat_count >= 3 && $deviasi > 30) {
                $lt_aktual = $rata_realisasi;
                $z_score = 2.05; // Overrides to 2.05
            } elseif ($terlambat_count >= 1) {
                $lt_aktual = $rata_realisasi;
                // z_score remains as set by CV
            }
        }

        // --- 5. FINAL EOQ & ROP CALCULATION ---
        $s = $parameter->biaya_pesan;
        $h = $parameter->biaya_simpan;
        
        $eoq = 0;
        if ($h > 0) {
            $eoq = sqrt((2 * $d_tahunan * $s) / $h);
        }

        $sigma_dl = $sigma_d * sqrt($lt_aktual);
        $ss = $z_score * $sigma_dl;
        $rop = ($d_harian_avg * $lt_aktual) + $ss;

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
                'tipe_fluktuasi' => $tipe_fluktuasi,
                'is_volatile' => $is_volatile,
                'z_score' => $z_score,
                'lead_time_aktual' => $lt_aktual,
                'nilai_penyesuaian' => $nilai_penyesuaian,
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
