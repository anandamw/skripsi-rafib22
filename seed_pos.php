<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\BahanBaku;
use App\Models\User;

$purchasingUser = User::where('role', 'purchasing')->first();
$userId = $purchasingUser ? $purchasingUser->id : 1;

$bahan1 = BahanBaku::where('kode', 'BB001')->first();
$bahan2 = BahanBaku::where('kode', 'BB002')->first();
$bahan3 = BahanBaku::where('kode', 'BB003')->first();
$bahan4 = BahanBaku::where('kode', 'BB004')->first();

// Bersihkan PO lama menggunakan delete agar tidak melanggar foreign key
PurchaseOrderItem::query()->delete();
PurchaseOrder::query()->delete();

// 1. PO Draft (Sedang Dikirim / Berjalan)
$po1 = PurchaseOrder::create([
    'no_po' => 'PO-2026-0001',
    'tanggal' => now()->subDays(1),
    'status' => 'draft',
    'catatan' => 'Pengajuan rutin bahan baku awal bulan.',
    'user_id' => $userId,
]);
if ($bahan1) {
    PurchaseOrderItem::create(['purchase_order_id' => $po1->id, 'bahan_baku_id' => $bahan1->id, 'qty' => 500, 'keterangan' => 'Kualitas Grade A']);
}

// 2. PO Draft (Sedang Dikirim / Berjalan)
$po2 = PurchaseOrder::create([
    'no_po' => 'PO-2026-0002',
    'tanggal' => now()->subDays(2),
    'status' => 'draft',
    'catatan' => 'Pesanan bahan baku tambahan untuk produksi.',
    'user_id' => $userId,
]);
if ($bahan2) {
    PurchaseOrderItem::create(['purchase_order_id' => $po2->id, 'bahan_baku_id' => $bahan2->id, 'qty' => 300, 'keterangan' => 'Butuh cepat']);
}

// 3. PO Draft (Sedang Dikirim / Berjalan - SIAP DITERIMA OLEH ADMIN GUDANG)
$po3 = PurchaseOrder::create([
    'no_po' => 'PO-2026-0003',
    'tanggal' => now()->subDays(5),
    'status' => 'draft',
    'catatan' => 'Barang dalam perjalanan dari supplier PT. Kimia Farma.',
    'user_id' => $userId,
]);
if ($bahan3) {
    PurchaseOrderItem::create(['purchase_order_id' => $po3->id, 'bahan_baku_id' => $bahan3->id, 'qty' => 1000, 'keterangan' => 'Pengiriman via darat']);
}

// 4. PO Diterima (Selesai)
$po4 = PurchaseOrder::create([
    'no_po' => 'PO-2026-0004',
    'tanggal' => now()->subDays(10),
    'status' => 'diterima',
    'catatan' => 'Barang telah diterima di gudang dengan baik.',
    'user_id' => $userId,
    'tanggal_diterima' => now()->subDays(4),
]);
if ($bahan4) {
    PurchaseOrderItem::create(['purchase_order_id' => $po4->id, 'bahan_baku_id' => $bahan4->id, 'qty' => 750, 'keterangan' => 'Sesuai spesifikasi']);
}

echo "Berhasil membuat 4 sampel PO dengan status 1-Click (hanya Draft dan Diterima)!\n";
