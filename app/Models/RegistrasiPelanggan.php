<?php

namespace App\Models;

use App\Enums\StatusPengajuan;
use App\Support\Wilayah;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Pengajuan berlangganan (tabel registrasi_pelanggan).
 *
 * Kolom id_user, id_paket, harga_saat_pengajuan, status_pengajuan,
 * pesan_untuk_pelanggan, dan updated_by_admin_id sengaja TIDAK dimasukkan ke $fillable. Nilainya
 * selalu diisi oleh service, tidak pernah langsung dari input form. nomor_pengajuan diisi model
 * saat pengajuan dibuat.
 */
class RegistrasiPelanggan extends Model
{
    /**
     * Awalan setiap nomor pengajuan, misalnya JNET-261119-01.
     */
    public const AWALAN_NOMOR = 'JNET-';

    protected $table = 'registrasi_pelanggan';

    protected $primaryKey = 'id_registrasi';

    protected $fillable = [
        'nama_pelanggan',
        'no_whatsapp',
        'alamat_pemasangan',
        'latitude',
        'longitude',
        'kecamatan',
        'kota_kabupaten',
        'catatan',
    ];

    protected $attributes = [
        'status_pengajuan' => 'BARU',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'harga_saat_pengajuan' => 'decimal:2',
            'status_pengajuan' => StatusPengajuan::class,
        ];
    }

    protected static function booted(): void
    {
        // Kecamatan di Kota/Kabupaten Probolinggo menentukan kota_kabupaten-nya sendiri.
        // Untuk luar Probolinggo nilainya wajib diisi (kolom NOT NULL).
        static::saving(function (self $registrasi) {
            if (blank($registrasi->kota_kabupaten)) {
                $registrasi->kota_kabupaten = Wilayah::kotaKabupatenUntuk($registrasi->kecamatan);
            }
        });

        // Nomor pengajuan dari tanggal pesan, misalnya JNET-261119-01 (lihat nomorBerikutnya()).
        static::creating(function (self $registrasi) {
            $registrasi->nomor_pengajuan = static::nomorBerikutnya($registrasi->created_at ?? now());
        });
    }

    /**
     * Lokasi di luar Kota/Kabupaten Probolinggo perlu dikonfirmasi jangkauannya oleh admin.
     */
    protected function luarWilayah(): Attribute
    {
        return Attribute::get(fn () => ! Wilayah::diWilayahUtama($this->kota_kabupaten));
    }

    /**
     * Jarak garis lurus dari titik acuan (km), atau null jika belum dapat dihitung.
     */
    protected function jarakKm(): Attribute
    {
        return Attribute::get(fn () => Wilayah::jarakKm($this->latitude, $this->longitude));
    }

    /**
     * Contoh: "Mayangan, Kota Probolinggo".
     */
    protected function lokasi(): Attribute
    {
        return Attribute::get(fn () => trim($this->kecamatan.', '.$this->kota_kabupaten, ', '));
    }

    /**
     * Nomor yang ditampilkan ke pelanggan: awalan dan tanggal pesannya saja, misalnya JNET-261119. Urutan
     * harian (-01) hanya dipakai admin untuk membedakan pengajuan pada tanggal yang sama.
     */
    protected function nomorPengajuanPelanggan(): Attribute
    {
        return Attribute::get(fn () => $this->nomor_pengajuan === null ? null : Str::beforeLast($this->nomor_pengajuan, '-'));
    }

    /**
     * Nomor pengajuan berikutnya untuk tanggal pesan $waktu (revisi mitra): "JNET-YYMMDD-NN", misalnya
     * JNET-261119-01 untuk pengajuan pertama pada 19 November 2026. NN adalah urutan pada hari itu, minimal dua digit.
     *
     * Dipanggil saat pengajuan dibuat di dalam transaksi penyimpanan. Nomor hari itu dibaca dengan kunci
     * agar dua pengajuan bersamaan tidak mendapat urutan yang sama; kolom yang unik menjadi pengaman terakhir.
     */
    public static function nomorBerikutnya(CarbonInterface $waktu): string
    {
        $awalanHariIni = self::AWALAN_NOMOR.$waktu->copy()->setTimezone(config('app.timezone'))->format('ymd').'-';

        $urutanTerakhir = static::query()
            ->where('nomor_pengajuan', 'like', $awalanHariIni.'%')
            ->lockForUpdate()
            ->pluck('nomor_pengajuan')
            ->map(fn (string $nomor) => (int) Str::after($nomor, $awalanHariIni))
            ->max() ?? 0;

        return sprintf('%s%02d', $awalanHariIni, $urutanTerakhir + 1);
    }

    /**
     * Akun pemilik pengajuan (relasi "membuat").
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Paket yang dipilih (relasi "dipilih").
     */
    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketInternet::class, 'id_paket', 'id_paket');
    }

    /**
     * Admin yang terakhir memperbarui pengajuan (relasi "memperbarui").
     */
    public function diperbaruiOleh(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by_admin_id', 'id_admin');
    }

    /**
     * Jadwal kunjungan pemasangan (relasi "memiliki").
     */
    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalLayanan::class, 'id_registrasi', 'id_registrasi');
    }
}
