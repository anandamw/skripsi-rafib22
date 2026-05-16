<!DOCTYPE html>
<html>
<head>
    <title>Laporan Persediaan</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 16pt; }
        .header p { margin: 5px 0 0 0; font-size: 10pt; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-start { text-align: left; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 50px; text-align: right; font-size: 8pt; color: #777; border-top: 1px solid #ddd; padding-top: 5px;}
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h2>PT. JJ TOP COSMINDO</h2>
        <p>Laporan Persediaan Bahan Baku Aktual</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th class="text-start">Kode & Nama Bahan Baku</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th class="text-end">Stok Aktual</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bahanBakus as $index => $item)
            @php $stok = $item->stok ? $item->stok->stok_aktual : 0; @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-start fw-bold">{{ $item->kode }} - {{ $item->nama }}</td>
                <td>{{ $item->kategori }}</td>
                <td>{{ $item->satuan }}</td>
                <td class="text-end fw-bold">{{ number_format($stok, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="border: none; width: 100%; margin-top: 50px;">
        <tr>
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center;">
                <p>Sidoarjo, {{ date('d F Y') }}</p>
                <p style="margin-bottom: 70px;">Admin Gudang</p>
                <p>_______________________</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        Halaman <span class="page-number"></span> | Dicetak oleh Sistem Informasi EOQ/ROP
    </div>
</body>
</html>
