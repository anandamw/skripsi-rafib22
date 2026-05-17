<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KalkulasiEoq;
use App\Exports\LaporanPersediaanExport;
use App\Exports\LaporanEoqExport;
use App\Exports\LaporanTicExport;
use App\Exports\LaporanPoExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    /**
     * Display report dashboard.
     */
    public function index()
    {
        $tahunList = \App\Models\PemakaianBulanan::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        return view('laporan.index', compact('tahunList'));
    }

    /**
     * Laporan Persediaan (Stok Aktual)
     */
    public function persediaan(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $bahanBakus = BahanBaku::with('stok')->orderBy('kode', 'asc')->get();

        if ($format === 'excel') {
            return Excel::download(new LaporanPersediaanExport, 'Laporan_Persediaan_' . date('Ymd_His') . '.xlsx');
        }

        // Generate PDF
        $pdf = Pdf::loadView('laporan.pdf_persediaan', compact('bahanBakus'))->setPaper('a4', 'portrait');
        return $pdf->stream('Laporan_Persediaan_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Laporan Kalkulasi EOQ & ROP
     */
    public function eoq(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $tahun = $request->query('tahun', date('Y'));
        
        $kalkulasi = KalkulasiEoq::with('bahanBaku.stok')->where('tahun', $tahun)->get();

        if ($format === 'excel') {
            return Excel::download(new LaporanEoqExport($tahun), 'Laporan_EOQ_' . $tahun . '_' . date('Ymd_His') . '.xlsx');
        }

        // Generate PDF
        $pdf = Pdf::loadView('laporan.pdf_eoq', compact('kalkulasi', 'tahun'))->setPaper('a4', 'landscape');
        return $pdf->stream('Laporan_EOQ_' . $tahun . '_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Laporan Evaluasi TIC
     */
    public function tic(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $tahun = $request->query('tahun', date('Y'));
        
        $kalkulasi = KalkulasiEoq::with('bahanBaku')->where('tahun', $tahun)->get();
        $excelSummary = app(\App\Http\Controllers\TicController::class)->getExcelSummaryData();

        $evaluasiTic = [];
        $totalPenghematan = 0;

        foreach ($kalkulasi as $item) {
            $kode = $item->bahanBaku->kode;
            $nama = $item->bahanBaku->nama;

            if ($tahun == 2025 && isset($excelSummary[$kode])) {
                $row = $excelSummary[$kode];
                $q_aktual = $row['q_obs'];
                $tic_aktual = $row['tic_lama'];
                $q_eoq = $row['q_eoq'];
                $tic_eoq = $row['tic_eoq'];
                $efisiensi = $row['hemat'];
            } else {
                $D = $item->d_tahunan;
                $param = $item->bahanBaku->parameterTahun($tahun);
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

            $totalPenghematan += $efisiensi;

            $evaluasiTic[] = [
                'kode' => $kode,
                'nama' => $nama,
                'q_aktual' => $q_aktual,
                'tic_aktual' => $tic_aktual,
                'q_eoq' => $q_eoq,
                'tic_eoq' => $tic_eoq,
                'efisiensi' => $efisiensi,
            ];
        }

        if ($format === 'excel') {
            return Excel::download(new LaporanTicExport($tahun), 'Laporan_TIC_' . $tahun . '_' . date('Ymd_His') . '.xlsx');
        }

        // Generate PDF
        $pdf = Pdf::loadView('laporan.pdf_tic', compact('evaluasiTic', 'tahun', 'totalPenghematan'))->setPaper('a4', 'landscape');
        return $pdf->stream('Laporan_TIC_' . $tahun . '_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Laporan Purchase Order
     */
    public function po(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $tahun = $request->query('tahun', date('Y'));
        
        $purchaseOrders = \App\Models\PurchaseOrder::with(['items.bahanBaku', 'user'])
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        if ($format === 'excel') {
            return Excel::download(new LaporanPoExport($tahun), 'Laporan_PO_' . $tahun . '_' . date('Ymd_His') . '.xlsx');
        }

        // Generate PDF
        $pdf = Pdf::loadView('laporan.pdf_po', compact('purchaseOrders', 'tahun'))->setPaper('a4', 'landscape');
        return $pdf->stream('Laporan_PO_' . $tahun . '_' . date('Ymd_His') . '.pdf');
    }
}
