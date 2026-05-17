@extends('layouts.app')

@section('title', 'Kalkulasi EOQ & ROP')
@section('page_title', 'Analisis EOQ & ROP')

@section('content')
<div class="card border-0 shadow-sm p-4 mb-4 bg-primary text-white" style="background: linear-gradient(135deg, var(--sidebar-bg) 0%, #2a4ec1 100%);">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="fw-bold mb-2"><i class="fas fa-calculator me-2"></i> Engine Kalkulasi EOQ (Web vs Excel)</h5>
            <p class="mb-0 text-white-50 small">Sistem menampilkan perbandingan langsung antara perhitungan <strong>Metode Adaptif (Sistem Web Skripsi)</strong> dan <strong>Metode Konvensional (Excel)</strong> untuk membuktikan keunggulan analisis dinamis pada persediaan bahan baku.</p>
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
        <h6 class="fw-bold mb-0">Hasil Analisis & Perbandingan Tahun {{ $selectedTahun }}</h6>
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
                        <th rowspan="2" class="align-middle text-start" style="min-width: 220px;">Bahan Baku & Keterangan</th>
                        <th rowspan="2" class="align-middle bg-info bg-opacity-10" style="min-width: 150px;">Arah Fluktuasi</th>
                        <th colspan="3" class="bg-primary bg-opacity-10">Data Demand</th>
                        <th colspan="4" class="bg-success bg-opacity-10">Parameter Optimal</th>
                        <th rowspan="2" class="align-middle bg-light" style="min-width: 140px;">Status Inventori</th>
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
                        
                        // --- PERHITUNGAN WEB (ADAPTIF) ---
                        $rop_web = $item->rop;
                        $ss_web = $item->safety_stock;
                        $eoq_web = $item->eoq;
                        $d_tahunan_web = $item->d_tahunan;
                        $d_harian_web = $item->d_harian_avg;
                        $sigma_d_web = $item->sigma_d;
                        $sigma_dl_web = $item->sigma_dl;

                        $status_badge_web = '';
                        $status_text_web = '';
                        if ($stok == 0 || $stok <= $ss_web) {
                            $status_badge_web = 'bg-danger';
                            $status_text_web = 'Stockout ⚠';
                        } elseif ($stok <= $rop_web) {
                            $status_badge_web = 'bg-warning text-dark';
                            $status_text_web = 'Reorder 🔄';
                        } elseif ($stok > ($rop_web + $eoq_web)) {
                            $status_badge_web = 'bg-info text-dark';
                            $status_text_web = 'Overstock 📦';
                        } else {
                            $status_badge_web = 'bg-success';
                            $status_text_web = 'Aman ✅';
                        }

                        // --- PERHITUNGAN EXCEL (KONVENSIONAL) ---
                        $pemakaian = \App\Models\PemakaianBulanan::where('bahan_baku_id', $item->bahan_baku_id)
                            ->where('tahun', $selectedTahun)
                            ->get();
                            
                        // Sesuai rumus Excel: D di Excel menggunakan SUM(DE:EA) yang mencakup pemakaian bulanan + d_harian
                        $d_tahunan_excel = round($pemakaian->sum('pemakaian') + $pemakaian->sum('d_harian'));
                        // Sesuai rumus Excel: d di Excel menggunakan rata-rata d_harian dari 12 bulan
                        $d_harian_excel = $pemakaian->avg('d_harian');

                        $raw_d_harian = $pemakaian->pluck('d_harian')->toArray();
                        $n = count($raw_d_harian);
                        $sigma_d_excel = 0;
                        if ($n > 1) {
                            $mean = array_sum($raw_d_harian) / $n;
                            $carry = 0.0;
                            foreach ($raw_d_harian as $val) {
                                $d = ((double) $val) - $mean;
                                $carry += $d * $d;
                            }
                            $sigma_d_excel = sqrt($carry / ($n - 1));
                        }

                        $param = $item->bahanBaku->parameterTahun($selectedTahun);
                        $s_val_num = $param ? $param->biaya_pesan : 0;
                        $h_val_num = $param ? $param->biaya_simpan : 0;

                        $eoq_excel = $h_val_num > 0 ? sqrt((2 * $d_tahunan_excel * $s_val_num) / $h_val_num) : 0;
                        $lt_kontrak = $item->bahanBaku->lead_time;
                        $sigma_dl_excel = $sigma_d_excel * sqrt($lt_kontrak);
                        $ss_excel = 1.65 * $sigma_dl_excel;
                        $rop_excel = ($d_harian_excel * $lt_kontrak) + $ss_excel;

                        $status_badge_excel = '';
                        $status_text_excel = '';
                        if ($stok == 0 || $stok <= $ss_excel) {
                            $status_badge_excel = 'bg-danger';
                            $status_text_excel = 'Stockout ⚠';
                        } elseif ($stok <= $rop_excel) {
                            $status_badge_excel = 'bg-warning text-dark';
                            $status_text_excel = 'Reorder 🔄';
                        } elseif ($stok > ($rop_excel + $eoq_excel)) {
                            $status_badge_excel = 'bg-info text-dark';
                            $status_text_excel = 'Overstock 📦';
                        } else {
                            $status_badge_excel = 'bg-success';
                            $status_text_excel = 'Aman ✅';
                        }

                        $s_val = $param ? 'Rp ' . number_format($param->biaya_pesan, 0, ',', '.') : '-';
                        $h_val = $param ? 'Rp ' . number_format($param->biaya_simpan, 0, ',', '.') : '-';
                        
                        // --- ANALISIS ARAH FLUKTUASI ---
                        $arah_fluktuasi_html = '';
                        if ($item->tipe_fluktuasi == 'Trend') {
                            $penyesuaian = json_decode($item->nilai_penyesuaian, true);
                            $slope = $penyesuaian['slope'] ?? 0;
                            if ($slope > 0) {
                                $arah_fluktuasi_html = '<div class="text-success fw-bold"><i class="fas fa-arrow-trend-up me-1"></i> Trend Naik</div>
                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">Slope: +' . number_format($slope, 1, ',', '.') . ' /thn</div>';
                            } else {
                                $arah_fluktuasi_html = '<div class="text-danger fw-bold"><i class="fas fa-arrow-trend-down me-1"></i> Trend Turun</div>
                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">Slope: ' . number_format($slope, 1, ',', '.') . ' /thn</div>';
                            }
                        } elseif ($item->tipe_fluktuasi == 'Musiman') {
                            $penyesuaian = json_decode($item->nilai_penyesuaian, true);
                            $si_list = $penyesuaian['seasonal_index'] ?? [];
                            if (!empty($si_list)) {
                                $max_m = array_keys($si_list, max($si_list))[0];
                                $min_m = array_keys($si_list, min($si_list))[0];
                                $bulan_names = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                                $max_name = $bulan_names[$max_m] ?? $max_m;
                                $min_name = $bulan_names[$min_m] ?? $min_m;
                                $arah_fluktuasi_html = '<div class="text-warning text-dark fw-bold"><i class="fas fa-water me-1"></i> Gelombang Musiman</div>
                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                    <span class="text-success fw-bold">Puncak:</span> ' . $max_name . ' (' . number_format($si_list[$max_m], 2, ',', '.') . ')<br>
                                    <span class="text-danger fw-bold">Sepi:</span> ' . $min_name . ' (' . number_format($si_list[$min_m], 2, ',', '.') . ')
                                </div>';
                            } else {
                                $arah_fluktuasi_html = '<div class="text-warning text-dark fw-bold"><i class="fas fa-water me-1"></i> Musiman</div>';
                            }
                        } else {
                            $arah_fluktuasi_html = '<div class="text-secondary fw-bold"><i class="fas fa-equals me-1"></i> Stasioner</div>
                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">Fluktuasi Normal (&lt;5%)</div>';
                        }
                    @endphp
                    <tr>
                        <td class="text-start fw-bold text-primary align-middle">
                            {{ $item->bahanBaku->kode }} - {{ $item->bahanBaku->nama }}
                            <div class="mt-1 d-flex align-items-center gap-1">
                                @php
                                    $fluktuasiColor = 'bg-secondary';
                                    if ($item->tipe_fluktuasi == 'Trend') $fluktuasiColor = 'bg-info text-dark';
                                    elseif ($item->tipe_fluktuasi == 'Musiman') $fluktuasiColor = 'bg-warning text-dark';
                                    elseif ($item->tipe_fluktuasi == 'Stasioner') $fluktuasiColor = 'bg-success';
                                @endphp
                                <span class="badge {{ $fluktuasiColor }} rounded-pill" style="font-size: 0.7rem;">{{ $item->tipe_fluktuasi }}</span>
                                @if($item->is_volatile)
                                    <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;"><i class="fas fa-exclamation-triangle"></i> Volatile</span>
                                @endif
                                @if($item->lead_time_aktual > $item->bahanBaku->lead_time)
                                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.7rem;" title="LT Kontrak: {{ $item->bahanBaku->lead_time }}hr"><i class="fas fa-truck"></i> LT Terlambat</span>
                                @endif
                            </div>
                            <div class="text-muted small fw-normal mt-1">S: {{ $s_val }} | H: {{ $h_val }} | LT Aktual: {{ $item->lead_time_aktual ?? $item->bahanBaku->lead_time }}hr</div>
                            <hr class="my-2 border-secondary-subtle">
                            <div class="d-flex flex-column gap-1 small fw-normal">
                                <span class="text-primary fw-bold"><i class="fas fa-globe me-1"></i> Web (Adaptif)</span>
                                <span class="text-secondary"><i class="fas fa-file-excel me-1"></i> Excel (Konvensional)</span>
                            </div>
                        </td>
                        <td class="align-middle bg-info bg-opacity-10 text-center">
                            {!! $arah_fluktuasi_html !!}
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column gap-1 justify-content-center">
                                <div class="fw-bold text-dark">{{ number_format($d_tahunan_web, 0, ',', '.') }}</div>
                                <hr class="my-1 border-secondary-subtle">
                                <div class="text-secondary small">{{ number_format($d_tahunan_excel, 0, ',', '.') }}</div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column gap-1 justify-content-center">
                                <div class="fw-bold text-dark">{{ number_format($d_harian_web, 2, ',', '.') }}</div>
                                <hr class="my-1 border-secondary-subtle">
                                <div class="text-secondary small">{{ number_format($d_harian_excel, 2, ',', '.') }}</div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column gap-1 justify-content-center">
                                <div class="fw-bold text-dark">{{ number_format($sigma_d_web, 2, ',', '.') }}</div>
                                <hr class="my-1 border-secondary-subtle">
                                <div class="text-secondary small">{{ number_format($sigma_d_excel, 2, ',', '.') }}</div>
                            </div>
                        </td>
                        <td class="align-middle bg-success bg-opacity-10">
                            <div class="d-flex flex-column gap-1 justify-content-center">
                                <div class="fw-bold text-success fs-6">{{ number_format($eoq_web, 0, ',', '.') }}</div>
                                <hr class="my-1 border-success-subtle">
                                <div class="text-secondary small">{{ number_format($eoq_excel, 0, ',', '.') }}</div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column gap-1 justify-content-center">
                                <div class="fw-bold text-dark">{{ number_format($sigma_dl_web, 2, ',', '.') }}</div>
                                <hr class="my-1 border-secondary-subtle">
                                <div class="text-secondary small">{{ number_format($sigma_dl_excel, 2, ',', '.') }}</div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column gap-1 justify-content-center">
                                <div class="fw-bold text-dark">{{ number_format($ss_web, 0, ',', '.') }}</div>
                                <hr class="my-1 border-secondary-subtle">
                                <div class="text-secondary small">{{ number_format($ss_excel, 0, ',', '.') }}</div>
                            </div>
                        </td>
                        <td class="align-middle bg-warning bg-opacity-10">
                            <div class="d-flex flex-column gap-1 justify-content-center">
                                <div class="fw-bold text-danger fs-6">{{ number_format($rop_web, 0, ',', '.') }}</div>
                                <hr class="my-1 border-warning-subtle">
                                <div class="text-secondary small">{{ number_format($rop_excel, 0, ',', '.') }}</div>
                            </div>
                        </td>
                        <td class="align-middle bg-light">
                            <div class="d-flex flex-column gap-1 align-items-center justify-content-center">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge {{ $status_badge_web }} rounded-pill px-2 py-1" style="font-size: 0.7rem;" title="Status Web">Web: {{ $status_text_web }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge {{ $status_badge_excel }} rounded-pill px-2 py-1" style="font-size: 0.7rem;" title="Status Excel">Excel: {{ $status_text_excel }}</span>
                                </div>
                                <hr class="my-1 border-secondary-subtle w-100">
                                <div class="small text-muted fw-bold" style="font-size: 0.75rem;">Stok: {{ number_format($stok, 0, ',', '.') }}</div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        
        <div class="alert alert-info border-0 rounded-3 mt-4 small shadow-sm">
            <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-2"></i> Keterangan Perbandingan & Status (Web vs Excel):</h6>
            <ul class="mb-0 mt-1" style="line-height: 1.6;">
                <li><strong>Web (Metode Adaptif Skripsi):</strong> Menggunakan peramalan WMA 5 Periode untuk data Trend, penyesuaian Seasonal Index untuk data Musiman, serta Adaptive Safety Stock (z=2.05) untuk bahan baku yang Volatile atau sering mengalami keterlambatan Lead Time.</li>
                <li><strong>Excel (Metode Konvensional):</strong> Menggunakan perhitungan stasioner tradisional berbasis pemakaian mentah tahun berjalan tanpa penyesuaian tren, dengan Safety Stock standar (z=1.65) dan Lead Time kontrak tetap.</li>
                <li><strong>Kategori Status Inventori (4 Tingkatan):</strong> 
                    <span class="badge bg-danger">Stockout ⚠</span> (Stok habis/di bawah Safety Stock), 
                    <span class="badge bg-warning text-dark">Reorder 🔄</span> (Stok di bawah ROP, saatnya memesan), 
                    <span class="badge bg-success">Aman ✅</span> (Stok ideal di rentang ROP hingga ROP + EOQ), dan 
                    <span class="badge bg-info text-dark">Overstock 📦</span> (Stok berlebih melampaui ROP + EOQ).
                </li>
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
            ordering: false // Disable sorting to keep the column grouping clean
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

