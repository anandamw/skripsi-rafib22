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
        Schema::table('kalkulasi_eoqs', function (Blueprint $table) {
            $table->string('tipe_fluktuasi')->default('Stasioner')->after('tahun');
            $table->boolean('is_volatile')->default(false)->after('tipe_fluktuasi');
            $table->double('z_score')->default(1.65)->after('is_volatile');
            $table->double('lead_time_aktual')->nullable()->after('z_score');
            $table->text('nilai_penyesuaian')->nullable()->after('lead_time_aktual'); // to store JSON data like SI or Slope
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kalkulasi_eoqs', function (Blueprint $table) {
            $table->dropColumn(['tipe_fluktuasi', 'is_volatile', 'z_score', 'lead_time_aktual', 'nilai_penyesuaian']);
        });
    }
};
