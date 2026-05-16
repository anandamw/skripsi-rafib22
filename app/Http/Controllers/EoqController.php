<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KalkulasiEoq;
use App\Models\PemakaianBulanan;
use App\Services\EoqService;
use Illuminate\Http\Request;

class EoqController extends Controller
{
    protected $eoqService;

    public function __construct(EoqService $eoqService)
    {
        $this->eoqService = $eoqService;
    }

    /**
     * Display the EOQ calculation results.
     */
    public function index(Request $request)
    {
        // Get unique years from PemakaianBulanan
        $tahunList = PemakaianBulanan::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        
        // Default to latest year if available, otherwise current year
        $selectedTahun = $request->tahun ?? ($tahunList->first() ?? date('Y'));

        $kalkulasi = KalkulasiEoq::with('bahanBaku.stok')
            ->where('tahun', $selectedTahun)
            ->get();

        return view('eoq.index', compact('kalkulasi', 'tahunList', 'selectedTahun'));
    }

    /**
     * Calculate EOQ for a specific year.
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer'
        ]);

        $tahun = $validated['tahun'];
        $bahanBakus = BahanBaku::all();
        $successCount = 0;

        foreach ($bahanBakus as $bahan) {
            if ($this->eoqService->calculateForYear($bahan, $tahun)) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            return redirect()->route('eoq.index', ['tahun' => $tahun])
                ->with('success', "Berhasil melakukan kalkulasi EOQ & ROP untuk $successCount bahan baku pada tahun $tahun.");
        }

        return redirect()->route('eoq.index', ['tahun' => $tahun])
            ->with('error', "Gagal melakukan kalkulasi. Pastikan data pemakaian untuk tahun $tahun sudah diinput.");
    }
}
