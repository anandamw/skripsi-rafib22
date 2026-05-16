<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use Illuminate\Database\Seeder;

class BahanBakuSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode'=>'BB001','nama'=>'Phenoxyethanol',        'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>6],
            ['kode'=>'BB002','nama'=>'Glycerin',              'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>41],
            ['kode'=>'BB003','nama'=>'Cetyl Alcohol',         'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>9],
            ['kode'=>'BB004','nama'=>'Stearic Acid',          'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>7],
            ['kode'=>'BB005','nama'=>'Titanium Dioxide',      'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>11],
            ['kode'=>'BB006','nama'=>'Sodium Lauryl Sulfate', 'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>15],
            ['kode'=>'BB007','nama'=>'Propylene Glycol',      'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>5],
            ['kode'=>'BB008','nama'=>'Carbomer 940',          'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>14],
            ['kode'=>'BB009','nama'=>'Kaolin Clay',           'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>8],
            ['kode'=>'BB010','nama'=>'Talc Powder',           'satuan'=>'kg','kategori'=>'Lokal','lead_time'=>10],
        ];

        foreach ($data as $item) {
            BahanBaku::create($item);
        }
    }
}
