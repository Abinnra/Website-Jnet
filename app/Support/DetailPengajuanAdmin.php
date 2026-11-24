<?php

namespace App\Support;

use App\Enums\JenisJadwal;
use App\Enums\StatusPengajuan;
use App\Models\JadwalLayanan;
use App\Models\RegistrasiPelanggan;

/**
 * Revisi (Vue): data halaman detail pengajuan admin (Pages/Admin/Pengajuan/Show.vue).
 * Isi dan aturannya sama dengan tampilan Blade sebelumnya: tindakan mengikuti transisi status
 * yang diizinkan, hasil hanya dapat dicatat mulai tanggal kunjungan.
 */
class DetailPengajuanAdmin
{
    /**
     * @return array<string, mixed>
     */
    public static function data(RegistrasiPelanggan $pengajuan): array
    {
        $status = $pengajuan->status_pengajuan;
        $paket = $pengajuan->paket;
        $titikAcuan = Wilayah::titikAcuan();
        $jarak = Format::jarak($pengajuan->jarak_km);
        $keteranganJarak = $jarak && $titikAcuan ? "{$jarak} garis lurus dari {$titikAcuan['nama']}" : null;
        $adaTitik = $pengajuan->latitude !== null && $pengajuan->longitude !== null;
        $jadwalAktif = $pengajuan->jadwal->filter(fn (JadwalLayanan $jadwal) => $jadwal->status_jadwal->isAktif())->count();
        // Kunjungan aktif terdekat: hasilnya dicatat dari kartu Tindakan, mulai tanggal kunjungannya.
        $kunjunganBerikutnya = $pengajuan->jadwal
            ->filter(fn (JadwalLayanan $jadwal) => $jadwal->status_jadwal->isAktif())
            ->sortBy(fn (JadwalLayanan $jadwal) => $jadwal->tanggal->format('Y-m-d').' '.$jadwal->waktu_mulai)
            ->first();
        $hasilDapatDicatat = $kunjunganBerikutnya !== null && ! $kunjunganBerikutnya->tanggal->isAfter(today());
        $jenisDapatDijadwalkan = JenisJadwal::untukStatus($status);

        $penjelasanAksi = [
            StatusPengajuan::Ditolak->value => 'Pengajuan akan ditolak dan tidak dapat diproses lagi. Tulis alasannya agar pelanggan dapat membacanya.',
            StatusPengajuan::Dibatalkan->value => 'Pengajuan akan dibatalkan dan tidak dapat diproses lagi.'
                .($jadwalAktif > 0 ? " {$jadwalAktif} jadwal yang masih aktif ikut dibatalkan." : ''),
        ];

        return [
            'pengajuan' => [
                'nomor' => $pengajuan->nomor_pengajuan,
                'diajukan' => Tampilan::tanggal($pengajuan->created_at, 'd F Y, H.i').' WIB',
                'status' => Tampilan::lencana($status),
                'nama' => $pengajuan->nama_pelanggan,
                'whatsapp' => '+'.$pengajuan->no_whatsapp,
                'tautanWhatsapp' => Format::tautanWhatsapp(
                    $pengajuan->no_whatsapp,
                    "Halo {$pengajuan->nama_pelanggan}, kami dari JNET terkait pengajuan {$pengajuan->nomor_pengajuan_pelanggan}.",
                ),
                'email' => $pengajuan->user->email,
                'tautanLupaSandi' => route('admin.sandi-pelanggan.create', ['email' => $pengajuan->user->email]),
                'alamat' => $pengajuan->alamat_pemasangan,
                'kecamatan' => $pengajuan->kecamatan,
                'kota' => $pengajuan->kota_kabupaten,
                'luarWilayah' => $pengajuan->luar_wilayah,
                'titik' => $adaTitik ? [
                    'koordinat' => "{$pengajuan->latitude}, {$pengajuan->longitude}",
                    'osm' => "https://www.openstreetmap.org/?mlat={$pengajuan->latitude}&mlon={$pengajuan->longitude}#map=18/{$pengajuan->latitude}/{$pengajuan->longitude}",
                    'gmaps' => "https://www.google.com/maps/search/?api=1&query={$pengajuan->latitude},{$pengajuan->longitude}",
                    'jarak' => $keteranganJarak,
                ] : null,
                'catatan' => $pengajuan->catatan,
                'paket' => $paket->nama_paket.(str_contains($paket->nama_paket, $paket->kecepatan_mbps.' Mbps') ? '' : " ({$paket->kecepatan_mbps} Mbps)"),
                'paketNonaktif' => ! $paket->status_aktif,
                'harga' => Format::rupiah($pengajuan->harga_saat_pengajuan),
                'hargaSekarang' => (float) $paket->harga !== (float) $pengajuan->harga_saat_pengajuan
                    ? 'Harga paket sekarang '.Format::rupiah($paket->harga)
                    : null,
                'pesanUntukPelanggan' => $pengajuan->pesan_untuk_pelanggan,
                'diperbarui' => Tampilan::tanggal($pengajuan->updated_at, 'd F Y, H.i').' WIB'
                    .($pengajuan->diperbaruiOleh ? ' oleh '.$pengajuan->diperbaruiOleh->nama : ''),
            ],

            'jadwal' => $pengajuan->jadwal->map(fn (JadwalLayanan $jadwal) => [
                'id' => $jadwal->id_jadwal,
                'jenis' => $jadwal->jenis_jadwal->label(),
                'waktu' => $jadwal->tanggal->translatedFormat('l, d F Y').', pukul '.Tampilan::jam($jadwal->waktu_mulai).' WIB',
                'catatan' => $jadwal->catatan,
                'status' => Tampilan::lencana($jadwal->status_jadwal),
                'tautanUbah' => $jadwal->status_jadwal->isAktif() ? route('admin.jadwal.edit', $jadwal) : null,
                'hasil' => $jadwal->hasil ? [
                    'label' => $jadwal->hasil->hasil->label(),
                    'catatan' => $jadwal->hasil->catatan,
                    'dicatat' => 'Dicatat '.Tampilan::tanggal($jadwal->hasil->created_at, 'd F Y, H.i').' WIB oleh '.($jadwal->hasil->admin?->nama ?? 'admin'),
                    'tautanDokumentasi' => $jadwal->hasil->dokumentasi_url ? route('admin.hasil.dokumentasi', $jadwal->hasil) : null,
                ] : null,
            ])->values()->all(),

            'tindakan' => [
                'langkah' => $status->langkahAdmin(),
                // Kabar status siap kirim (status, jadwal aktif, dan pesan untuk pelanggan).
                'tautanKabar' => Format::tautanWhatsapp($pengajuan->no_whatsapp, PesanWhatsapp::kabarStatus($pengajuan)),
                'konfirmasiJangkauan' => $pengajuan->luar_wilayah && $status === StatusPengajuan::Baru
                    ? "Lokasi di {$pengajuan->kota_kabupaten}".($keteranganJarak ? ", {$keteranganJarak}" : '').'.'
                    : null,
                // Tombol utama tepat di bawah kabar WhatsApp, seperti tombol "Jadwalkan pemasangan".
                'catatHasil' => $hasilDapatDicatat ? [
                    'label' => 'Catat hasil pemasangan',
                    'tautan' => route('admin.hasil.create', $kunjunganBerikutnya),
                ] : null,
                'catatHasilMulai' => $kunjunganBerikutnya !== null && ! $hasilDapatDicatat
                    ? 'Hasil pemasangan dapat dicatat mulai '.$kunjunganBerikutnya->tanggal->translatedFormat('l, d F Y').'.'
                    : null,
                'jadwalkan' => $jenisDapatDijadwalkan ? [
                    'label' => 'Jadwalkan '.mb_strtolower($jenisDapatDijadwalkan->label()),
                    'tautan' => route('admin.jadwal.create', $pengajuan),
                ] : null,
                // Tindakan yang tidak dapat dibatalkan (tolak, batalkan) memerlukan konfirmasi kedua.
                'transisi' => array_map(fn (StatusPengajuan $tujuan) => [
                    'status' => $tujuan->value,
                    'label' => $tujuan->labelAksi(),
                    'konfirmasi' => $tujuan !== StatusPengajuan::Diverifikasi,
                    'penjelasan' => $penjelasanAksi[$tujuan->value] ?? null,
                ], $status->transisiManual()),
            ],

            'tautan' => [
                'daftar' => route('admin.pengajuan.index'),
                'status' => route('admin.pengajuan.status', $pengajuan),
                'pesan' => route('admin.pengajuan.pesan', $pengajuan),
            ],
        ];
    }
}
