<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokHistoris extends Model
{
    use HasFactory;

    protected $table = 'stok_historis';

    protected $fillable = [
        'bahan_baku_id',
        'tahun',
        'stok_aktual',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'stok_aktual'  => 'integer',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
