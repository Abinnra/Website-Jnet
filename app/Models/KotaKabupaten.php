<?php

namespace App\Models;

use App\Services\WilayahService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kota/kabupaten wilayah utama layanan (tabel kota_kabupaten), dikelola admin.
 *
 * Pengajuan dari kota/kabupaten yang terdaftar di sini tidak dianggap luar wilayah.
 */
class KotaKabupaten extends Model
{
    protected $table = 'kota_kabupaten';

    protected $primaryKey = 'id_kota_kabupaten';

    protected $fillable = ['nama'];

    protected static function booted(): void
    {
        // Daftar wilayah yang sudah dibaca pada request ini dibaca ulang setelah berubah.
        static::saved(fn () => app(WilayahService::class)->lupakan());
        static::deleted(fn () => app(WilayahService::class)->lupakan());
    }

    public function kecamatan(): HasMany
    {
        return $this->hasMany(Kecamatan::class, 'id_kota_kabupaten', 'id_kota_kabupaten');
    }

    /**
     * Jumlah pengajuan yang tercatat di kota/kabupaten ini.
     */
    public function jumlahPengajuan(): int
    {
        return RegistrasiPelanggan::query()->where('kota_kabupaten', $this->nama)->count();
    }
}
