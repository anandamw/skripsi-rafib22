<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_po',
        'tanggal',
        'status',
        'catatan',
        'user_id',
        'tanggal_diterima',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_diterima' => 'date',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DIKIRIM = 'dikirim';
    const STATUS_DITERIMA = 'diterima';
    const STATUS_DIBATALKAN = 'dibatalkan';

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate the next PO number.
     */
    public static function generateNoPo(): string
    {
        $year = date('Y');
        $lastPo = self::whereYear('tanggal', $year)->orderBy('id', 'desc')->first();

        if ($lastPo) {
            $lastNumber = intval(substr($lastPo->no_po, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'PO-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge bg-secondary rounded-pill px-3">Draft</span>',
            'disetujui' => '<span class="badge bg-primary rounded-pill px-3">Disetujui</span>',
            'dikirim' => '<span class="badge bg-info rounded-pill px-3">Dikirim</span>',
            'diterima' => '<span class="badge bg-success rounded-pill px-3">Diterima</span>',
            'dibatalkan' => '<span class="badge bg-danger rounded-pill px-3">Dibatalkan</span>',
            default => '<span class="badge bg-light text-dark rounded-pill px-3">-</span>',
        };
    }
}
