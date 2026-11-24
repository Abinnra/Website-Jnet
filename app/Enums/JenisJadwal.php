<?php

namespace App\Enums;

/**
 * Nilai kolom jadwal_layanan.jenis_jadwal (ENUM di database).
 *
 * Revisi mitra (tanpa survei): setiap kunjungan baru berupa PEMASANGAN. Teknisi
 * mengecek lokasi saat datang, lalu memasang atau menyatakan tidak layak.
 * SURVEI hanya tersisa pada jadwal lama dan tidak dapat dibuat lagi.
 */
enum JenisJadwal: string
{
    case Survei = 'SURVEI';
    case Pemasangan = 'PEMASANGAN';

    public function label(): string
    {
        return match ($this) {
            self::Survei => 'Survei',
            self::Pemasangan => 'Pemasangan',
        };
    }

    /**
     * Bentuk huruf kecil, misalnya untuk nama file dokumentasi.
     */
    public function slug(): string
    {
        return strtolower($this->value);
    }

    /**
     * Jenis jadwal yang boleh dibuat untuk pengajuan berstatus $status, atau null
     * jika pengajuan belum memenuhi syarat (penjadwalan ditunda). Layak hanya ada
     * pada data lama (menunggu pemasangan ulang).
     */
    public static function untukStatus(StatusPengajuan $status): ?self
    {
        return in_array($status, [StatusPengajuan::Diverifikasi, StatusPengajuan::Layak], true)
            ? self::Pemasangan
            : null;
    }

    /**
     * Status pengajuan setelah jadwal jenis ini disimpan. Hasil kunjungan hanya dapat
     * dicatat selama pengajuan masih berstatus ini.
     */
    public function statusSetelahDijadwalkan(): StatusPengajuan
    {
        return match ($this) {
            self::Survei => StatusPengajuan::SurveiDijadwalkan,
            self::Pemasangan => StatusPengajuan::PemasanganDijadwalkan,
        };
    }
}
