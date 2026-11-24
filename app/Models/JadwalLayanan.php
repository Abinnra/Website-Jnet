<?php

namespace App\Models;

use App\Enums\JenisJadwal;
use App\Enums\StatusJadwal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Jadwal kunjungan pemasangan (tabel jadwal_layanan); jadwal survei hanya ada pada data lama.
 *
 * jenis_jadwal, status_jadwal, id_registrasi, dan id_admin diisi oleh
 * service, bukan langsung dari input form.
 */
class JadwalLayanan extends Model
{
    protected $table = 'jadwal_layanan';

    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'tanggal',
        'waktu_mulai',
        'catatan',
    ];

    protected $attributes = [
        'status_jadwal' => 'TERJADWAL',
    ];

    protected function casts(): array
    {
        return [
            'jenis_jadwal' => JenisJadwal::class,
            'status_jadwal' => StatusJadwal::class,
            'tanggal' => 'date',
        ];
    }

    /**
     * Pengajuan pemilik jadwal ini (relasi "memiliki").
     */
    public function registrasi(): BelongsTo
    {
        return $this->belongsTo(RegistrasiPelanggan::class, 'id_registrasi', 'id_registrasi');
    }

    /**
     * Admin yang mengatur jadwal (relasi "mengatur").
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    /**
     * Hasil layanan; satu jadwal memiliki paling banyak satu hasil
     * (relasi "menghasilkan", 1 : 0..1).
     */
    public function hasil(): HasOne
    {
        return $this->hasOne(HasilLayanan::class, 'id_jadwal', 'id_jadwal');
    }
}
