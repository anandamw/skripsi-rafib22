@extends('layouts.app')

@section('title', 'Tambah Bahan Baku')
@section('page_title', 'Master Data Bahan Baku')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('bahan-baku.index') }}" class="btn btn-light rounded-circle me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h5 class="fw-bold mb-0">Tambah Bahan Baku Baru</h5>
            </div>

            <form action="{{ route('bahan-baku.store') }}" method="POST">
                @csrf
                
                <div class="row g-3">
                    <!-- Basic Info -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">KODE BAHAN</label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode', $kodeOtomatis) }}" readonly style="background-color: #f8f9fa;">
                        @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-muted">NAMA BAHAN BAKU</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Masukkan nama bahan..." value="{{ old('nama') }}" required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">KATEGORI</label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                            <option value="">Pilih Kategori...</option>
                            <option value="Lokal" {{ old('kategori') == 'Lokal' ? 'selected' : '' }}>Lokal</option>
                            <option value="Impor" {{ old('kategori') == 'Impor' ? 'selected' : '' }}>Impor</option>
                        </select>
                        @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">SATUAN</label>
                        <select name="satuan" class="form-select @error('satuan') is-invalid @enderror" required>
                            <option value="">Pilih Satuan...</option>
                            <option value="Kg"     {{ old('satuan') == 'Kg'     ? 'selected' : '' }}>Kg (Kilogram)</option>
                            <option value="Gram"   {{ old('satuan') == 'Gram'   ? 'selected' : '' }}>Gram</option>
                            <option value="Liter"  {{ old('satuan') == 'Liter'  ? 'selected' : '' }}>Liter</option>
                            <option value="mL"     {{ old('satuan') == 'mL'     ? 'selected' : '' }}>mL (Mililiter)</option>
                            <option value="Pcs"    {{ old('satuan') == 'Pcs'    ? 'selected' : '' }}>Pcs (Pieces)</option>
                            <option value="Lusin"  {{ old('satuan') == 'Lusin'  ? 'selected' : '' }}>Lusin</option>
                            <option value="Karton" {{ old('satuan') == 'Karton' ? 'selected' : '' }}>Karton</option>
                            <option value="Sak"    {{ old('satuan') == 'Sak'    ? 'selected' : '' }}>Sak</option>
                            <option value="Drum"   {{ old('satuan') == 'Drum'   ? 'selected' : '' }}>Drum</option>
                            <option value="Meter"  {{ old('satuan') == 'Meter'  ? 'selected' : '' }}>Meter</option>
                        </select>
                        @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- EOQ Parameters -->
                    <div class="col-12 mt-4">
                        <div class="alert alert-info border-0 rounded-3 bg-opacity-10 py-2">
                            <i class="fas fa-calculator me-2"></i> Parameter Kalkulasi EOQ & ROP
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">LEAD TIME (LT)</label>
                        <div class="input-group">
                            <input type="number" name="lead_time" class="form-control @error('lead_time') is-invalid @enderror" placeholder="0" value="{{ old('lead_time') }}" required>
                            <span class="input-group-text">Hari</span>
                        </div>
                        @error('lead_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="fas fa-save me-2"></i> SIMPAN BAHAN BAKU
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
