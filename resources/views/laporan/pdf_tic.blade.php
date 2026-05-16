<!DOCTYPE html>
<html>
<head>
    <title>Laporan Evaluasi TIC {{ $tahun }}</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 16pt; }
        .header p { margin: 5px 0 0 0; font-size: 10pt; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-start { text-align: left; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .bg-red { background-color: #ffeaea; }
        .bg-green { background-color: #eaffee; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 50px; text-align: right; font-size: 8pt; color: #777; border-top: 1px solid #ddd; padding-top: 5px;}
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h2>PT. JJ TOP COSMINDO</h2>
        <p>Laporan Evaluasi Total Inventory Cost (TIC) — Tahun {{ $tahun }}</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="text-start">Bahan Baku</th>
                <th colspan="2" class="bg-red">Kebijakan Saat Ini (Asumsi)</th>
                <th colspan="2" class="bg-green">Metode EOQ (Optimal)</th>
                <th rowspan="2">Efisiensi (Penghematan)</th>
            </tr>
            <tr>
                <th class="bg-red">Q Aktual</th>
                <th class="bg-red">TIC Konvensional</th>
                <th class="bg-green">Q Optimal</th>
                <th class="bg-green">TIC EOQ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evaluasiTic as $item)
            <tr>
                <td class="text-start fw-bold">{{ $item['kode'] }} - {{ $item['nama'] }}</td>
                <td>{{ number_format($item['q_aktual'], 0, ',', '.') }}</td>
                <td class="bg-red">Rp {{ number_format($item['tic_aktual'], 0, ',', '.') }}</td>
                <td>{{ number_format($item['q_eoq'], 0, ',', '.') }}</td>
                <td class="bg-green">Rp {{ number_format($item['tic_eoq'], 0, ',', '.') }}</td>
                <td class="fw-bold">Rp {{ number_format(abs($item['efisiensi']), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end fw-bold">TOTAL PENGHEMATAN TAHUN {{ $tahun }}:</td>
                <td class="fw-bold" style="font-size: 11pt;">Rp {{ number_format($totalPenghematan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table style="border: none; width: 100%; margin-top: 30px;">
        <tr>
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center;">
                <p>Sidoarjo, {{ date('d F Y') }}</p>
                <p style="margin-bottom: 70px;">Manajer Perusahaan</p>
                <p>_______________________</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        Halaman <span class="page-number"></span> | Dicetak oleh Sistem Informasi EOQ/ROP
    </div>
</body>
</html>
