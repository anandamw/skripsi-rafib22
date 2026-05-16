@extends('layouts.app')

@section('title', 'Histori Pemakaian')
@section('page_title', 'Pemakaian Produksi')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0"><i class="fas fa-history me-2"></i> Monthly Usage History</h6>
        <div class="d-flex gap-2">
            <form action="{{ route('pemakaian.index') }}" method="GET" class="d-flex gap-2">
                <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $thn)
                        <option value="{{ $thn }}" {{ request('tahun') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                    @endforeach
                </select>
            </form>
            @if(auth()->user()->isProduksi() || auth()->user()->isManajer())
            <a href="{{ route('pemakaian.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
                <i class="fas fa-plus me-2"></i> Input Pemakaian
            </a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="pemakaianTable" style="font-size: 0.9rem;">
            <thead class="table-light">
                <tr>
                    <th>Bahan Baku</th>
                    <th>Periode</th>
                    <th class="text-center">Jumlah Hari</th>
                    <th class="text-end">Total Pemakaian</th>
                    <th class="text-end">Rata-rata Harian (d)</th>
                    <th class="text-center" width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pemakaian as $item)
                <tr>
                    <td class="fw-bold text-primary">{{ $item->bahanBaku->nama }}</td>
                    <td>{{ $item->bulan_nama }} {{ $item->tahun }}</td>
                    <td class="text-center">{{ $item->jumlah_hari }} Hari</td>
                    <td class="text-end fw-bold">{{ number_format($item->pemakaian, 2, ',', '.') }} {{ $item->bahanBaku->satuan }}</td>
                    <td class="text-end text-success">
                        {{ number_format($item->d_harian, 2, ',', '.') }} {{ $item->bahanBaku->satuan }}/hari
                    </td>
                    <td class="text-center">
                        <form action="{{ route('pemakaian.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-light text-danger btn-delete" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
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
        $('#pemakaianTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            order: [[1, 'desc']], // Sort by period
            pageLength: 25
        });

        $('.btn-delete').on('click', function() {
            const form = $(this).closest('form');
            Swal.fire({
                title: 'Hapus data ini?',
                text: "Data pemakaian akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
