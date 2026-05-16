<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Role constants
    const ROLE_PURCHASING = 'purchasing';
    const ROLE_PRODUKSI = 'produksi';
    const ROLE_GUDANG = 'gudang';
    const ROLE_MANAJER = 'manajer';

    const ROLE_LIST = [
        self::ROLE_PURCHASING => 'Staff Purchasing',
        self::ROLE_PRODUKSI => 'Staff Produksi',
        self::ROLE_GUDANG => 'Admin Gudang',
        self::ROLE_MANAJER => 'Manajer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'aktif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    // ── Role Helpers ──

    public function isPurchasing(): bool
    {
        return $this->role === self::ROLE_PURCHASING;
    }

    public function isProduksi(): bool
    {
        return $this->role === self::ROLE_PRODUKSI;
    }

    public function isGudang(): bool
    {
        return $this->role === self::ROLE_GUDANG;
    }

    public function isManajer(): bool
    {
        return $this->role === self::ROLE_MANAJER;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Get human-readable role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LIST[$this->role] ?? ucfirst($this->role);
    }
}
