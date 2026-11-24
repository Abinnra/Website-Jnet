<?php

namespace App\Models;

use App\Enums\KeputusanHasil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hasil kunjungan pemasangan (tabel hasil_layanan); hasil survei hanya ada pada data lama.
 *
 * Tabel ini hanya memiliki created_at, sehingga UPDATED_AT dinonaktifkan.
 * Kolom hasil berisi kode KeputusanHasil; uraian disimpan di catatan.
 */
class HasilLayanan extends Model
{
    const UPDATED_AT = null;

    protected $table = 'hasil_layanan';

    protected $primaryKey = 'id_hasil';

    protected $fillable = [
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'hasil' => KeputusanHasil::class,
        ];
    }

    /**
     * Jadwal yang menghasilkan catatan ini (relasi "menghasilkan").
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalLayanan::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Admin yang mencatat hasil (relasi "mencatat").
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
