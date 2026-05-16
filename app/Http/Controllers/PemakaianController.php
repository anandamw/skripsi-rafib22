<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\PemakaianBulanan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PemakaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PemakaianBulanan::with('bahanBaku')->orderBy('tahun', 'desc')->orderBy('bulan', 'desc');
        
        if ($request->tahun) {
            $query->where('tahun', $request->tahun);
        }

        $pemakaian = $query->get();
        $tahunList = PemakaianBulanan::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('pemakaian.index', compact('pemakaian', 'tahunList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bahanBaku = BahanBaku::orderBy('nama', 'asc')->get();
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        return view('pemakaian.create', compact('bahanBaku', 'currentYear', 'currentMonth'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'bulan' => 'required|integer|min:1|max:12',
            'pemakaian' => 'required|numeric|min:0',
        ]);

        // Check if already exists
        $exists = PemakaianBulanan::where('bahan_baku_id', $validated['bahan_baku_id'])
            ->where('tahun', $validated['tahun'])
            ->where('bulan', $validated['bulan'])
            ->first();

        if ($exists) {
            return back()->with('error', 'Data pemakaian untuk bahan dan periode tersebut sudah ada.')->withInput();
        }

        PemakaianBulanan::create($validated);

        return redirect()->route('pemakaian.index')
            ->with('success', 'Data pemakaian berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PemakaianBulanan $pemakaian)
    {
        $pemakaian->delete();

        return redirect()->route('pemakaian.index')
            ->with('success', 'Data pemakaian berhasil dihapus.');
    }
}
