@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<!-- Welcome Header -->
<div class="row mb-3">
    <div class="col-md-6">
        <h4 class="fw-bold mb-0">Selamat datang, <span class="text-primary">{{ $user->nama }}</span></h4>
        <p class="text-muted">Sistem Informasi Pengelolaan Persediaan Bahan Baku — PT. JJ Top Cosmindo</p>
    </div>
    <div class="col-md-6 text-end">
        <form action="{{ route('dashboard') }}" method="GET" class="d-inline-block">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar"></i></span>
                <select name="tahun" class="form-select border-start-0 fw-bold text-primary" onchange="this.form.submit()" style="cursor: pointer;">
                    @foreach($availableYears as $thn)
                        <option value="{{ $thn }}" {{ $selectedYear == $thn ? 'selected' : '' }}>Tahun {{ $thn }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-primary bg-opacity-10 text-primary me-3 rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fas fa-cube fs-5"></i>
                </div>
                <div>
                    <small class="text-muted d-block">Total Bahan Baku</small>
                    <h4 class="mb-0 fw-bold">{{ $totalBahan }}</h4>
                    <small class="text-muted">Jenis</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-success bg-opacity-10 text-success me-3 rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fas fa-database fs-5"></i>
                </div>
                <div>
                    <small class="text-muted d-block">Total Persediaan</small>
                    <h4 class="mb-0 fw-bold">{{ number_format($totalStok, 0, ',', '.') }}</h4>
                    <small class="text-muted">Unit</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-info bg-opacity-10 text-info me-3 rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fas fa-shopping-cart fs-5"></i>
                </div>
                <div>
                    <small class="text-muted d-block">Purchase Order</small>
                    <h4 class="mb-0 fw-bold">{{ $totalPO }}</h4>
                    <small class="text-muted">{{ $poDraft }} Draft</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 h-100 {{ ($statusStockout + $statusReorder) > 0 ? 'border-danger bg-danger bg-opacity-10' : '' }}">
            <div class="d-flex align-items-center">
                <div class="icon-box {{ ($statusStockout + $statusReorder) > 0 ? 'bg-danger text-white' : 'bg-warning bg-opacity-10 text-warning' }} me-3 rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fas fa-exclamation-triangle fs-5"></i>
                </div>
                <div>
                    <small class="{{ ($statusStockout + $statusReorder) > 0 ? 'text-danger fw-bold' : 'text-muted' }} d-block">Peringatan Stok</small>
                    <h4 class="mb-0 fw-bold">{{ $statusStockout + $statusReorder }}</h4>
                    <small class="text-muted">Item</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Donut Chart: Stock Status -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-4"><i class="fas fa-chart-pie text-primary me-2"></i> Status Persediaan</h6>
            <div class="d-flex justify-content-center" style="height: 220px;">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="row text-center mt-3 g-2">
                <div class="col-4">
                    <div class="bg-success bg-opacity-10 rounded-3 py-2">
                        <h5 class="fw-bold text-success mb-0">{{ $statusAman }}</h5>
                        <small class="text-muted">Aman</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-warning bg-opacity-10 rounded-3 py-2">
                        <h5 class="fw-bold text-warning mb-0">{{ $statusReorder }}</h5>
                        <small class="text-muted">Reorder</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-danger bg-opacity-10 rounded-3 py-2">
                        <h5 class="fw-bold text-danger mb-0">{{ $statusStockout }}</h5>
                        <small class="text-muted">Stockout</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Chart: Usage Trend -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 h-100">
            <div class="d-flex justify-content-between mb-4">
                <h6 class="fw-bold"><i class="fas fa-chart-line text-primary me-2"></i> Tren Pemakaian Tahun {{ $chartTahun }}</h6>
            </div>
            <div style="height: 280px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: Recent Activities -->
<div class="row g-4">
    <!-- Recent Transactions -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-exchange-alt text-primary me-2"></i> Transaksi Terbaru</h6>
                @if(auth()->user()->isGudang() || auth()->user()->isManajer())
                <a href="{{ route('stok.riwayat') }}" class="btn btn-sm btn-light">Lihat Semua</a>
                @endif
            </div>
            @if($recentTransaksi->isEmpty())
                <p class="text-muted text-center py-4 small">Belum ada transaksi.</p>
            @else
                <div class="list-group list-group-flush">
                    @foreach($recentTransaksi as $trx)
                    <div class="list-group-item px-0 d-flex align-items-center">
                        <div class="me-3">
                            @if($trx->tipe == 'masuk')
                                <span class="badge bg-success rounded-circle p-2"><i class="fas fa-arrow-down"></i></span>
                            @else
                                <span class="badge bg-danger rounded-circle p-2"><i class="fas fa-arrow-up"></i></span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold small">{{ $trx->bahanBaku->nama }}</div>
                            <small class="text-muted">{{ $trx->tanggal->format('d/m/Y') }} · {{ $trx->user->nama ?? '-' }}</small>
                        </div>
                        <div class="fw-bold {{ $trx->tipe == 'masuk' ? 'text-success' : 'text-danger' }}">
                            {{ $trx->tipe == 'masuk' ? '+' : '-' }}{{ number_format($trx->jumlah, 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Purchase Orders -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-file-invoice text-primary me-2"></i> Purchase Order Terbaru</h6>
                @if(auth()->user()->isPurchasing() || auth()->user()->isManajer())
                <a href="{{ route('po.index') }}" class="btn btn-sm btn-light">Lihat Semua</a>
                @endif
            </div>
            @if($recentPOs->isEmpty())
                <p class="text-muted text-center py-4 small">Belum ada Purchase Order.</p>
            @else
                <div class="list-group list-group-flush">
                    @foreach($recentPOs as $po)
                    <div class="list-group-item px-0 d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2"><i class="fas fa-file-alt"></i></span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold small">{{ $po->no_po }}</div>
                            <small class="text-muted">{{ $po->tanggal->format('d/m/Y') }} · {{ $po->user->nama ?? '-' }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div>{!! $po->status_badge !!}</div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('po.show', $po->id) }}" class="btn btn-sm btn-light text-primary" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($po->status == 'draft')
                                    @if(auth()->user()->isGudang() || auth()->user()->isManajer())
                                    <form action="{{ route('po.updateStatus', $po->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="diterima">
                                        <button class="btn btn-sm btn-primary text-white" title="Terima Barang di Gudang" onclick="return confirm('Konfirmasi terima barang di gudang?')">
                                            <i class="fas fa-box-open me-1"></i> Terima
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Donut Chart: Stock Status
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Aman', 'Reorder', 'Stockout'],
                datasets: [{
                    data: [{{ $statusAman }}, {{ $statusReorder }}, {{ $statusStockout }}],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Line Chart: Monthly Usage Trend
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Total Pemakaian (Unit)',
                    data: {!! json_encode($monthlyUsage) !!},
                    borderColor: '#1a337e',
                    backgroundColor: 'rgba(26, 51, 126, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointBackgroundColor: '#1a337e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return new Intl.NumberFormat('id-ID').format(ctx.parsed.y) + ' Unit';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
