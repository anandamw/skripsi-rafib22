@extends('layouts.app')
@section('content')

 <div class="row mb-3">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-0">Selamat datang, <span class="text-primary">Admin</span></h4>
                    <p class="text-muted">Sistem Informasi Pengelolaan Persediaan Bahan Baku</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn btn-white shadow-sm border">
                        <i class="fas fa-calendar me-2 text-muted"></i> 01 Mei 2024 - 31 Mei 2024
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-2.4 col-lg">
                    <div class="card card-stat shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary me-3"><i class="fas fa-cube"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Bahan Baku</small>
                                <h4 class="mb-0 fw-bold">128</h4>
                                <small class="text-muted">Jenis</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2.4 col-lg">
                    <div class="card card-stat shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success bg-opacity-10 text-success me-3"><i
                                    class="fas fa-database"></i></div>
                            <div>
                                <small class="text-muted d-block">Total Persediaan</small>
                                <h4 class="mb-0 fw-bold">15.642,75</h4>
                                <small class="text-muted">Kg</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2.4 col-lg">
                    <div class="card card-stat shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-warning bg-opacity-10 text-warning me-3"><i
                                    class="fas fa-shopping-bag"></i></div>
                            <div>
                                <small class="text-muted d-block">Pembelian</small>
                                <h4 class="mb-0 fw-bold">Rp 245.7M</h4>
                                <small class="text-success" style="font-size: 0.7rem;"><i class="fas fa-arrow-up"></i>
                                    12.45%</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2.4 col-lg">
                    <div class="card card-stat shadow-sm p-3 border-danger bg-danger bg-opacity-10">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-danger text-white me-3"><i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <small class="text-danger d-block fw-bold">Peringatan Stok</small>
                                <h4 class="mb-0 fw-bold">18</h4>
                                <small class="text-muted">Item</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <h6 class="fw-bold mb-4">Ringkasan Persediaan</h6>
                        <canvas id="ringkasanChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex justify-content-between mb-4">
                            <h6 class="fw-bold">Analisis EOQ & ROP</h6>
                            <button class="btn btn-sm btn-outline-primary">Lihat Detail</button>
                        </div>
                        <canvas id="analisisChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="card border-0 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Daftar Bahan Baku</h6>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" placeholder="Cari bahan baku...">
                        <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Bahan Baku</th>
                                <th>Kategori</th>
                                <th>Stok Aktual</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>BB-0001</td>
                                <td>Niacinamide</td>
                                <td>Bahan Aktif</td>
                                <td class="text-danger fw-bold">18,50 Kg</td>
                                <td><span class="badge bg-danger">Kritis</span></td>
                                <td>
                                    <button class="btn btn-sm btn-light"><i
                                            class="far fa-eye text-primary"></i></button>
                                    <button class="btn btn-sm btn-light"><i
                                            class="far fa-edit text-warning"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>BB-0002</td>
                                <td>Glycerin</td>
                                <td>Bahan Tambahan</td>
                                <td class="text-success fw-bold">256,30 Kg</td>
                                <td><span class="badge bg-success">Aman</span></td>
                                <td>
                                    <button class="btn btn-sm btn-light"><i
                                            class="far fa-eye text-primary"></i></button>
                                    <button class="btn btn-sm btn-light"><i
                                            class="far fa-edit text-warning"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

@endsection