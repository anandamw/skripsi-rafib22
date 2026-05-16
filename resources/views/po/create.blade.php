@extends('layouts.app')

@section('title', 'Buat Purchase Order')
@section('page_title', 'Purchase Order')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('po.index') }}" class="btn btn-light rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h5 class="fw-bold mb-0">Buat Purchase Order Baru</h5>
                    <small class="text-muted">No. PO: <strong class="text-primary">{{ $noPo }}</strong></small>
                </div>
            </div>

            <form action="{{ route('po.store') }}" method="POST" id="poForm">
                @csrf
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">TANGGAL PO</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-muted">CATATAN (OPSIONAL)</label>
                        <input type="text" name="catatan" class="form-control" placeholder="Cth: Pemesanan rutin bulanan...">
                    </div>
                </div>

                <!-- Items Section -->
                <div class="alert alert-info border-0 rounded-3 py-2 small">
                    <i class="fas fa-info-circle me-2"></i> Pilih bahan baku yang ingin dipesan. Sistem akan menyarankan kuantitas berdasarkan EOQ yang telah dihitung.
                </div>

                <div id="po-items-container">
                    <div class="po-item card bg-light border-0 rounded-3 p-3 mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted">BAHAN BAKU</label>
                                <select name="items[0][bahan_baku_id]" class="form-select bahan-select" required>
                                    <option value="">Pilih Bahan...</option>
                                    @foreach($bahanBakus as $b)
                                        @php
                                            $stokAktual = $b->stok ? $b->stok->stok_aktual : 0;
                                            $eoqVal = isset($eoqData[$b->id]) ? $eoqData[$b->id]['eoq'] : '-';
                                        @endphp
                                        <option value="{{ $b->id }}" data-eoq="{{ $eoqVal }}" data-stok="{{ $stokAktual }}" data-satuan="{{ $b->satuan }}">
                                            {{ $b->kode }} - {{ $b->nama }} (Stok: {{ $stokAktual }} {{ $b->satuan }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">QTY PESAN</label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" name="items[0][qty]" class="form-control qty-input" placeholder="0" required>
                                    <span class="input-group-text satuan-label">Unit</span>
                                </div>
                                <small class="text-success eoq-suggestion fw-bold"></small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">KETERANGAN</label>
                                <input type="text" name="items[0][keterangan]" class="form-control" placeholder="Opsional...">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-sm btn-light text-danger remove-item" title="Hapus Baris" disabled>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm mb-4" id="add-item">
                    <i class="fas fa-plus me-2"></i> Tambah Bahan Baku Lain
                </button>

                <hr>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                    <i class="fas fa-paper-plane me-2"></i> BUAT PURCHASE ORDER
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let itemIndex = 1;

    // Populate EOQ suggestion when bahan is selected
    $(document).on('change', '.bahan-select', function() {
        const selected = $(this).find(':selected');
        const eoq = selected.data('eoq');
        const satuan = selected.data('satuan');
        const row = $(this).closest('.po-item');

        row.find('.satuan-label').text(satuan || 'Unit');
        
        if (eoq && eoq !== '-') {
            row.find('.eoq-suggestion').html('<i class="fas fa-lightbulb me-1"></i> EOQ: ' + eoq);
            row.find('.qty-input').val(eoq);
        } else {
            row.find('.eoq-suggestion').text('');
        }
    });

    // Add new item row
    $('#add-item').on('click', function() {
        const firstItem = $('.po-item:first').clone();
        firstItem.find('select').attr('name', `items[${itemIndex}][bahan_baku_id]`).val('');
        firstItem.find('.qty-input').attr('name', `items[${itemIndex}][qty]`).val('');
        firstItem.find('input[type=text]').attr('name', `items[${itemIndex}][keterangan]`).val('');
        firstItem.find('.eoq-suggestion').text('');
        firstItem.find('.satuan-label').text('Unit');
        firstItem.find('.remove-item').prop('disabled', false);
        
        $('#po-items-container').append(firstItem);
        itemIndex++;
        updateRemoveButtons();
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.po-item').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        if ($('.po-item').length <= 1) {
            $('.remove-item').prop('disabled', true);
        } else {
            $('.remove-item').prop('disabled', false);
        }
    }
</script>
@endpush
