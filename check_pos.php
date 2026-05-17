<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseOrder;

$pos = PurchaseOrder::all();
echo "Total POs: " . $pos->count() . "\n";
foreach ($pos as $po) {
    echo "ID: {$po->id} | No: {$po->no_po} | Status: {$po->status} | Tanggal: {$po->tanggal->format('Y-m-d')}\n";
}
