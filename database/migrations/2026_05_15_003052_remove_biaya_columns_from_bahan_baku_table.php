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
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropColumn(['s_biaya_pesan', 'h_biaya_simpan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->bigInteger('s_biaya_pesan')->nullable();
            $table->bigInteger('h_biaya_simpan')->nullable();
        });
    }
};
