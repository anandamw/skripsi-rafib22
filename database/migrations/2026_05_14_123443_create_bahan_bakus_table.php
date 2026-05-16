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
        Schema::create('bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // BB001, etc.
            $table->string('nama');
            $table->string('satuan'); // Kg, Liter, etc.
            $table->enum('kategori', ['Lokal', 'Impor'])->default('Lokal');
            
            // EOQ/ROP Parameters
            $table->double('s_biaya_pesan')->default(0); // S
            $table->double('h_biaya_simpan')->default(0); // H
            $table->integer('lead_time')->default(0); // LT (hari)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_bakus');
    }
};
