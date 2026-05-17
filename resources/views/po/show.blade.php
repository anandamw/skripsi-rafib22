@extends('layouts.app')

@section('title', 'Detail PO ' . $po->no_po)
@section('page_title', 'Purchase Order')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- PO Header Card -->
        <div class="card border-0 shadow-sm p-4 mb-3" id="printArea">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="fw-bold mb-1">PT. JJ Top Cosmindo</h4>
                    <p class="text-muted small mb-0">Sistem Informasi Pengelolaan Persediaan</p>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-primary mb-1">{{ $po->no_po }}</h5>
                    <p class="text-muted small mb-0">{{ $po->tanggal->format('d F Y') }}</p>
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-6">
                    <p class="small fw-bold text-muted mb-1">STATUS</p>
                    <div>{!! $po->status_badge !!}</div>
                </div>
                <div class="col-6">
                    <p class="small fw-bold text-muted mb-1">DIBUAT OLEH</p>
                    <p class="mb-0"><i class="fas fa-user-circle me-1 text-muted"></i> {{ $po->user->nama ?? '-' }}</p>
                </div>
            </div>

            @if($po->catatan)
            <div class="alert alert-light border rounded-3 py-2 small mb-4">
                <strong>Catatan:</strong> {{ $po->catatan }}
            </div>
            @endif

            <!-- Items Table -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle" style="font-size: 0.9rem;">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="50">No</th>
                            <th class="text-start">Bahan Baku</th>
                            <th>Satuan</th>
                            <th>Qty Pesan</th>
                            <th class="text-start">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $item->bahanBaku->kode }} - {{ $item->bahanBaku->nama }}</td>
                            <td class="text-center">{{ $item->bahanBaku->satuan }}</td>
                            <td class="text-center fw-bold text-primary fs-6">{{ number_format($item->qty, 0, ',', '.') }}</td>
                            <td>{{ $item->keterangan ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Signatures Area -->
            <div class="row mt-5 text-center" style="page-break-inside: avoid;">
                <div class="col-4">
                    <p class="small fw-bold text-muted">Dibuat oleh,</p>
                    <div style="height: 60px;"></div>
                    <p class="fw-bold border-top pt-2 d-inline-block px-4">Staff Purchasing</p>
                </div>
                <div class="col-4">
                    <p class="small fw-bold text-muted">Disetujui oleh,</p>
                    <div style="height: 60px;"></div>
                    <p class="fw-bold border-top pt-2 d-inline-block px-4">Manajer</p>
                </div>
                <div class="col-4">
                    <p class="small fw-bold text-muted">Diterima oleh,</p>
                    <div style="height: 60px;"></div>
                    <p class="fw-bold border-top pt-2 d-inline-block px-4">Admin Gudang</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons (not printed) -->
        <div class="d-flex justify-content-between no-print">
            <a href="{{ route('po.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Kembali</a>
            <div class="d-flex gap-2">
                @if($po->status == 'draft')
                    @if(auth()->user()->isGudang() || auth()->user()->isManajer())
                    <form action="{{ route('po.updateStatus', $po->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="diterima">
                        <button class="btn btn-primary text-white" onclick="return confirm('Konfirmasi terima barang di gudang?')"><i class="fas fa-box-open me-2"></i> Terima di Gudang</button>
                    </form>
                    @endif
                @endif
                <button class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print me-2"></i> Cetak</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .no-print, .sidebar, .topbar, nav, .alert { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ccc !important; }
        body { background: #fff !important; }
    }
</style>
@endpush
