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
        Schema::create('pemakaian_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            $table->integer('tahun');
            $table->integer('bulan'); // 1-12
            $table->integer('jumlah_hari'); // 28-31
            $table->double('pemakaian'); // Total unit pemakaian
            $table->double('d_harian'); // pemakaian / jumlah_hari
            $table->timestamps();

            // Ensure one entry per material per month
            $table->unique(['bahan_baku_id', 'tahun', 'bulan'], 'unique_pemakaian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemakaian_bulanans');
    }
};
