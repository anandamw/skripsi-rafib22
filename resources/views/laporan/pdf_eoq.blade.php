<!DOCTYPE html>
<html>
<head>
    <title>Laporan EOQ & ROP {{ $tahun }}</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 16pt; }
        .header p { margin: 5px 0 0 0; font-size: 10pt; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .bg-gray { background-color: #eee; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 50px; text-align: right; font-size: 8pt; color: #777; border-top: 1px solid #ddd; padding-top: 5px;}
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h2>PT. JJ TOP COSMINDO</h2>
        <p>Laporan Hasil Kalkulasi EOQ & ROP — Tahun {{ $tahun }}</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="text-start">Bahan Baku</th>
                <th colspan="2">Demand</th>
                <th colspan="4" class="bg-gray">Parameter Optimal</th>
                <th rowspan="2">Status</th>
            </tr>
            <tr>
                <th>D (Thn)</th>
                <th>σ_d</th>
                <th class="bg-gray">EOQ</th>
                <th class="bg-gray">σ_DL</th>
                <th class="bg-gray">SS</th>
                <th class="bg-gray">ROP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kalkulasi as $item)
            @php
                $stok = $item->bahanBaku->stok ? $item->bahanBaku->stok->stok_aktual : 0;
                $status = 'Aman';
                if ($stok <= $item->safety_stock) $status = 'Stockout';
                elseif ($stok <= $item->rop) $status = 'Reorder';
            @endphp
            <tr>
                <td class="text-start fw-bold">{{ $item->bahanBaku->kode }} - {{ $item->bahanBaku->nama }}</td>
                <td>{{ number_format($item->d_tahunan, 0, ',', '.') }}</td>
                <td>{{ number_format($item->sigma_d, 2, ',', '.') }}</td>
                <td class="bg-gray fw-bold">{{ number_format($item->eoq, 0, ',', '.') }}</td>
                <td class="bg-gray">{{ number_format($item->sigma_dl, 2, ',', '.') }}</td>
                <td class="bg-gray fw-bold">{{ number_format($item->safety_stock, 0, ',', '.') }}</td>
                <td class="bg-gray fw-bold">{{ number_format($item->rop, 0, ',', '.') }}</td>
                <td>{{ $status }} ({{ number_format($stok, 0, ',', '.') }})</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="border: none; width: 100%; margin-top: 30px;">
        <tr>
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center;">
                <p>Sidoarjo, {{ date('d F Y') }}</p>
                <p style="margin-bottom: 70px;">Manajer / Staff Purchasing</p>
                <p>_______________________</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        Halaman <span class="page-number"></span> | Dicetak oleh Sistem Informasi EOQ/ROP
    </div>
</body>
</html>
