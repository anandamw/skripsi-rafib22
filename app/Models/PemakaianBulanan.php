<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PemakaianBulanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'bahan_baku_id',
        'tahun',
        'bulan',
        'jumlah_hari',
        'pemakaian',
        'd_harian',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    /**
     * Get human-readable month name.
     */
    public function getBahanBakuNamaAttribute()
    {
        return $this->bahanBaku->nama;
    }

    public function getBulanNamaAttribute()
    {
        return Carbon::create()->month($this->bulan)->translatedFormat('F');
    }

    /**
     * Boot logic for automatic calculations.
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            // Get number of days in month if not provided
            if (!$model->jumlah_hari) {
                $model->jumlah_hari = Carbon::create($model->tahun, $model->bulan)->daysInMonth;
            }
            
            // Calculate daily usage (d)
            if ($model->jumlah_hari > 0) {
                $model->d_harian = $model->pemakaian / $model->jumlah_hari;
            }
        });
    }
}
