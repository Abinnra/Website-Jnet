<?php

namespace App\Services;

use App\Enums\StatusJadwal;
use App\Enums\StatusPengajuan;
use App\Models\Admin;
use App\Models\PaketInternet;
use App\Models\RegistrasiPelanggan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Aturan bisnis pengajuan berlangganan.
 *
 * Sesuai Sequence Diagram (submit -> periksa paket aktif -> simpan) dan
 * Activity Diagram Pengajuan Revisi 1 (simpan pengajuan, simpan harga paket,
 * tetapkan status Baru dalam satu transaksi).
 */
class PengajuanService
{
    /**
     * Simpan pengajuan baru milik $user.
     *
     * id_user, harga_saat_pengajuan, dan status_pengajuan selalu ditentukan
     * di sini, tidak pernah diambil dari input form.
     *
     * @param  array<string, mixed>  $data  Data yang sudah divalidasi.
     *
     * @throws ValidationException Jika paket tidak lagi aktif saat disimpan.
     */
    public function submit(User $user, array $data): RegistrasiPelanggan
    {
        return DB::transaction(function () use ($user, $data) {
            // Kunci baris paket agar status dan harganya tidak berubah
            // di tengah proses penyimpanan.
            $paket = PaketInternet::query()
                ->whereKey($data['id_paket'])
                ->lockForUpdate()
                ->first();

            if (! $paket || ! $paket->status_aktif) {
                throw ValidationException::withMessages([
                    'id_paket' => 'Paket yang dipilih sudah tidak tersedia. Silakan pilih paket lain.',
                ]);
            }

            $registrasi = new RegistrasiPelanggan([
                'nama_pelanggan' => $data['nama_pelanggan'],
                'no_whatsapp' => $data['no_whatsapp'],
                'alamat_pemasangan' => $data['alamat_pemasangan'],
                'kecamatan' => $data['kecamatan'],
                'kota_kabupaten' => $data['kota_kabupaten'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $registrasi->id_user = $user->id_user;
            $registrasi->id_paket = $paket->id_paket;
            $registrasi->harga_saat_pengajuan = $paket->harga;
            $registrasi->status_pengajuan = StatusPengajuan::Baru;
            $registrasi->save();

            return $registrasi;
        });
    }

    /**
     * Ubah status secara MANUAL oleh admin (Class Diagram: updateStatus(),
     * Admin.updatePengajuan()).
     *
     * Hanya transisi di StatusPengajuan::transisiManual() yang diizinkan:
     * BARU -> DIVERIFIKASI / DITOLAK / DIBATALKAN, dan status aktif lain
     * -> DIBATALKAN. Status lain berubah otomatis melalui jadwal dan hasil.
     *
     * Jika pengajuan dibatalkan, jadwal yang masih TERJADWAL ikut dibatalkan.
     *
     * @throws ValidationException Jika transisi tidak diizinkan.
     */
    public function ubahStatus(RegistrasiPelanggan $registrasi, StatusPengajuan $tujuan, Admin $admin, ?string $pesanUntukPelanggan = null): RegistrasiPelanggan
    {
        return DB::transaction(function () use ($registrasi, $tujuan, $admin, $pesanUntukPelanggan) {
            // Baca ulang dengan kunci baris agar dua admin tidak mengubah status bersamaan.
            $terkini = RegistrasiPelanggan::query()->lockForUpdate()->findOrFail($registrasi->id_registrasi);
            $asal = $terkini->status_pengajuan;

            if (! $asal->bolehManualKe($tujuan)) {
                throw ValidationException::withMessages([
                    'status' => "Status tidak dapat diubah dari \"{$asal->label()}\" menjadi \"{$tujuan->label()}\".",
                ]);
            }

            if ($tujuan === StatusPengajuan::Dibatalkan) {
                $terkini->jadwal()
                    ->where('status_jadwal', StatusJadwal::Terjadwal->value)
                    ->update([
                        'status_jadwal' => StatusJadwal::Dibatalkan->value,
                        'updated_at' => now(),
                    ]);
            }

            $terkini->status_pengajuan = $tujuan;
            $terkini->updated_by_admin_id = $admin->id_admin;

            // Pesan kosong tidak menghapus pesan sebelumnya.
            if ($pesanUntukPelanggan !== null) {
                $terkini->pesan_untuk_pelanggan = $pesanUntukPelanggan;
            }

            $terkini->save();

            return $terkini;
        });
    }

    /**
     * Ganti (atau hapus, jika null) pesan yang tampil di halaman pelanggan.
     */
    public function simpanPesan(RegistrasiPelanggan $registrasi, ?string $pesan, Admin $admin): bool
    {
        if ($registrasi->pesan_untuk_pelanggan === $pesan) {
            return false;
        }

        $registrasi->pesan_untuk_pelanggan = $pesan;
        $registrasi->updated_by_admin_id = $admin->id_admin;
        $registrasi->save();

        return true;
    }
}
