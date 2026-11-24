<?php

namespace App\Support;

use App\Enums\StatusPengajuan;
use App\Models\JadwalLayanan;
use App\Models\RegistrasiPelanggan;
use App\Services\SiteSettingService;

/**
 * Revisi (Vue): data halaman detail pengajuan milik pelanggan (Pages/Akun/Pengajuan.vue).
 * Catatan internal admin pada jadwal dan uraian hasil tidak pernah ikut dikirim.
 */
class DetailPengajuanPelanggan
{
    /**
     * @return array<string, mixed>
     */
    public static function data(RegistrasiPelanggan $pengajuan): array
    {
        $status = $pengajuan->status_pengajuan;
        $langkahSekarang = $status->langkah();
        $paket = $pengajuan->paket;
        // Pesan WhatsApp dibaca admin, jadi tetap memakai nomor lengkap dengan urutan harian.
        $tautanWa = app(SiteSettingService::class)
            ->tautanWhatsapp('Halo JNET, saya ingin menanyakan pengajuan '.$pengajuan->nomor_pengajuan.'.');

        return [
            'nomor' => $pengajuan->nomor_pengajuan_pelanggan,
            'diajukan' => Tampilan::tanggal($pengajuan->created_at, 'd F Y, H.i').' WIB',
            'status' => Tampilan::lencana($status),
            'keterangan' => $status->keterangan(),
            'final' => $status->isFinal(),
            'luarWilayah' => (bool) $pengajuan->luar_wilayah,
            'luarWilayahMenunggu' => $pengajuan->luar_wilayah && $status === StatusPengajuan::Baru,
            'langkah' => $langkahSekarang === null ? null : self::langkah($status, $langkahSekarang),
            'pesan' => $pengajuan->pesan_untuk_pelanggan,
            'tautanWa' => $tautanWa && ($status->isBerhenti() || $status === StatusPengajuan::Baru) ? $tautanWa : null,
            // Jadwal kunjungan: hanya jenis, tanggal, jam, dan status.
            'jadwal' => $pengajuan->jadwal->map(fn (JadwalLayanan $jadwal) => [
                'id' => $jadwal->id_jadwal,
                'jenis' => $jadwal->jenis_jadwal->label(),
                'waktu' => $jadwal->tanggal->translatedFormat('l, d F Y').', pukul '.Tampilan::jam($jadwal->waktu_mulai).' WIB',
                'status' => Tampilan::lencana($jadwal->status_jadwal),
            ])->values()->all(),
            'jadwalKosong' => $status->isBerhenti()
                ? 'Tidak ada jadwal kunjungan untuk pengajuan ini.'
                : 'Belum ada jadwal kunjungan. Jadwal pemasangan akan muncul di sini setelah data Anda diperiksa.',
            'data' => [
                'paket' => $paket->nama_paket.(str_contains($paket->nama_paket, $paket->kecepatan_mbps.' Mbps') ? '' : " ({$paket->kecepatan_mbps} Mbps)"),
                'harga' => Format::rupiah($pengajuan->harga_saat_pengajuan).config('jnet.paket.label_periode_harga'),
                'nama' => $pengajuan->nama_pelanggan,
                'whatsapp' => '+'.$pengajuan->no_whatsapp,
                'alamat' => $pengajuan->alamat_pemasangan,
                'kecamatan' => $pengajuan->kecamatan,
                'kota' => $pengajuan->kota_kabupaten,
                'peta' => $pengajuan->latitude !== null && $pengajuan->longitude !== null
                    ? "https://www.openstreetmap.org/?mlat={$pengajuan->latitude}&mlon={$pengajuan->longitude}#map=18/{$pengajuan->latitude}/{$pengajuan->longitude}"
                    : null,
                'catatan' => $pengajuan->catatan,
            ],
        ];
    }

    /**
     * Pelacak 4 tahap: keadaan setiap tahap untuk status sekarang.
     *
     * @return list<array{nomor: int, nama: string, keadaan: string, teks: string, sekarang: bool}>
     */
    private static function langkah(StatusPengajuan $status, int $langkahSekarang): array
    {
        $teks = ['selesai' => 'selesai', 'aktif' => 'sedang berlangsung', 'berhenti' => 'berhenti di tahap ini', 'belum' => 'belum'];
        $hasil = [];

        foreach (StatusPengajuan::namaLangkah() as $indeks => $nama) {
            $nomor = $indeks + 1;
            $keadaan = match (true) {
                $nomor < $langkahSekarang => 'selesai',
                $nomor === $langkahSekarang && $status->isBerhenti() => 'berhenti',
                $nomor === $langkahSekarang && $status === StatusPengajuan::Selesai => 'selesai',
                $nomor === $langkahSekarang => 'aktif',
                default => 'belum',
            };

            $hasil[] = [
                'nomor' => $nomor,
                'nama' => $nama,
                'keadaan' => $keadaan,
                'teks' => $teks[$keadaan],
                'sekarang' => $nomor === $langkahSekarang,
            ];
        }

        return $hasil;
    }
}
