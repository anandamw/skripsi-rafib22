<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    protected $fillable = [
        'bahan_baku_id',
        'stok_aktual',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
