<?php

namespace Database\Seeders;

use App\Models\StokHistoris;
use App\Models\BahanBaku;
use Illuminate\Database\Seeder;

class StokHistorisSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['BB001', 2021,     0], ['BB001', 2022,   611], ['BB001', 2023,  5530],
            ['BB001', 2024,     0], ['BB001', 2025,  4121],

            ['BB002', 2021, 15322], ['BB002', 2022,  6496], ['BB002', 2023, 16278],
            ['BB002', 2024, 23217], ['BB002', 2025, 12645],

            ['BB003', 2021,  2265], ['BB003', 2022,  1928], ['BB003', 2023,  1837],
            ['BB003', 2024,     0], ['BB003', 2025,  1365],

            ['BB004', 2021,     0], ['BB004', 2022,  3034], ['BB004', 2023,  1488],
            ['BB004', 2024,     0], ['BB004', 2025,   518],

            ['BB005', 2021,  2953], ['BB005', 2022,  2881], ['BB005', 2023,   465],
            ['BB005', 2024,  1130], ['BB005', 2025,  3097],

            ['BB006', 2021,   131], ['BB006', 2022,     0], ['BB006', 2023,     0],
            ['BB006', 2024,  1299], ['BB006', 2025,   540],

            ['BB007', 2021,     0], ['BB007', 2022,  7175], ['BB007', 2023,  7360],
            ['BB007', 2024,  4147], ['BB007', 2025, 10845],

            ['BB008', 2021,     0], ['BB008', 2022,     0], ['BB008', 2023,  6679],
            ['BB008', 2024,  4599], ['BB008', 2025,  4757],

            ['BB009', 2021,  2352], ['BB009', 2022,   987], ['BB009', 2023,  1466],
            ['BB009', 2024,  1824], ['BB009', 2025,  1269],

            ['BB010', 2021,  1331], ['BB010', 2022,     0], ['BB010', 2023,  2084],
            ['BB010', 2024,  4343], ['BB010', 2025,  2382],
        ];

        $kodeToId = BahanBaku::pluck('id', 'kode')->toArray();

        $rows = [];
        foreach ($data as [$kode, $tahun, $stok]) {
            if (!isset($kodeToId[$kode])) continue;
            $rows[] = [
                'bahan_baku_id' => $kodeToId[$kode],
                'tahun'         => $tahun,
                'stok_aktual'   => $stok,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        StokHistoris::insert($rows);
    }
}
