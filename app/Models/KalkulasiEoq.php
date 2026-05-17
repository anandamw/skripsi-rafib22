<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KalkulasiEoq extends Model
{
    use HasFactory;

    protected $fillable = [
        'bahan_baku_id',
        'tahun',
        'd_tahunan',
        'd_harian_avg',
        'sigma_d',
        'eoq',
        'sigma_dl',
        'safety_stock',
        'rop',
        'cv',
        'slope',
        'r_squared',
        'tipe_fluktuasi',
        'is_volatile',
        'z_score',
        'lead_time_aktual',
        'nilai_penyesuaian',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
