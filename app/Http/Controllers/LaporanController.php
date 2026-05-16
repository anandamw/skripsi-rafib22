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

        $evaluasiTic = [];
        $totalPenghematan = 0;

        foreach ($kalkulasi as $item) {
            $D = $item->d_tahunan;
            $param = $item->bahanBaku->parameterTahun($tahun);
            if (!$param) continue;

            $S = $param->biaya_pesan;
            $H = $param->biaya_simpan;
            
            $q_aktual = max($D / 12, 1);
            $tic_aktual = (($D / $q_aktual) * $S) + (($q_aktual / 2) * $H);

            $q_eoq = $item->eoq;
            $tic_eoq = $q_eoq > 0 ? (($D / $q_eoq) * $S) + (($q_eoq / 2) * $H) : 0;

            $efisiensi = $tic_aktual - $tic_eoq;
            $totalPenghematan += $efisiensi;

            $evaluasiTic[] = [
                'kode' => $item->bahanBaku->kode,
                'nama' => $item->bahanBaku->nama,
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
