@extends('layouts.app')

@section('title', 'Pusat Laporan')
@section('page_title', 'Laporan & Ekspor Data')

@section('content')
<div class="row g-4">
    @if(auth()->user()->isGudang() || auth()->user()->isManajer())
    <!-- Laporan Persediaan -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="icon-box bg-primary bg-opacity-10 text-primary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:70px;height:70px;">
                    <i class="fas fa-boxes fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Laporan Persediaan</h5>
                <p class="text-muted small mb-4">Cetak laporan stok fisik bahan baku terkini, lengkap dengan batas parameter biaya.</p>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('laporan.persediaan', ['format' => 'pdf']) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-file-pdf me-2"></i> Download PDF
                    </a>
                    <a href="{{ route('laporan.persediaan', ['format' => 'excel']) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-2"></i> Download Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan EOQ & ROP -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="icon-box bg-info bg-opacity-10 text-info mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:70px;height:70px;">
                    <i class="fas fa-calculator fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Laporan Kalkulasi EOQ</h5>
                <p class="text-muted small mb-4">Cetak hasil perhitungan kuantitas pemesanan optimal (EOQ), Safety Stock, dan titik ROP.</p>
                
                <form action="{{ route('laporan.eoq') }}" method="GET" target="_blank" class="mb-2">
                    <input type="hidden" name="format" value="pdf">
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-light border-end-0">Tahun</span>
                        <select name="tahun" class="form-select border-start-0">
                            @foreach($tahunList as $thn)
                                <option value="{{ $thn }}">{{ $thn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-file-pdf me-2"></i> Download PDF</button>
                </form>
                <form action="{{ route('laporan.eoq') }}" method="GET">
                    <input type="hidden" name="format" value="excel">
                    <!-- Javascript will copy the year from the select above if needed, but for simplicity we rely on default or user selects again if we want to get fancy, but let's keep it simple -->
                    <button type="submit" class="btn btn-outline-success btn-sm w-100" onclick="this.form.tahun.value = this.form.previousElementSibling.tahun.value;">
                        <input type="hidden" name="tahun" value="{{ $tahunList->first() }}">
                        <i class="fas fa-file-excel me-2"></i> Download Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Laporan TIC -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="icon-box bg-success bg-opacity-10 text-success mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:70px;height:70px;">
                    <i class="fas fa-chart-line fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Laporan Evaluasi TIC</h5>
                <p class="text-muted small mb-4">Cetak laporan efisiensi biaya (Total Inventory Cost) yang membandingkan metode konvensional vs EOQ.</p>
                
                <form action="{{ route('laporan.tic') }}" method="GET" target="_blank" class="mb-2">
                    <input type="hidden" name="format" value="pdf">
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-light border-end-0">Tahun</span>
                        <select name="tahun" class="form-select border-start-0">
                            @foreach($tahunList as $thn)
                                <option value="{{ $thn }}">{{ $thn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-file-pdf me-2"></i> Download PDF</button>
                </form>
                <form action="{{ route('laporan.tic') }}" method="GET">
                    <input type="hidden" name="format" value="excel">
                    <button type="submit" class="btn btn-outline-success btn-sm w-100" onclick="this.form.tahun.value = this.form.previousElementSibling.tahun.value;">
                        <input type="hidden" name="tahun" value="{{ $tahunList->first() }}">
                        <i class="fas fa-file-excel me-2"></i> Download Excel
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->isPurchasing() || auth()->user()->isManajer())
    <!-- Laporan Purchase Order -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="icon-box bg-warning bg-opacity-10 text-warning mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:70px;height:70px;">
                    <i class="fas fa-shopping-cart fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Laporan Purchase Order</h5>
                <p class="text-muted small mb-4">Cetak laporan data Purchase Order berdasarkan periode tahun.</p>
                
                <form action="{{ route('laporan.po') }}" method="GET" target="_blank" class="mb-2">
                    <input type="hidden" name="format" value="pdf">
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-light border-end-0">Tahun</span>
                        <select name="tahun" class="form-select border-start-0">
                            @foreach($tahunList as $thn)
                                <option value="{{ $thn }}">{{ $thn }}</option>
                            @endforeach
                            @if(count($tahunList) == 0)
                                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                            @endif
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-file-pdf me-2"></i> Download PDF</button>
                </form>
                <form action="{{ route('laporan.po') }}" method="GET">
                    <input type="hidden" name="format" value="excel">
                    <button type="submit" class="btn btn-outline-success btn-sm w-100" onclick="this.form.tahun.value = this.form.previousElementSibling.tahun.value;">
                        <input type="hidden" name="tahun" value="{{ count($tahunList) > 0 ? $tahunList->first() : date('Y') }}">
                        <i class="fas fa-file-excel me-2"></i> Download Excel
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
