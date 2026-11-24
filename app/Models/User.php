<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Akun pelanggan (tabel users).
 *
 * Penamaan mengikuti ERD Revisi 1:
 * - primary key  : id_user
 * - kata sandi   : password_hash (disimpan dalam bentuk hash)
 * - tidak ada kolom remember_token (fitur "Ingat saya" tidak dipakai)
 *
 * Revisi 5: wajib_ganti_sandi bernilai true setelah admin mengatur ulang kata sandi.
 * Kolom ini sengaja tidak masuk $fillable; hanya diubah oleh KataSandiService.
 *
 * Revisi mitra (masuk dengan Google): google_id diisi LoginGoogleService (tidak masuk $fillable).
 * Akun yang dibuat lewat Google belum memiliki kata sandi (password_hash kosong) sampai
 * pelanggan membuatnya sendiri.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $primaryKey = 'id_user';

    /**
     * Nama kolom kata sandi yang dibaca oleh sistem autentikasi Laravel.
     */
    protected $authPasswordName = 'password_hash';

    /**
     * Dikosongkan karena tabel users tidak memiliki kolom remember_token.
     */
    protected $rememberTokenName = '';

    protected $fillable = [
        'email',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
        'google_id',
    ];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'wajib_ganti_sandi' => 'boolean',
        ];
    }

    /**
     * False untuk akun yang dibuat lewat Google dan belum membuat kata sandi.
     */
    public function punyaKataSandi(): bool
    {
        return filled($this->password_hash);
    }

    public function terhubungGoogle(): bool
    {
        return filled($this->google_id);
    }

    /**
     * Pengajuan berlangganan yang dibuat akun ini (relasi "membuat" di ERD).
     */
    public function registrasi(): HasMany
    {
        return $this->hasMany(RegistrasiPelanggan::class, 'id_user', 'id_user');
    }
}
