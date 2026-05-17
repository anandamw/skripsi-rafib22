@extends('layouts.app')

@section('title', 'Purchase Order')
@section('page_title', 'Manajemen Purchase Order')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0"><i class="fas fa-shopping-cart me-2"></i> Daftar Purchase Order</h6>

        <!-- Tombol Aksi Sesuai Role -->
        @if(!auth()->user()->isGudang())
            <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
                <i class="fas fa-plus me-2"></i> Buat PO Baru
            </a>
        @endif

    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="poTable" style="font-size: 0.9rem;">
            <thead class="table-light">
                <tr>
                    <th>No. PO</th>
                    <th>Tanggal Order</th>
                    <th>Jumlah Item</th>
                    <th>Estimasi Tiba &amp; Countdown</th>
                    <th class="text-center">Status</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-center" width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrders as $po)
                @php
                    // Menghitung Max Lead Time dari item PO
                    $max_lt = 0;
                    foreach($po->items as $item) {
                        if ($item->bahanBaku && $item->bahanBaku->lead_time > $max_lt) {
                            $max_lt = $item->bahanBaku->lead_time;
                        }
                    }
                    $estimasi_tiba = $po->tanggal->copy()->addDays($max_lt);
                    $hari_ini = now();
                    $selisih = $hari_ini->diffInDays($estimasi_tiba, false); // false agar negatif jika terlambat
                    
                    $countdown_html = "";
                    if ($po->status == 'diterima') {
                        $terima = \Carbon\Carbon::parse($po->tanggal_diterima);
                        $selisih_terima = $terima->diffInDays($estimasi_tiba, false);
                        if ($selisih_terima < 0) {
                            $countdown_html = '<span class="badge bg-danger rounded-pill"><i class="fas fa-exclamation-triangle me-1"></i> Terlambat ' . abs(round($selisih_terima)) . ' Hari</span>';
                        } else {
                            $countdown_html = '<span class="badge bg-success rounded-pill"><i class="fas fa-check-circle me-1"></i> Tepat Waktu</span>';
                        }
                        $countdown_html .= '<div class="small text-muted mt-1" style="font-size: 0.75rem;">Diterima: ' . $terima->format('d/m/Y') . '</div>';
                    } elseif ($po->status == 'dibatalkan') {
                        $countdown_html = '<span class="badge bg-secondary rounded-pill">-</span>';
                    } else {
                        if ($selisih > 0) {
                            $countdown_html = '<span class="badge bg-info text-dark rounded-pill"><i class="fas fa-clock me-1"></i> Sisa ' . round($selisih) . ' Hari</span>';
                        } elseif ($selisih == 0) {
                            $countdown_html = '<span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-bell me-1"></i> Tenggat Hari Ini</span>';
                        } else {
                            $countdown_html = '<span class="badge bg-danger rounded-pill"><i class="fas fa-radiation me-1"></i> Terlambat ' . abs(round($selisih)) . ' Hari</span>';
                        }
                        $countdown_html .= '<div class="small text-muted mt-1" style="font-size: 0.75rem;">Tenggat: ' . $estimasi_tiba->format('d/m/Y') . '</div>';
                    }
                @endphp
                <tr>
                    <td class="fw-bold text-primary">{{ $po->no_po }}</td>
                    <td>{{ $po->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $po->items->count() }} Bahan</td>
                    <td>{!! $countdown_html !!}</td>
                    <td class="text-center">{!! $po->status_badge !!}</td>
                    <td class="small text-muted"><i class="fas fa-user-circle me-1"></i> {{ $po->user->nama ?? '-' }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('po.show', $po->id) }}" class="btn btn-sm btn-light text-primary" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($po->status == 'draft')
                                @if(auth()->user()->isGudang() || auth()->user()->isManajer())
                                <form action="{{ route('po.updateStatus', $po->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="diterima">
                                    <button class="btn btn-sm btn-primary text-white" title="Terima Barang di Gudang" onclick="return confirm('Konfirmasi terima barang di gudang?')">
                                        <i class="fas fa-box-open me-1"></i> Terima
                                    </button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#poTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
            order: [[1, 'desc']],
            pageLength: 25
        });
    });
</script>
@endpush
