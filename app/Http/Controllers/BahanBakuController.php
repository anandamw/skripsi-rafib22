<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bahanBaku = BahanBaku::orderBy('kode', 'asc')->get();
        return view('bahan_baku.index', compact('bahanBaku'));
    }

    /**
     * Generate kode bahan baku otomatis (BB001, BB002, dst.)
     */
    private function generateKode(): string
    {
        $last = BahanBaku::orderBy('id', 'desc')->first();

        if (!$last) {
            return 'BB001';
        }

        // Ambil angka dari kode terakhir, misal BB012 → 12
        $number = (int) preg_replace('/[^0-9]/', '', $last->kode);
        return 'BB' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kodeOtomatis = $this->generateKode();
        return view('bahan_baku.create', compact('kodeOtomatis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:bahan_bakus,kode',
            'nama' => 'required',
            'satuan' => 'required',
            'kategori' => 'required|in:Lokal,Impor',
            'lead_time' => 'required|integer|min:1',
        ]);

        BahanBaku::create($validated);

        return redirect()->route('bahan-baku.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BahanBaku $bahanBaku)
    {
        return view('bahan_baku.edit', compact('bahanBaku'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BahanBaku $bahanBaku)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:bahan_bakus,kode,' . $bahanBaku->id,
            'nama' => 'required',
            'satuan' => 'required',
            'kategori' => 'required|in:Lokal,Impor',
            'lead_time' => 'required|integer|min:1',
        ]);

        $bahanBaku->update($validated);

        return redirect()->route('bahan-baku.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BahanBaku $bahanBaku)
    {
        $bahanBaku->delete();

        return redirect()->route('bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
