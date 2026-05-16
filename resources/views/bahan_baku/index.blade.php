@extends('layouts.app')

@section('title', 'Daftar Bahan Baku')
@section('page_title', 'Master Data Bahan Baku')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0"><i class="fas fa-boxes me-2"></i> Inventory List</h6>
        @if(auth()->user()->isGudang())
        <a href="{{ route('bahan-baku.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
            <i class="fas fa-plus me-2"></i> Tambah Bahan Baru
        </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="bahanBakuTable" style="font-size: 0.9rem;">
            <thead class="table-light">
                <tr>
                    <th width="80">Kode</th>
                    <th>Nama Bahan Baku</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="text-center">Lead Time</th>
                    <th class="text-center" width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bahanBaku as $item)
                <tr>
                    <td class="fw-bold">{{ $item->kode }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>
                        <span class="badge {{ $item->kategori == 'Lokal' ? 'bg-info bg-opacity-10 text-info' : 'bg-warning bg-opacity-10 text-warning' }} rounded-pill px-3">
                            {{ $item->kategori }}
                        </span>
                    </td>
                    <td>{{ $item->satuan }}</td>
                    <td class="text-center">{{ $item->lead_time }} Hari</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            @if(auth()->user()->isGudang())
                                <a href="{{ route('bahan-baku.edit', $item->id) }}" class="btn btn-sm btn-light text-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('bahan-baku.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-light text-danger btn-delete" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">-</span>
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
        $('#bahanBakuTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            pageLength: 25,
            responsive: true
        });

        // SweetAlert Delete Confirmation
        $('.btn-delete').on('click', function() {
            const form = $(this).closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data bahan baku akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
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
