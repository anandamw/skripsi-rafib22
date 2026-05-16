<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterBahan extends Model
{
    use HasFactory;

    protected $table = 'parameter_bahan';

    protected $fillable = [
        'bahan_baku_id',
        'tahun',
        'biaya_pesan',
        'biaya_simpan',
    ];

    protected $casts = [
        'tahun'        => 'integer',
        'biaya_pesan'  => 'integer',
        'biaya_simpan' => 'integer',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
