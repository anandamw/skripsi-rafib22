<?php

namespace App\Exports;

use App\Models\BahanBaku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPersediaanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return BahanBaku::with('stok')->orderBy('kode', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Bahan Baku',
            'Kategori',
            'Satuan',
            'Biaya Pesan (S)',
            'Biaya Simpan (H)',
            'Lead Time (Hari)',
            'Stok Aktual'
        ];
    }

    public function map($bahan): array
    {
        return [
            $bahan->kode,
            $bahan->nama,
            $bahan->kategori,
            $bahan->satuan,
            $bahan->s_biaya_pesan,
            $bahan->h_biaya_simpan,
            $bahan->lead_time,
            $bahan->stok ? $bahan->stok->stok_aktual : 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
