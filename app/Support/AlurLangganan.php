<?php

namespace App\Support;

/**
 * Empat tahap berlangganan (revisi mitra: maksimal 4–5 tahap sampai terpasang, tanpa
 * survei terpisah), selaras dengan StatusPengajuan::langkah(). Dipakai bersama oleh
 * daftar langkah dan banner halaman Alur Berlangganan.
 */
class AlurLangganan
{
    /**
     * @return list<array{0: string, 1: string}> [judul, keterangan]
     */
    public static function langkah(): array
    {
        return [
            ['Daftar dan ajukan', 'Buat akun, pilih paket, lalu isi data dan alamat pemasangan.'],
            ['Pemeriksaan data', 'Admin JNET memeriksa kelengkapan data pengajuan Anda, lalu menjadwalkan pemasangan.'],
            ['Pemasangan', 'Teknisi datang sesuai jadwal, mengecek lokasi, lalu langsung memasang jika lokasi memenuhi syarat.'],
            ['Terpasang', 'Internet siap dipakai dan status pengajuan di akun Anda menjadi Terpasang.'],
        ];
    }
}
