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
        Schema::create('kalkulasi_eoqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            $table->integer('tahun');
            
            // Parameter Kalkulasi
            $table->double('d_tahunan'); // D
            $table->double('d_harian_avg'); // d bar
            $table->double('sigma_d'); // std dev of d_harian
            
            // Hasil Kalkulasi EOQ & ROP
            $table->double('eoq'); // Q
            $table->double('sigma_dl'); // std dev during lead time
            $table->double('safety_stock'); // SS
            $table->double('rop'); // Reorder Point
            
            // Analisis Tambahan (opsional untuk saat ini)
            $table->double('cv')->nullable(); // Koefisien Variasi
            $table->double('slope')->nullable(); // Tren
            $table->double('r_squared')->nullable();
            
            $table->timestamps();

            $table->unique(['bahan_baku_id', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kalkulasi_eoqs');
    }
};
