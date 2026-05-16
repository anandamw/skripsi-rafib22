@extends('layouts.app')

@section('title', 'Purchase Order')
@section('page_title', 'Manajemen Purchase Order')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0"><i class="fas fa-shopping-cart me-2"></i> Daftar Purchase Order</h6>
        <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
            <i class="fas fa-plus me-2"></i> Buat PO Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="poTable" style="font-size: 0.9rem;">
            <thead class="table-light">
                <tr>
                    <th>No. PO</th>
                    <th>Tanggal</th>
                    <th>Jumlah Item</th>
                    <th class="text-center">Status</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-center" width="130">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrders as $po)
                <tr>
                    <td class="fw-bold text-primary">{{ $po->no_po }}</td>
                    <td>{{ $po->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $po->items->count() }} Bahan</td>
                    <td class="text-center">{!! $po->status_badge !!}</td>
                    <td class="small text-muted"><i class="fas fa-user-circle me-1"></i> {{ $po->user->nama ?? '-' }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('po.show', $po->id) }}" class="btn btn-sm btn-light text-primary" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($po->status == 'draft')
                            <form action="{{ route('po.updateStatus', $po->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="disetujui">
                                <button class="btn btn-sm btn-light text-success" title="Setujui PO" onclick="return confirm('Setujui PO ini?')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
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
