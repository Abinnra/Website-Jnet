<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Akun admin (tabel admins), terpisah dari akun pelanggan.
 *
 * Penamaan mengikuti ERD Revisi 1. Tabel admins hanya memiliki
 * created_at, sehingga UPDATED_AT dinonaktifkan.
 */
class Admin extends Authenticatable
{
    const UPDATED_AT = null;

    protected $table = 'admins';

    protected $primaryKey = 'id_admin';

    protected $authPasswordName = 'password_hash';

    protected $rememberTokenName = '';

    protected $fillable = [
        'nama',
        'username',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Pengajuan yang terakhir diperbarui admin ini (relasi "memperbarui").
     */
    public function registrasiDiperbarui(): HasMany
    {
        return $this->hasMany(RegistrasiPelanggan::class, 'updated_by_admin_id', 'id_admin');
    }

    /**
     * Jadwal yang diatur admin ini (relasi "mengatur").
     */
    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalLayanan::class, 'id_admin', 'id_admin');
    }

    /**
     * Hasil layanan yang dicatat admin ini (relasi "mencatat").
     */
    public function hasil(): HasMany
    {
        return $this->hasMany(HasilLayanan::class, 'id_admin', 'id_admin');
    }

    /**
     * Informasi website yang diperbarui admin ini (relasi "memperbarui").
     */
    public function siteSettings(): HasMany
    {
        return $this->hasMany(SiteSetting::class, 'id_admin', 'id_admin');
    }
}
