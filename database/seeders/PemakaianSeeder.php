<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\PemakaianBulanan;
use Illuminate\Database\Seeder;

class PemakaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bahanBakus = BahanBaku::all();

        if ($bahanBakus->isEmpty()) {
            return;
        }

        $tahun_list = [2021, 2022, 2023, 2024, 2025];

        foreach ($tahun_list as $tahun) {
            foreach ($bahanBakus as $bahan) {
                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    // Generate random usage for dummy data, tailored a bit based on material
                    $baseUsage = 0;
                    if ($bahan->kode === 'BB001') {
                        $baseUsage = rand(300, 450);
                    } elseif ($bahan->kode === 'BB002') {
                        $baseUsage = rand(800, 1500);
                    } elseif ($bahan->kode === 'BB003') {
                        $baseUsage = rand(100, 300);
                    } elseif ($bahan->kode === 'BB004') {
                        $baseUsage = rand(150, 400);
                    } elseif ($bahan->kode === 'BB007') {
                        $baseUsage = rand(2000, 4000);
                    } else {
                        $baseUsage = rand(100, 500);
                    }

                    PemakaianBulanan::create([
                        'bahan_baku_id' => $bahan->id,
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                        'pemakaian' => $baseUsage,
                        // jumlah_hari and d_harian will be auto-calculated by the model's booted method
                    ]);
                }
            }
        }
    }
}
