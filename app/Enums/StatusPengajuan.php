<?php

namespace App\Enums;

/**
 * Status pada kolom registrasi_pelanggan.status_pengajuan.
 *
 * Sesuai Class Diagram Revisi 1 (enum StatusPengajuan). Ditampilkan kepada
 * pelanggan sebagai alur 4 tahap (revisi mitra: maksimal 4–5 tahap). Nilai yang
 * disimpan ke database adalah string pada setiap case (misalnya 'BARU').
 *
 * Revisi mitra (tanpa survei): setelah data diverifikasi, admin langsung menjadwalkan
 * kunjungan pemasangan; teknisi mengecek lokasi lalu memasang atau menyatakan tidak layak.
 * SurveiDijadwalkan dan Layak tidak lagi dihasilkan alur baru dan hanya tersisa pada
 * data lama (lihat masihDipakai()).
 */
enum StatusPengajuan: string
{
    case Baru = 'BARU';
    case Diverifikasi = 'DIVERIFIKASI';
    case Ditolak = 'DITOLAK';
    case SurveiDijadwalkan = 'SURVEI_DIJADWALKAN';
    case Layak = 'LAYAK';
    case TidakLayak = 'TIDAK_LAYAK';
    case PemasanganDijadwalkan = 'PEMASANGAN_DIJADWALKAN';
    case Selesai = 'SELESAI';
    case Dibatalkan = 'DIBATALKAN';

    /**
     * Label untuk ditampilkan kepada pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Diverifikasi => 'Data Diverifikasi',
            self::Ditolak => 'Ditolak',
            self::SurveiDijadwalkan => 'Survei Dijadwalkan',
            self::Layak => 'Layak Dipasang',
            self::TidakLayak => 'Tidak Layak',
            self::PemasanganDijadwalkan => 'Pemasangan Dijadwalkan',
            self::Selesai => 'Terpasang',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /**
     * Penjelasan untuk pelanggan: arti status dan langkah berikutnya.
     */
    public function keterangan(): string
    {
        return match ($this) {
            self::Baru => 'Pengajuan diterima dan menunggu pemeriksaan data oleh admin.',
            self::Diverifikasi => 'Data Anda sudah diperiksa. Admin akan menjadwalkan kunjungan pemasangan.',
            self::Ditolak => 'Pengajuan tidak dapat diproses. Hubungi JNET untuk informasi lebih lanjut.',
            self::SurveiDijadwalkan => 'Kunjungan teknisi sudah dijadwalkan. Lihat jadwal kunjungan di bawah.',
            self::Layak => 'Lokasi Anda memenuhi syarat. Admin akan menjadwalkan pemasangan.',
            self::TidakLayak => 'Berdasarkan pengecekan teknisi di lokasi, lokasi Anda belum dapat dipasang. Hubungi JNET untuk informasi lebih lanjut.',
            self::PemasanganDijadwalkan => 'Pemasangan sudah dijadwalkan. Teknisi akan mengecek lokasi lalu langsung memasang. Lihat jadwal kunjungan di bawah.',
            self::Selesai => 'Pemasangan selesai. Terima kasih telah berlangganan JNET.',
            self::Dibatalkan => 'Pengajuan ini dibatalkan. Hubungi JNET jika ada pertanyaan.',
        };
    }

    /**
     * Petunjuk langkah berikutnya untuk admin.
     */
    public function langkahAdmin(): string
    {
        return match ($this) {
            self::Baru => 'Periksa data calon pelanggan, lalu verifikasi atau tolak pengajuan.',
            self::Diverifikasi, self::Layak => 'Jadwalkan pemasangan.',
            self::SurveiDijadwalkan,
            self::PemasanganDijadwalkan => 'Catat hasil pemasangan setelah kunjungan: pemasangan berhasil atau tidak layak dipasang.',
            default => 'Pengajuan selesai diproses. Status tidak dapat diubah lagi.',
        };
    }

    /**
     * Label tombol admin untuk berpindah KE status ini secara manual.
     */
    public function labelAksi(): string
    {
        return match ($this) {
            self::Diverifikasi => 'Verifikasi data',
            self::Ditolak => 'Tolak pengajuan',
            self::Dibatalkan => 'Batalkan pengajuan',
            default => 'Ubah ke '.$this->label(),
        };
    }

    /**
     * Nama empat langkah alur berlangganan, berurutan (lihat langkah()).
     * Tidak ada tahap survei: teknisi mengecek lokasi pada kunjungan pemasangan.
     *
     * @return list<string>
     */
    public static function namaLangkah(): array
    {
        return [
            'Pengajuan dikirim',
            'Pemeriksaan data',
            'Pemasangan',
            'Terpasang',
        ];
    }

    /**
     * False untuk status alur lama (sebelum survei dihapus) yang tidak lagi dihasilkan.
     * Status ini hanya ditampilkan di ringkasan dan filter admin selama masih ada datanya.
     */
    public function masihDipakai(): bool
    {
        return ! in_array($this, [self::SurveiDijadwalkan, self::Layak], true);
    }

    /**
     * Varian warna badge. Dipakai sebagai kelas CSS: badge--{warna}.
     * Badge selalu menampilkan teks label, jadi makna tidak hanya
     * bergantung pada warna.
     */
    public function warna(): string
    {
        return match ($this) {
            self::Baru => 'netral',
            self::Diverifikasi => 'info',
            self::SurveiDijadwalkan, self::PemasanganDijadwalkan => 'proses',
            self::Layak, self::Selesai => 'sukses',
            self::Ditolak, self::TidakLayak => 'bahaya',
            self::Dibatalkan => 'redup',
        };
    }

    /**
     * Status final tidak dapat diubah lagi.
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::Selesai,
            self::Ditolak,
            self::TidakLayak,
            self::Dibatalkan,
        ], true);
    }

    /**
     * Status tujuan yang boleh dipilih admin secara MANUAL dari status ini.
     * Perubahan lain (dijadwalkan, layak, selesai, dan seterusnya) hanya
     * terjadi otomatis melalui service saat jadwal atau hasil disimpan.
     *
     * @return list<self>
     */
    public function transisiManual(): array
    {
        return match ($this) {
            self::Baru => [self::Diverifikasi, self::Ditolak, self::Dibatalkan],
            self::Diverifikasi,
            self::SurveiDijadwalkan,
            self::Layak,
            self::PemasanganDijadwalkan => [self::Dibatalkan],
            default => [],
        };
    }

    public function bolehManualKe(self $tujuan): bool
    {
        return in_array($tujuan, $this->transisiManual(), true);
    }

    /**
     * Posisi pada alur 4 langkah (halaman Alur Berlangganan dan pelacak
     * progres pelanggan):
     * 1 Pengajuan dikirim, 2 Pemeriksaan data, 3 Pemasangan, 4 Terpasang.
     *
     * Ditolak berhenti di langkah 2, Tidak Layak berhenti di langkah 3 (hasil
     * kunjungan pemasangan). Status alur lama (Survei Dijadwalkan, Layak) berada
     * di langkah 3. Dibatalkan dapat terjadi di langkah mana pun, sehingga bernilai
     * null dan ditampilkan sebagai pemberitahuan tersendiri.
     */
    public function langkah(): ?int
    {
        return match ($this) {
            self::Baru => 1,
            self::Diverifikasi, self::Ditolak => 2,
            self::SurveiDijadwalkan, self::Layak, self::PemasanganDijadwalkan, self::TidakLayak => 3,
            self::Selesai => 4,
            self::Dibatalkan => null,
        };
    }

    /**
     * True jika alur berhenti sebelum selesai (ditolak, tidak layak,
     * atau dibatalkan).
     */
    public function isBerhenti(): bool
    {
        return in_array($this, [self::Ditolak, self::TidakLayak, self::Dibatalkan], true);
    }

    /**
     * Pasangan nilai => label untuk filter dan dropdown.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $hasil = [];

        foreach (self::cases() as $status) {
            $hasil[$status->value] = $status->label();
        }

        return $hasil;
    }
}
