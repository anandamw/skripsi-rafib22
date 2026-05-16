<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Manajer
        User::create([
            'nama' => 'Manager JJ Top',
            'email' => 'manajer@jjtop.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAJER,
            'aktif' => true,
        ]);

        // 2. Staff Purchasing
        User::create([
            'nama' => 'Staff Purchasing',
            'email' => 'purchasing@jjtop.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PURCHASING,
            'aktif' => true,
        ]);

        // 3. Staff Produksi
        User::create([
            'nama' => 'Staff Produksi',
            'email' => 'produksi@jjtop.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PRODUKSI,
            'aktif' => true,
        ]);

        // 4. Admin Gudang
        User::create([
            'nama' => 'Admin Gudang',
            'email' => 'gudang@jjtop.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_GUDANG,
            'aktif' => true,
        ]);

        // // 5. Bahan Baku
        // $this->call(BahanBakuSeeder::class);

        // // 6. Parameter Bahan (Historis S & H)
        // $this->call(ParameterBahanSeeder::class);

        // // 7. Stok Historis Akhir Tahun
        // $this->call(StokHistorisSeeder::class);

        // // 8. Pemakaian Bulanan (Data Historis 2021-2025)
        // $this->call(PemakaianSeeder::class);

        // // 9. Stok Awal Gudang (Stok Berjalan saat ini)
        // $this->call(StokSeeder::class);
    }
}

