<?php

namespace App\Support;

use App\Models\RegistrasiPelanggan;

/**
 * Teks pesan WhatsApp siap kirim dari admin ke pelanggan.
 *
 * Hanya menyusun tautan wa.me (tanpa layanan berbayar). Pesan dikirim sendiri
 * oleh admin dari WhatsApp-nya, sehingga tidak mengubah alur sistem di UML.
 */
class PesanWhatsapp
{
    /**
     * Kabar status terbaru sebuah pengajuan, berisi jadwal aktif dan pesan admin (jika ada).
     */
    public static function kabarStatus(RegistrasiPelanggan $pengajuan): string
    {
        $status = $pengajuan->status_pengajuan;

        $baris = [
            "Halo {$pengajuan->nama_pelanggan}, kami dari JNET ingin mengabarkan perkembangan pengajuan pemasangan {$pengajuan->nomor_pengajuan_pelanggan}.",
            '',
            "Status: {$status->label()}",
            $status->keterangan(),
        ];

        $jadwal = $pengajuan->jadwal
            ->filter(fn ($item) => $item->status_jadwal->isAktif())
            ->sortBy(fn ($item) => $item->tanggal->format('Y-m-d').' '.$item->waktu_mulai)
            ->first();

        if ($jadwal) {
            $baris[] = '';
            $baris[] = 'Jadwal '.mb_strtolower($jadwal->jenis_jadwal->label()).': '
                .$jadwal->tanggal->translatedFormat('l, d F Y').', pukul '.substr((string) $jadwal->waktu_mulai, 0, 5).' WIB';
        }

        if (filled($pengajuan->pesan_untuk_pelanggan)) {
            $baris[] = '';
            $baris[] = 'Pesan dari JNET: '.$pengajuan->pesan_untuk_pelanggan;
        }

        $baris[] = '';
        $baris[] = 'Detail pengajuan dapat dilihat setelah masuk ke akun Anda: '.route('pengajuan.show', $pengajuan->id_registrasi);

        return implode("\n", $baris);
    }
}
