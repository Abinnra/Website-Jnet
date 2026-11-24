<?php

namespace App\Support;

/**
 * Fungsi bantu format tampilan yang dipakai di banyak halaman.
 */
class Format
{
    /**
     * Format angka menjadi rupiah tanpa desimal, contoh: Rp125.000.
     */
    public static function rupiah(int|float|string|null $nilai): string
    {
        return 'Rp'.number_format((float) $nilai, 0, ',', '.');
    }

    /**
     * Ubah nomor WhatsApp ke format internasional 628xxxxxxxxx.
     * Menerima 08xx, +628xx, 628xx, atau 8xx (spasi dan tanda hubung diabaikan).
     * Mengembalikan null jika nomor tidak valid.
     */
    public static function nomorWhatsapp(?string $nomor): ?string
    {
        $angka = preg_replace('/\D+/', '', (string) $nomor);

        if (str_starts_with($angka, '0')) {
            $angka = '62'.substr($angka, 1);
        } elseif (str_starts_with($angka, '8')) {
            $angka = '62'.$angka;
        }

        return preg_match('/^628\d{7,11}$/', $angka) ? $angka : null;
    }

    /**
     * Tautan wa.me untuk membuka percakapan WhatsApp.
     * Mengembalikan null jika nomor kosong atau tidak valid, sehingga
     * tombol WhatsApp dapat disembunyikan.
     */
    public static function tautanWhatsapp(?string $nomor, string $pesan = ''): ?string
    {
        $nomorValid = self::nomorWhatsapp($nomor);

        if ($nomorValid === null) {
            return null;
        }

        return 'https://wa.me/'.$nomorValid.($pesan !== '' ? '?text='.rawurlencode($pesan) : '');
    }

    /**
     * Jarak garis lurus untuk ditampilkan, misalnya "±42 km" atau "±3,5 km".
     */
    public static function jarak(?float $km): ?string
    {
        if ($km === null) {
            return null;
        }

        return '±'.number_format($km, $km < 10 ? 1 : 0, ',', '.').' km';
    }
}
