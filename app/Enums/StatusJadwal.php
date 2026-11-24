<?php

namespace App\Enums;

/**
 * Status pada kolom jadwal_layanan.status_jadwal.
 *
 * TERJADWAL  : jadwal dibuat; tanggal dan jam masih dapat diubah.
 * SELESAI    : hasil layanan sudah dicatat.
 * DIBATALKAN : pengajuan terkait dibatalkan admin.
 */
enum StatusJadwal: string
{
    case Terjadwal = 'TERJADWAL';
    case Selesai = 'SELESAI';
    case Dibatalkan = 'DIBATALKAN';

    public function label(): string
    {
        return match ($this) {
            self::Terjadwal => 'Terjadwal',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Terjadwal => 'proses',
            self::Selesai => 'sukses',
            self::Dibatalkan => 'redup',
        };
    }

    /**
     * Jadwal aktif masih dapat diubah dan dicatat hasilnya.
     */
    public function isAktif(): bool
    {
        return $this === self::Terjadwal;
    }
}
