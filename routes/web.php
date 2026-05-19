<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\PemakaianController;
use App\Http\Controllers\EoqController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\TicController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil Saya (Semua user yang login bisa akses)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile.index');
    Route::patch('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');
    
    // Master Data Bahan Baku (Semua bisa melihat, hanya manajer/purchasing yang bisa kelola)
    Route::get('/bahan-baku', [BahanBakuController::class, 'index'])->name('bahan-baku.index');
    Route::get('/bahan-baku/{bahan_baku}', [BahanBakuController::class, 'show'])->name('bahan-baku.show');
    
    Route::middleware('role:gudang')->group(function () {
        Route::get('/bahan-baku/create', [BahanBakuController::class, 'create'])->name('bahan-baku.create');
        Route::post('/bahan-baku', [BahanBakuController::class, 'store'])->name('bahan-baku.store');
        Route::get('/bahan-baku/{bahan_baku}/edit', [BahanBakuController::class, 'edit'])->name('bahan-baku.edit');
        Route::put('/bahan-baku/{bahan_baku}', [BahanBakuController::class, 'update'])->name('bahan-baku.update');
        Route::delete('/bahan-baku/{bahan_baku}', [BahanBakuController::class, 'destroy'])->name('bahan-baku.destroy');
    });


    // Pemakaian Produksi
    Route::middleware('role:produksi')->group(function () {
        Route::resource('pemakaian', PemakaianController::class)->except(['edit', 'update', 'show']);
    });

    // Kalkulasi EOQ & ROP
    Route::middleware('role:gudang')->group(function () {
        Route::get('/eoq', [EoqController::class, 'index'])->name('eoq.index');
        Route::post('/eoq/calculate', [EoqController::class, 'calculate'])->name('eoq.calculate');
    });

    // Manajemen Stok
    Route::middleware('role:manajer,gudang')->group(function () {
        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
        Route::get('/stok/riwayat', [StokController::class, 'riwayat'])->name('stok.riwayat');
        Route::post('/stok/transaksi', [StokController::class, 'storeTransaksi'])->name('stok.transaksi.store');
    });

    // Evaluasi TIC
    Route::middleware('role:manajer,gudang')->group(function () {
        Route::get('/tic', [TicController::class, 'index'])->name('tic.index');
    });

    // Purchase Order
    Route::middleware('role:purchasing,manajer,gudang')->group(function () {
        Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
        Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
        Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('/po/{po}', [PurchaseOrderController::class, 'show'])->name('po.show');
        Route::patch('/po/{po}/status', [PurchaseOrderController::class, 'updateStatus'])->name('po.updateStatus');
    });

    // Laporan & Ekspor
    Route::middleware('role:purchasing,manajer,gudang,produksi')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/persediaan', [LaporanController::class, 'persediaan'])->name('laporan.persediaan');
        Route::get('/laporan/eoq', [LaporanController::class, 'eoq'])->name('laporan.eoq');
        Route::get('/laporan/tic', [LaporanController::class, 'tic'])->name('laporan.tic');
        Route::get('/laporan/po', [LaporanController::class, 'po'])->name('laporan.po');
    });

    // Administrator (Manajemen User)
    Route::middleware('role:manajer')->group(function () {
        Route::resource('user', UserController::class)->except(['create', 'show', 'edit']);
    });
});








