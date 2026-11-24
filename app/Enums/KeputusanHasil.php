<?php

namespace App\Enums;

/**
 * Kode keputusan yang disimpan pada kolom hasil_layanan.hasil.
 * Uraian dari admin disimpan terpisah pada kolom hasil_layanan.catatan.
 *
 * Revisi mitra (tanpa survei): satu kunjungan pemasangan hanya punya dua hasil,
 * "Pemasangan berhasil" atau "Tidak layak dipasang" (lihat pilihan()). Keputusan
 * lain berasal dari alur lama (survei terpisah, kunjungan ulang) dan tetap ada
 * agar riwayat hasil yang sudah tersimpan dapat dibaca.
 */
enum KeputusanHasil: string
{
    case Berhasil = 'BERHASIL';
    case TidakLayak = 'TIDAK_LAYAK';

    // Alur lama: hanya untuk riwayat, tidak dapat dipilih lagi.
    case Layak = 'LAYAK';
    case SurveiUlang = 'SURVEI_ULANG';
    case PemasanganUlang = 'PEMASANGAN_ULANG';

    public function label(): string
    {
        return match ($this) {
            self::Berhasil => 'Pemasangan berhasil',
            self::TidakLayak => 'Tidak layak dipasang',
            self::Layak => 'Layak dipasang',
            self::SurveiUlang => 'Perlu survei ulang',
            self::PemasanganUlang => 'Perlu pemasangan ulang',
        };
    }

    /**
     * Keputusan yang dapat dipilih saat mencatat hasil kunjungan.
     *
     * @return list<self>
     */
    public static function pilihan(): array
    {
        return [self::Berhasil, self::TidakLayak];
    }

    /**
     * Uraian wajib diisi agar alasan tidak layak tercatat.
     */
    public function wajibUraian(): bool
    {
        return $this === self::TidakLayak;
    }

    /**
     * Status pengajuan setelah hasil dengan keputusan ini disimpan.
     * Keputusan alur lama tidak dapat dipilih lagi, jadi hanya dua yang dipakai.
     */
    public function statusPengajuanBerikutnya(): StatusPengajuan
    {
        return match ($this) {
            self::Berhasil => StatusPengajuan::Selesai,
            self::TidakLayak => StatusPengajuan::TidakLayak,
            self::Layak => StatusPengajuan::PemasanganDijadwalkan,
            self::SurveiUlang => StatusPengajuan::Diverifikasi,
            self::PemasanganUlang => StatusPengajuan::Layak,
        };
    }
}
