<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KalkulasiEoq;
use App\Models\PemakaianBulanan;
use App\Models\PurchaseOrder;
use App\Models\Stok;
use App\Models\TransaksiStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // KPI Cards
        $totalBahan = BahanBaku::count();
        $totalStok = Stok::sum('stok_aktual');
        $totalPO = PurchaseOrder::count();
        $poDraft = PurchaseOrder::where('status', 'draft')->count();

        // Get available years for the dropdown
        $availableYears = PemakaianBulanan::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        // Determine selected year
        $selectedYear = $request->query('tahun', $availableYears->first());

        // Stock Status Summary (requires kalkulasi data)
        $latestTahun = $selectedYear;
        $statusAman = 0;
        $statusReorder = 0;
        $statusStockout = 0;

        if ($latestTahun) {
            $kalkulasis = KalkulasiEoq::with('bahanBaku.stok')
                ->where('tahun', $latestTahun)
                ->get();

            foreach ($kalkulasis as $k) {
                $stokAktual = $k->bahanBaku->stok ? $k->bahanBaku->stok->stok_aktual : 0;
                if ($stokAktual <= $k->safety_stock) {
                    $statusStockout++;
                } elseif ($stokAktual <= $k->rop) {
                    $statusReorder++;
                } else {
                    $statusAman++;
                }
            }
        }

        // Recent transactions (last 5)
        $recentTransaksi = TransaksiStok::with(['bahanBaku', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent POs (last 5)
        $recentPOs = PurchaseOrder::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data: Monthly usage trend (selected year)
        $chartTahun = $selectedYear;
        $usageByMonth = PemakaianBulanan::where('tahun', $chartTahun)
            ->selectRaw('bulan, SUM(pemakaian) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Fill all 12 months
        $monthlyUsage = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyUsage[] = $usageByMonth[$i] ?? 0;
        }

        return view('dashboard', compact(
            'user',
            'totalBahan',
            'totalStok',
            'totalPO',
            'poDraft',
            'statusAman',
            'statusReorder',
            'statusStockout',
            'recentTransaksi',
            'recentPOs',
            'monthlyUsage',
            'chartTahun',
            'availableYears',
            'selectedYear'
        ));
    }
}
