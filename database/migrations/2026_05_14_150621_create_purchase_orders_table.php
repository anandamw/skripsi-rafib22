<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_po')->unique(); // PO-2024-0001
            $table->date('tanggal');
            $table->enum('status', ['draft', 'disetujui', 'dikirim', 'diterima', 'dibatalkan'])->default('draft');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Dibuat oleh
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
