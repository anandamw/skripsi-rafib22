@extends('layouts.app')

@section('title', 'Manajemen Stok')
@section('page_title', 'Stok Aktual Gudang')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0"><i class="fas fa-cubes me-2"></i> Daftar Stok Bahan Baku</h6>
        <button type="button" class="btn btn-primary btn-sm rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#transaksiModal">
            <i class="fas fa-exchange-alt me-2"></i> Input Transaksi
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="stokTable" style="font-size: 0.9rem;">
            <thead class="table-light">
                <tr>
                    <th width="100">Kode</th>
                    <th>Nama Bahan Baku</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="text-end">Stok Aktual</th>
                    <th class="text-center" width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bahanBakus as $item)
                @php
                    $stok_aktual = $item->stok ? $item->stok->stok_aktual : 0;
                    $rop = $item->safety_stock + $item->lead_time_demand;
                    
                    // Determine color based on stock level vs ROP
                    $stok_class = 'text-success fw-bold'; // Default green
                    if ($stok_aktual == 0) {
                        $stok_class = 'text-danger fw-bold'; // Red if zero
                    } elseif ($stok_aktual <= $rop) {
                        $stok_class = 'text-warning fw-bold'; // Yellow if at or below ROP
                    }
                @endphp
                <tr>
                    <td class="fw-bold">{{ $item->kode }}</td>
                    <td>{{ $item->nama }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $item->kategori }}</span></td>
                    <td>{{ $item->satuan }}</td>
                    <td class="text-end {{ $stok_class }} fs-6">{{ number_format($stok_aktual, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-light text-primary btn-transaksi" 
                            data-id="{{ $item->id }}" data-nama="{{ $item->nama }}" data-satuan="{{ $item->satuan }}" title="Tambah Transaksi">
                            <i class="fas fa-plus-circle"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Input Transaksi -->
<div class="modal fade" id="transaksiModal" tabindex="-1" aria-labelledby="transaksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="transaksiModalLabel"><i class="fas fa-exchange-alt me-2"></i> Input Transaksi Stok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('stok.transaksi.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">TIPE TRANSAKSI</label>
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipe" id="tipeMasuk" value="masuk" checked>
                                <label class="form-check-label text-success fw-bold" for="tipeMasuk"><i class="fas fa-arrow-down me-1"></i> Stok Masuk</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipe" id="tipeKeluar" value="keluar">
                                <label class="form-check-label text-danger fw-bold" for="tipeKeluar"><i class="fas fa-arrow-up me-1"></i> Stok Keluar</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">BAHAN BAKU</label>
                        <select name="bahan_baku_id" id="modal_bahan_baku_id" class="form-select" required>
                            <option value="">Pilih Bahan Baku...</option>
                            @foreach($bahanBakus as $item)
                                <option value="{{ $item->id }}" data-satuan="{{ $item->satuan }}">
                                    {{ $item->kode }} - {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">TANGGAL TRANSAKSI</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">JUMLAH</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" name="jumlah" class="form-control" placeholder="0.00" required>
                                <span class="input-group-text bg-light" id="modal_satuan">Unit</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-bold text-muted">KETERANGAN</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Cth: Penerimaan dari Supplier X / Dipakai untuk Batch Y"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#stokTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
            pageLength: 25
        });

        // Update satuan text based on selected bahan
        $('#modal_bahan_baku_id').on('change', function() {
            var satuan = $(this).find(':selected').data('satuan');
            $('#modal_satuan').text(satuan || 'Unit');
        });

        // Open modal from table row
        $('.btn-transaksi').on('click', function() {
            var id = $(this).data('id');
            var satuan = $(this).data('satuan');
            
            $('#modal_bahan_baku_id').val(id);
            $('#modal_satuan').text(satuan);
            $('#transaksiModal').modal('show');
        });
    });
</script>
@endpush
