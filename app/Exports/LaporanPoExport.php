<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPoExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function collection()
    {
        return PurchaseOrder::with(['items.bahanBaku', 'user'])
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            ['LAPORAN DATA PURCHASE ORDER (PO)'],
            ['Tahun: ' . $this->tahun],
            [],
            [
                'No.',
                'No. PO',
                'Tanggal',
                'Status',
                'Dibuat Oleh',
                'Kode Bahan',
                'Nama Bahan',
                'Qty',
                'Keterangan Item',
            ]
        ];
    }

    public function map($po): array
    {
        $rows = [];
        $index = 1;
        
        if ($po->items->isEmpty()) {
            return [
                [
                    '',
                    $po->no_po,
                    $po->tanggal->format('d/m/Y'),
                    strtoupper($po->status),
                    $po->user->nama ?? '-',
                    '-',
                    '-',
                    '-',
                    '-'
                ]
            ];
        }

        foreach ($po->items as $item) {
            $rows[] = [
                $index === 1 ? '' : '', // no urut can be left blank for multiple items, or handled differently
                $index === 1 ? $po->no_po : '',
                $index === 1 ? $po->tanggal->format('d/m/Y') : '',
                $index === 1 ? strtoupper($po->status) : '',
                $index === 1 ? ($po->user->nama ?? '-') : '',
                $item->bahanBaku->kode,
                $item->bahanBaku->nama,
                $item->qty,
                $item->keterangan ?? '-'
            ];
            $index++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']]],
        ];
    }
}
