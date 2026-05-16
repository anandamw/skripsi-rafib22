@extends('layouts.app')

@section('title', 'Input Pemakaian')
@section('page_title', 'Pemakaian Produksi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('pemakaian.index') }}" class="btn btn-light rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h5 class="fw-bold mb-0">Input Pemakaian Bulanan</h5>
            </div>

            <form action="{{ route('pemakaian.store') }}" method="POST">
                @csrf
                
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">PILIH BAHAN BAKU</label>
                        <select name="bahan_baku_id" class="form-select @error('bahan_baku_id') is-invalid @enderror" required>
                            <option value="">Pilih Bahan...</option>
                            @foreach($bahanBaku as $item)
                                <option value="{{ $item->id }}" {{ old('bahan_baku_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->kode }} - {{ $item->nama }} ({{ $item->satuan }})
                                </option>
                            @endforeach
                        </select>
                        @error('bahan_baku_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">TAHUN</label>
                        <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', $currentYear) }}" required>
                        @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">BULAN</label>
                        <select name="bulan" class="form-select @error('bulan') is-invalid @enderror" required>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ old('bulan', $currentMonth) == $i ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                        @error('bulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">TOTAL PEMAKAIAN</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="pemakaian" class="form-control @error('pemakaian') is-invalid @enderror" placeholder="0.00" value="{{ old('pemakaian') }}" required>
                            <span class="input-group-text" id="unit-label">Unit</span>
                        </div>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i> Sistem akan menghitung rata-rata harian (d) secara otomatis.</small>
                        @error('pemakaian') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="fas fa-save me-2"></i> SIMPAN DATA PEMAKAIAN
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
