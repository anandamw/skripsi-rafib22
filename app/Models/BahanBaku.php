<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'satuan',
        'lead_time',
    ];

    /**
     * Get formatted S parameter.
     */
    public function getSFormattedAttribute()
    {
        return 'Rp ' . number_format($this->s_biaya_pesan, 0, ',', '.');
    }

    /**
     * Get formatted H parameter.
     */
    public function getHFormattedAttribute()
    {
        return 'Rp ' . number_format($this->h_biaya_simpan, 0, ',', '.');
    }

    /**
     * Relasi ke tabel stoks.
     */
    public function stok()
    {
        return $this->hasOne(Stok::class);
    }

    public function parameterBahan()
    {
        return $this->hasMany(ParameterBahan::class, 'bahan_baku_id');
    }

    public function stokHistoris()
    {
        return $this->hasMany(StokHistoris::class, 'bahan_baku_id');
    }

    // Parameter untuk tahun tertentu
    public function parameterTahun(int $tahun)
    {
        return $this->parameterBahan()->where('tahun', $tahun)->first();
    }

    // Stok historis tahun tertentu
    public function stokTahun(int $tahun)
    {
        return $this->stokHistoris()->where('tahun', $tahun)->first();
    }
}

