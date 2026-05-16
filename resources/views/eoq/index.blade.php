@extends('layouts.app')

@section('title', 'Kalkulasi EOQ & ROP')
@section('page_title', 'Analisis EOQ & ROP')

@section('content')
<div class="card border-0 shadow-sm p-4 mb-4 bg-primary text-white" style="background: linear-gradient(135deg, var(--sidebar-bg) 0%, #2a4ec1 100%);">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="fw-bold mb-2"><i class="fas fa-calculator me-2"></i> Engine Kalkulasi EOQ</h5>
            <p class="mb-0 text-white-50 small">Sistem akan menghitung D (Permintaan Tahunan), d (Permintaan Harian), σ_d, EOQ, Safety Stock, dan Reorder Point berdasarkan data pemakaian historis yang ada pada sistem.</p>
        </div>
        <div class="col-md-4 text-end">
            <form action="{{ route('eoq.calculate') }}" method="POST" id="form-calculate">
                @csrf
                <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
                <button type="button" class="btn btn-warning fw-bold px-4 rounded-pill shadow" id="btn-calculate">
                    <i class="fas fa-sync-alt me-2"></i> PROSES KALKULASI
                </button>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0">Hasil Analisis Tahun {{ $selectedTahun }}</h6>
        <form action="{{ route('eoq.index') }}" method="GET" class="d-flex gap-2">
            <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                @if($tahunList->isEmpty())
                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                @endif
                @foreach($tahunList as $thn)
                    <option value="{{ $thn }}" {{ $selectedTahun == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($kalkulasi->isEmpty())
        <div class="alert alert-warning border-0 rounded-3 text-center py-4">
            <i class="fas fa-exclamation-circle fs-3 mb-2 d-block text-warning"></i>
            <h6 class="fw-bold">Belum Ada Data Kalkulasi</h6>
            <p class="mb-0 small">Belum ada hasil analisis untuk tahun {{ $selectedTahun }}. Silakan klik tombol <strong>"Proses Kalkulasi"</strong> di atas.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center" id="eoqTable" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="align-middle text-start">Bahan Baku</th>
                        <th colspan="3" class="bg-primary bg-opacity-10">Data Demand</th>
                        <th colspan="4" class="bg-success bg-opacity-10">Parameter Optimal</th>
                        <th rowspan="2" class="align-middle bg-light">Status Inventori</th>
                    </tr>
                    <tr>
                        <th class="bg-primary bg-opacity-10" title="Permintaan Tahunan">D (Thn)</th>
                        <th class="bg-primary bg-opacity-10" title="Rata-rata Permintaan Harian">d̄ (Hari)</th>
                        <th class="bg-primary bg-opacity-10" title="Standar Deviasi Permintaan Harian">σ_d</th>
                        <th class="bg-success bg-opacity-10 fw-bold" title="Economic Order Quantity">EOQ (Q)</th>
                        <th class="bg-success bg-opacity-10" title="Standar Deviasi selama Lead Time">σ_DL</th>
                        <th class="bg-success bg-opacity-10 fw-bold" title="Safety Stock">SS</th>
                        <th class="bg-success bg-opacity-10 fw-bold" title="Reorder Point">ROP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kalkulasi as $item)
                    @php
                        $stokHistoris = $item->bahanBaku->stokTahun($selectedTahun);
                        $stok = $stokHistoris ? $stokHistoris->stok_aktual : 0;
                        $rop = $item->rop;
                        $ss = $item->safety_stock;
                        
                        $status_badge = '';
                        $status_text = '';
                        if ($stok <= $ss) {
                            $status_badge = 'bg-danger';
                            $status_text = 'STOCKOUT';
                        } elseif ($stok <= $rop) {
                            $status_badge = 'bg-warning text-dark';
                            $status_text = 'REORDER';
                        } else {
                            $status_badge = 'bg-success';
                            $status_text = 'AMAN';
                        }
                    @endphp
                    <tr>
                    @php
                        $param = $item->bahanBaku->parameterTahun($selectedTahun);
                        $s_val = $param ? 'Rp ' . number_format($param->biaya_pesan, 0, ',', '.') : '-';
                        $h_val = $param ? 'Rp ' . number_format($param->biaya_simpan, 0, ',', '.') : '-';
                    @endphp
                        <td class="text-start fw-bold text-primary">
                            {{ $item->bahanBaku->kode }} - {{ $item->bahanBaku->nama }}
                            <div class="text-muted small fw-normal">S: {{ $s_val }} | H: {{ $h_val }} | LT: {{ $item->bahanBaku->lead_time }}hr</div>
                        </td>
                        <td>{{ number_format($item->d_tahunan, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->d_harian_avg, 2, ',', '.') }}</td>
                        <td>{{ number_format($item->sigma_d, 2, ',', '.') }}</td>
                        <td class="fw-bold bg-success bg-opacity-10 text-success fs-6">{{ number_format($item->eoq, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->sigma_dl, 2, ',', '.') }}</td>
                        <td class="fw-bold">{{ number_format($item->safety_stock, 0, ',', '.') }}</td>
                        <td class="fw-bold bg-warning bg-opacity-10 text-danger">{{ number_format($item->rop, 0, ',', '.') }}</td>
                        <td class="bg-light">
                            <span class="badge {{ $status_badge }} rounded-pill px-3">{{ $status_text }}</span>
                            <div class="small mt-1 text-muted">Stok: {{ number_format($stok, 0, ',', '.') }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        
        <div class="alert alert-info border-0 rounded-3 mt-4 small">
            <i class="fas fa-info-circle me-2"></i> <strong>Keterangan Rumus:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>EOQ</strong> = Akar(2 * D * S / H)</li>
                <li><strong>SS (Safety Stock)</strong> = 1.65 * σ_DL (asumsi service level 95%)</li>
                <li><strong>ROP (Reorder Point)</strong> = (d̄ * Lead Time) + SS</li>
            </ul>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        @if(!$kalkulasi->isEmpty())
        $('#eoqTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            pageLength: 25,
            ordering: false // Disable sorting to keep the column grouping clean, or configure specific targets
        });
        @endif

        $('#btn-calculate').on('click', function() {
            Swal.fire({
                title: 'Proses Kalkulasi?',
                text: "Sistem akan menghitung ulang EOQ, Safety Stock, dan ROP untuk tahun {{ $selectedTahun }}. Proses ini mungkin memakan waktu beberapa detik.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a337e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Proses Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang Memproses...',
                        html: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    $('#form-calculate').submit();
                }
            });
        });
    });
</script>
@endpush
