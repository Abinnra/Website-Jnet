<?php

namespace App\Support;

use App\Services\WilayahService;

/**
 * Wilayah layanan: kota/kabupaten wilayah utama (dikelola admin, bawaannya Kota dan
 * Kabupaten Probolinggo), dan jarak garis lurus dari titik acuan untuk lokasi di luar wilayah itu.
 */
class Wilayah
{
    private const RADIUS_BUMI_KM = 6371.0;

    /**
     * Kota/kabupaten wilayah utama beserta kecamatannya, untuk daftar pilihan dan halaman Jangkauan.
     *
     * @return array<string, list<string>>
     */
    public static function daftar(): array
    {
        return app(WilayahService::class)->daftar();
    }

    /**
     * Semua kecamatan wilayah utama dalam satu daftar.
     *
     * @return list<string>
     */
    public static function semuaKecamatan(): array
    {
        return array_merge(...array_values(self::daftar()));
    }

    /**
     * Nama kota/kabupaten wilayah utama, misalnya ["Kota Probolinggo", "Kabupaten Probolinggo"].
     *
     * @return list<string>
     */
    public static function wilayahUtama(): array
    {
        return array_keys(self::daftar());
    }

    /**
     * Kota/kabupaten dari sebuah kecamatan di wilayah utama, atau null jika tidak ada di daftar.
     */
    public static function kotaKabupatenUntuk(?string $kecamatan): ?string
    {
        foreach (self::daftar() as $kotaKabupaten => $daftar) {
            if (in_array($kecamatan, $daftar, true)) {
                return $kotaKabupaten;
            }
        }

        return null;
    }

    public static function diWilayahUtama(?string $kotaKabupaten): bool
    {
        return in_array($kotaKabupaten, self::wilayahUtama(), true);
    }

    /**
     * Apakah teks yang diketik pelanggan sebenarnya menyebut wilayah utama
     * (misalnya "kab. probolinggo"), sehingga seharusnya memilih dari daftar.
     * Awalan "Kota", "Kabupaten", atau "Kab." diabaikan saat membandingkan.
     */
    public static function menyebutWilayahUtama(string $teks): bool
    {
        $teks = mb_strtolower($teks);

        foreach (self::wilayahUtama() as $namaWilayah) {
            $inti = trim((string) preg_replace('/^(kota|kabupaten|kab\.?)\s+/u', '', mb_strtolower($namaWilayah)));

            if ($inti !== '' && str_contains($teks, $inti)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{nama: string, latitude: float, longitude: float}|null
     */
    public static function titikAcuan(): ?array
    {
        $titik = config('jnet.wilayah.titik_acuan');

        if (! is_array($titik) || ! is_numeric($titik['latitude'] ?? null) || ! is_numeric($titik['longitude'] ?? null)) {
            return null;
        }

        return [
            'nama' => (string) ($titik['nama'] ?? 'titik acuan'),
            'latitude' => (float) $titik['latitude'],
            'longitude' => (float) $titik['longitude'],
        ];
    }

    /**
     * Jarak garis lurus (rumus haversine) dari titik acuan, dalam km.
     * Null jika koordinat atau titik acuan belum tersedia. Jarak jalan sebenarnya lebih jauh.
     */
    public static function jarakKm(mixed $latitude, mixed $longitude): ?float
    {
        $acuan = self::titikAcuan();

        if ($acuan === null || ! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        $lat1 = deg2rad($acuan['latitude']);
        $lat2 = deg2rad((float) $latitude);
        $dLat = $lat2 - $lat1;
        $dLng = deg2rad((float) $longitude - $acuan['longitude']);

        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;

        return self::RADIUS_BUMI_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
