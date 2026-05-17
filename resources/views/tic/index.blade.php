@extends('layouts.app')

@section('title', 'Evaluasi TIC')
@section('page_title', 'Total Inventory Cost (TIC)')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="fw-bold mb-1"><i class="fas fa-chart-bar me-2 text-primary"></i> Evaluasi Efisiensi Biaya (Tahun {{ $selectedTahun }})</h6>
                    <p class="text-muted small mb-0">Perbandingan Total Biaya Persediaan (TIC) antara Kebijakan Konvensional vs Metode EOQ.</p>
                </div>
                <form action="{{ route('tic.index') }}" method="GET" class="d-flex gap-2">
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

            @if(empty($evaluasiTic))
                <div class="alert alert-warning border-0 rounded-3 text-center py-4">
                    <i class="fas fa-exclamation-circle fs-3 mb-2 d-block text-warning"></i>
                    <h6 class="fw-bold">Belum Ada Data Kalkulasi</h6>
                    <p class="mb-0 small">Belum ada hasil analisis EOQ untuk tahun {{ $selectedTahun }}. Silakan lakukan kalkulasi EOQ terlebih dahulu.</p>
                </div>
            @else
                <div class="row">
                    <!-- Chart Section -->
                    <div class="col-lg-12 mb-5">
                        <div style="height: 350px;">
                            <canvas id="ticChart"></canvas>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle text-center" id="ticTable" style="font-size: 0.82rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2" class="align-middle text-start" style="min-width: 220px;">Bahan Baku</th>
                                        <th colspan="2" class="bg-danger bg-opacity-10 text-danger">Kebijakan Saat Ini (F=12x)</th>
                                        <th colspan="2" class="bg-success bg-opacity-10 text-success fw-bold">Metode EOQ (Optimal)</th>
                                        <th colspan="2" class="bg-warning bg-opacity-10 text-warning-emphasis">Q Kecil (50% EOQ)</th>
                                        <th colspan="2" class="bg-info bg-opacity-10 text-info-emphasis">Q Besar (150% EOQ)</th>
                                        <th rowspan="2" class="align-middle bg-primary bg-opacity-10 text-primary fw-bold">Efisiensi (Hemat Rp)</th>
                                        <th rowspan="2" class="align-middle bg-primary bg-opacity-10 text-primary fw-bold">Hemat (%)</th>
                                        <th rowspan="2" class="align-middle bg-success bg-opacity-20 text-success fw-bold" style="min-width: 140px;">Rekomendasi</th>
                                    </tr>
                                    <tr>
                                        <th class="bg-danger bg-opacity-10" title="Kuantitas Order Aktual">Q Aktual</th>
                                        <th class="bg-danger bg-opacity-10 fw-bold" title="Total Inventory Cost Aktual">TIC Konvensional</th>
                                        <th class="bg-success bg-opacity-10 fw-bold" title="Economic Order Quantity">Q EOQ</th>
                                        <th class="bg-success bg-opacity-10 fw-bold" title="Total Inventory Cost EOQ">TIC EOQ</th>
                                        <th class="bg-warning bg-opacity-10" title="Kuantitas 50% EOQ">Q Kecil</th>
                                        <th class="bg-warning bg-opacity-10" title="TIC Q Kecil">TIC Q Kecil</th>
                                        <th class="bg-info bg-opacity-10" title="Kuantitas 150% EOQ">Q Besar</th>
                                        <th class="bg-info bg-opacity-10" title="TIC Q Besar">TIC Q Besar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalPenghematan = 0; @endphp
                                    @foreach($evaluasiTic as $item)
                                    @php $totalPenghematan += $item['efisiensi']; @endphp
                                    <tr>
                                        <td class="text-start fw-bold text-dark">{{ $item['kode'] }} - {{ $item['bahan_baku'] }}</td>
                                        <td>{{ number_format($item['q_aktual'], 0, ',', '.') }}</td>
                                        <td class="text-danger fw-bold">Rp {{ number_format($item['tic_aktual'], 0, ',', '.') }}</td>
                                        <td class="bg-success bg-opacity-10 fw-bold text-success">{{ number_format($item['q_eoq'], 0, ',', '.') }}</td>
                                        <td class="bg-success bg-opacity-10 fw-bold text-success">Rp {{ number_format($item['tic_eoq'], 0, ',', '.') }}</td>
                                        <td>{{ number_format($item['q_kecil'], 0, ',', '.') }}</td>
                                        <td class="text-muted">Rp {{ number_format($item['tic_kecil'], 0, ',', '.') }}</td>
                                        <td>{{ number_format($item['q_besar'], 0, ',', '.') }}</td>
                                        <td class="text-muted">Rp {{ number_format($item['tic_besar'], 0, ',', '.') }}</td>
                                        <td class="fw-bold bg-primary bg-opacity-10 text-primary fs-6">
                                            @if($item['efisiensi'] > 0)
                                                <i class="fas fa-arrow-down text-success me-1"></i>
                                            @elseif($item['efisiensi'] < 0)
                                                <i class="fas fa-arrow-up text-danger me-1"></i>
                                            @endif
                                            Rp {{ number_format(abs($item['efisiensi']), 0, ',', '.') }}
                                        </td>
                                        <td class="fw-bold bg-primary bg-opacity-10 text-primary fs-6">
                                            {{ $item['hemat_persen'] }}%
                                        </td>
                                        <td class="fw-bold bg-success bg-opacity-20 text-success">
                                            <span class="badge bg-success py-2 px-3 fs-6"><i class="fas fa-check-circle me-1"></i> {{ $item['rekomendasi'] }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="9" class="text-end fw-bold align-middle fs-6">TOTAL PENGHEMATAN TAHUN {{ $selectedTahun }}:</td>
                                        <td colspan="3" class="fw-bold fs-5 text-success text-start align-middle">Rp {{ number_format($totalPenghematan, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(!empty($evaluasiTic))
<script>
    $(document).ready(function() {
        $('#ticTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
            paging: false,
            searching: false,
            info: false,
            ordering: false
        });

        // Prepare Data for Chart
        const labels = {!! json_encode(array_column($evaluasiTic, 'kode')) !!};
        const ticAktual = {!! json_encode(array_column($evaluasiTic, 'tic_aktual')) !!};
        const ticEoq = {!! json_encode(array_column($evaluasiTic, 'tic_eoq')) !!};

        const ctx = document.getElementById('ticChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'TIC Konvensional (Rp)',
                        data: ticAktual,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)', // Danger color
                        borderColor: 'rgb(220, 53, 69)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'TIC Metode EOQ (Rp)',
                        data: ticEoq,
                        backgroundColor: 'rgba(25, 135, 84, 0.8)', // Success color
                        borderColor: 'rgb(25, 135, 84)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Inter', sans-serif", weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000) + ' Juta';
                                }
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endif
@endpush
