<!DOCTYPE html>
<html>
<head>
    <title>Laporan Purchase Order</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 14pt; }
        .header p { margin: 5px 0 0 0; font-size: 9pt; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; vertical-align: top; }
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
        <p>Laporan Data Purchase Order (PO)</p>
        <p>Tahun: {{ $tahun }} | Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="90">No. PO</th>
                <th width="80">Tanggal</th>
                <th>Detail Item (Kode - Nama - Qty)</th>
                <th width="70">Status</th>
                <th width="80">Dibuat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrders as $index => $po)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="fw-bold">{{ $po->no_po }}</td>
                <td>{{ $po->tanggal->format('d/m/Y') }}</td>
                <td class="text-start">
                    @if($po->items->isEmpty())
                        -
                    @else
                        <ul style="margin: 0; padding-left: 15px;">
                        @foreach($po->items as $item)
                            <li>{{ $item->bahanBaku->kode }} - {{ $item->bahanBaku->nama }} ({{ $item->qty }} {{ $item->bahanBaku->satuan }})</li>
                        @endforeach
                        </ul>
                    @endif
                </td>
                <td>{{ strtoupper($po->status) }}</td>
                <td>{{ $po->user->nama ?? '-' }}</td>
            </tr>
            @endforeach
            @if($purchaseOrders->isEmpty())
            <tr>
                <td colspan="6">Tidak ada data Purchase Order pada tahun {{ $tahun }}.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table style="border: none; width: 100%; margin-top: 50px;">
        <tr>
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center;">
                <p>Sidoarjo, {{ date('d F Y') }}</p>
                <p style="margin-bottom: 70px;">Staff Purchasing</p>
                <p>_______________________</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        Halaman <span class="page-number"></span> | Dicetak oleh Sistem Informasi EOQ/ROP
    </div>
</body>
</html>
