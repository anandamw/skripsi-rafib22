@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page_title', 'Manajemen Stok')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0"><i class="fas fa-history me-2"></i> Riwayat Transaksi Keluar/Masuk</h6>
        <a href="{{ route('stok.index') }}" class="btn btn-light btn-sm rounded-3 px-3">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Stok
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="riwayatTable" style="font-size: 0.9rem;">
            <thead class="table-light">
                <tr>
                    <th width="120">Tanggal</th>
                    <th width="100" class="text-center">Tipe</th>
                    <th>Bahan Baku</th>
                    <th class="text-end">Jumlah</th>
                    <th>Keterangan</th>
                    <th>Admin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaksis as $item)
                <tr>
                    <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td class="text-center">
                        @if($item->tipe == 'masuk')
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3"><i class="fas fa-arrow-down me-1"></i> MASUK</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3"><i class="fas fa-arrow-up me-1"></i> KELUAR</span>
                        @endif
                    </td>
                    <td class="fw-bold text-primary">{{ $item->bahanBaku->kode }} - {{ $item->bahanBaku->nama }}</td>
                    <td class="text-end fw-bold {{ $item->tipe == 'masuk' ? 'text-success' : 'text-danger' }}">
                        {{ $item->tipe == 'masuk' ? '+' : '-' }}{{ number_format($item->jumlah, 0, ',', '.') }} {{ $item->bahanBaku->satuan }}
                    </td>
                    <td>{{ $item->keterangan ?: '-' }}</td>
                    <td class="small text-muted"><i class="fas fa-user-circle me-1"></i> {{ $item->user->nama ?? 'Sistem' }}</td>
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
        $('#riwayatTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
            order: [[0, 'desc']], // Sort by date descending
            pageLength: 25
        });
    });
</script>
@endpush
