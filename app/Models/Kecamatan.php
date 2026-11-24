<?php

namespace App\Models;

use App\Services\WilayahService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kecamatan wilayah utama (tabel kecamatan), dipilih pelanggan dari daftar saat mengajukan.
 *
 * Pengajuan menyimpan nama kecamatan sebagai teks, sehingga menghapus kecamatan
 * tidak mengubah pengajuan lama.
 */
class Kecamatan extends Model
{
    protected $table = 'kecamatan';

    protected $primaryKey = 'id_kecamatan';

    protected $fillable = ['nama'];

    protected static function booted(): void
    {
        static::saved(fn () => app(WilayahService::class)->lupakan());
        static::deleted(fn () => app(WilayahService::class)->lupakan());
    }

    public function kotaKabupaten(): BelongsTo
    {
        return $this->belongsTo(KotaKabupaten::class, 'id_kota_kabupaten', 'id_kota_kabupaten');
    }
}
