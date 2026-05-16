<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Stok;
use App\Models\TransaksiStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StokController extends Controller
{
    /**
     * Display current stock levels.
     */
    public function index()
    {
        $bahanBakus = BahanBaku::with('stok')->orderBy('kode', 'asc')->get();
        return view('stok.index', compact('bahanBakus'));
    }

    /**
     * Display transaction history.
     */
    public function riwayat()
    {
        $transaksis = TransaksiStok::with(['bahanBaku', 'user'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('stok.riwayat', compact('transaksis'));
    }

    /**
     * Store a new stock transaction.
     */
    public function storeTransaksi(Request $request)
    {
        $validated = $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'tipe' => 'required|in:masuk,keluar',
            'jumlah' => 'required|numeric|min:0.01',
            'tanggal' => 'required|date|before_or_equal:today',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // Specific validation for 'keluar' - check if stock is enough
        if ($validated['tipe'] === 'keluar') {
            $stok = Stok::where('bahan_baku_id', $validated['bahan_baku_id'])->first();
            $currentStock = $stok ? $stok->stok_aktual : 0;
            
            if ($currentStock < $validated['jumlah']) {
                return back()->with('error', 'Stok tidak mencukupi untuk transaksi keluar. Stok saat ini: ' . $currentStock);
            }
        }

        $validated['user_id'] = Auth::id();

        TransaksiStok::create($validated);

        return redirect()->back()->with('success', 'Transaksi stok berhasil disimpan dan stok aktual telah diperbarui.');
    }
}
