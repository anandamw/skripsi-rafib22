<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\TransaksiStok;
use App\Models\User;
use Illuminate\Database\Seeder;

class StokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bahanBakus = BahanBaku::all();
        $adminGudang = User::where('role', User::ROLE_GUDANG)->first();

        if ($bahanBakus->isEmpty() || !$adminGudang) {
            return;
        }

        // Simulasi stok awal operasional gudang (Terpisah dari data Excel skripsi)
        foreach ($bahanBakus as $bahan) {
            $initialStock = 0;
            if ($bahan->kode === 'BB001') {
                $initialStock = 120;
            } elseif ($bahan->kode === 'BB002') {
                $initialStock = 200;
            } elseif ($bahan->kode === 'BB003') {
                $initialStock = 50;
            } elseif ($bahan->kode === 'BB004') {
                $initialStock = 80;
            } else {
                $initialStock = 150;
            }

            TransaksiStok::create([
                'bahan_baku_id' => $bahan->id,
                'tipe' => 'masuk',
                'jumlah' => $initialStock,
                'keterangan' => 'Stok awal operasional (Simulasi Gudang)',
                'user_id' => $adminGudang->id,
                'tanggal' => date('Y-m-d'),
            ]);
        }
    }
}
