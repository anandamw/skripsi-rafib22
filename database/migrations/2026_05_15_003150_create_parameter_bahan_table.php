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
        Schema::create('parameter_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            $table->year('tahun');
            $table->bigInteger('biaya_pesan');   // S — Rp/order
            $table->bigInteger('biaya_simpan');  // H — Rp/unit/tahun
            $table->timestamps();

            $table->unique(['bahan_baku_id', 'tahun'], 'uq_parameter_bahan_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_bahan');
    }
};
