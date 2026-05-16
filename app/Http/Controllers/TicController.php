<?php

namespace App\Http\Controllers;

use App\Models\KalkulasiEoq;
use Illuminate\Http\Request;

class TicController extends Controller
{
    /**
     * Display Total Inventory Cost evaluation.
     */
    public function index(Request $request)
    {
        // Get unique years from PemakaianBulanan
        $tahunList = \App\Models\PemakaianBulanan::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $selectedTahun = $request->tahun ?? ($tahunList->first() ?? date('Y'));

        $kalkulasi = KalkulasiEoq::with('bahanBaku')
            ->where('tahun', $selectedTahun)
            ->get();

        $evaluasiTic = [];

        foreach ($kalkulasi as $item) {
            $D = $item->d_tahunan;
            
            $param = $item->bahanBaku->parameterTahun($selectedTahun);
            if (!$param) continue;

            $S = $param->biaya_pesan;
            $H = $param->biaya_simpan;
            // 1. Kebijakan Konvensional (Asumsi: pesan tiap bulan = D/12)
            $q_aktual = $D / 12;
            if ($q_aktual <= 0) $q_aktual = 1; // Prevent division by zero
            
            $tic_aktual = (($D / $q_aktual) * $S) + (($q_aktual / 2) * $H);

            // 2. Kebijakan EOQ
            $q_eoq = $item->eoq;
            $tic_eoq = 0;
            if ($q_eoq > 0) {
                $tic_eoq = (($D / $q_eoq) * $S) + (($q_eoq / 2) * $H);
            }

            // 3. Penghematan
            $efisiensi = $tic_aktual - $tic_eoq;

            $evaluasiTic[] = [
                'bahan_baku' => $item->bahanBaku->nama,
                'kode' => $item->bahanBaku->kode,
                'q_aktual' => $q_aktual,
                'tic_aktual' => $tic_aktual,
                'q_eoq' => $q_eoq,
                'tic_eoq' => $tic_eoq,
                'efisiensi' => $efisiensi,
            ];
        }

        return view('tic.index', compact('evaluasiTic', 'tahunList', 'selectedTahun'));
    }
}
