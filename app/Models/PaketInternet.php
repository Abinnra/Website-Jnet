<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Paket internet (tabel paket_internet).
 *
 * Paket tidak dihapus; paket yang tidak dijual lagi dinonaktifkan
 * melalui kolom status_aktif.
 */
class PaketInternet extends Model
{
    protected $table = 'paket_internet';

    protected $primaryKey = 'id_paket';

    protected $fillable = [
        'nama_paket',
        'label',
        'kecepatan_mbps',
        'harga',
        'biaya_pemasangan',
        'keunggulan',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'kecepatan_mbps' => 'integer',
            'harga' => 'decimal:2',
            'biaya_pemasangan' => 'decimal:2',
            'status_aktif' => 'boolean',
        ];
    }

    /**
     * Keunggulan per baris untuk kartu paket (Revisi 4).
     * "1–8 perangkat | Perangkat optimal" -> ['nilai' => '1–8 perangkat', 'keterangan' => 'Perangkat optimal'].
     */
    protected function daftarKeunggulan(): Attribute
    {
        return Attribute::get(function () {
            $baris = preg_split('/\R/', (string) $this->keunggulan, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            return collect($baris)
                ->map(fn (string $teks) => array_map('trim', explode('|', $teks, 2)))
                ->filter(fn (array $bagian) => $bagian[0] !== '')
                ->map(fn (array $bagian) => ['nilai' => $bagian[0], 'keterangan' => $bagian[1] ?? null])
                ->take(3)
                ->values()
                ->all();
        });
    }

    /**
     * Hanya paket yang aktif. Contoh: PaketInternet::aktif()->get().
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status_aktif', true);
    }

    /**
     * Pengajuan yang memilih paket ini (relasi "dipilih" di ERD).
     */
    public function registrasi(): HasMany
    {
        return $this->hasMany(RegistrasiPelanggan::class, 'id_paket', 'id_paket');
    }
}
