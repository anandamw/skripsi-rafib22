<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TransaksiStok extends Model
{
    use HasFactory;

    protected $fillable = [
        'bahan_baku_id',
        'tipe',
        'jumlah',
        'keterangan',
        'user_id',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update actual stock when a transaction is created.
     */
    protected static function booted()
    {
        static::created(function ($transaksi) {
            DB::transaction(function () use ($transaksi) {
                $stok = Stok::firstOrCreate(
                    ['bahan_baku_id' => $transaksi->bahan_baku_id],
                    ['stok_aktual' => 0]
                );

                if ($transaksi->tipe === 'masuk') {
                    $stok->stok_aktual += $transaksi->jumlah;
                } elseif ($transaksi->tipe === 'keluar') {
                    $stok->stok_aktual -= $transaksi->jumlah;
                }

                $stok->save();
            });
        });
        
        // Handle deletes to revert stock
        static::deleted(function ($transaksi) {
             DB::transaction(function () use ($transaksi) {
                $stok = Stok::where('bahan_baku_id', $transaksi->bahan_baku_id)->first();
                if ($stok) {
                    if ($transaksi->tipe === 'masuk') {
                        $stok->stok_aktual -= $transaksi->jumlah;
                    } elseif ($transaksi->tipe === 'keluar') {
                        $stok->stok_aktual += $transaksi->jumlah;
                    }
                    $stok->save();
                }
            });
        });
    }
}
