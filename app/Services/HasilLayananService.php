<?php

namespace App\Services;

use App\Enums\KeputusanHasil;
use App\Enums\StatusJadwal;
use App\Models\Admin;
use App\Models\HasilLayanan;
use App\Models\JadwalLayanan;
use App\Models\RegistrasiPelanggan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Pencatatan hasil kunjungan pemasangan (Class Diagram: HasilLayanan.simpanHasil()).
 *
 * Mengikuti Activity Diagram Pengelolaan Jadwal Revisi 1:
 * simpan hasil layanan -> perbarui status jadwal -> perbarui status
 * pengajuan sesuai keputusan, dalam satu transaksi.
 *
 * Revisi mitra (tanpa survei): hanya dua keputusan, "Pemasangan berhasil" atau
 * "Tidak layak dipasang"; keduanya mengakhiri pengajuan. Jadwal survei lama yang
 * masih aktif juga dicatat dengan dua keputusan ini.
 */
class HasilLayananService
{
    /**
     * @throws ValidationException
     */
    public function simpan(
        JadwalLayanan $jadwal,
        KeputusanHasil $keputusan,
        ?string $catatan,
        ?UploadedFile $dokumentasi,
        Admin $admin,
        ?string $pesanUntukPelanggan = null,
    ): HasilLayanan {
        $this->pastikanDapatDicatat($jadwal);

        if (! in_array($keputusan, KeputusanHasil::pilihan(), true)) {
            throw ValidationException::withMessages([
                'hasil' => 'Pilih "Pemasangan berhasil" atau "Tidak layak dipasang".',
            ]);
        }

        // File disimpan lebih dulu di disk privat. Jika transaksi gagal, file dihapus lagi.
        $pathDokumentasi = $dokumentasi ? $this->simpanFile($dokumentasi) : null;

        try {
            return DB::transaction(function () use ($jadwal, $keputusan, $catatan, $pathDokumentasi, $admin, $pesanUntukPelanggan) {
                $jadwalTerkini = JadwalLayanan::query()->lockForUpdate()->findOrFail($jadwal->id_jadwal);
                $registrasi = RegistrasiPelanggan::query()->lockForUpdate()->findOrFail($jadwalTerkini->id_registrasi);

                $this->pastikanDapatDicatat($jadwalTerkini);

                $statusDiharapkan = $jadwalTerkini->jenis_jadwal->statusSetelahDijadwalkan();

                if ($registrasi->status_pengajuan !== $statusDiharapkan) {
                    throw ValidationException::withMessages([
                        'hasil' => "Hasil tidak dapat dicatat karena status pengajuan saat ini \"{$registrasi->status_pengajuan->label()}\".",
                    ]);
                }

                // 1. Simpan hasil layanan.
                $hasil = new HasilLayanan(['catatan' => $catatan]);
                $hasil->id_jadwal = $jadwalTerkini->id_jadwal;
                $hasil->id_admin = $admin->id_admin;
                $hasil->hasil = $keputusan;
                $hasil->dokumentasi_url = $pathDokumentasi;
                $hasil->save();

                // 2. Perbarui status jadwal.
                $jadwalTerkini->status_jadwal = StatusJadwal::Selesai;
                $jadwalTerkini->save();

                // 3. Perbarui status pengajuan sesuai keputusan.
                $registrasi->status_pengajuan = $keputusan->statusPengajuanBerikutnya();
                $registrasi->updated_by_admin_id = $admin->id_admin;

                // 4. Pesan untuk pelanggan (opsional); kosong tidak menghapus pesan sebelumnya.
                if ($pesanUntukPelanggan !== null) {
                    $registrasi->pesan_untuk_pelanggan = $pesanUntukPelanggan;
                }

                $registrasi->save();

                return $hasil;
            });
        } catch (Throwable $e) {
            if ($pathDokumentasi) {
                Storage::disk($this->disk())->delete($pathDokumentasi);
            }

            throw $e;
        }
    }

    /**
     * Hasil hanya untuk jadwal TERJADWAL yang tanggalnya sudah tiba
     * (aturan sementara: kunjungan belum terjadi sebelum tanggalnya).
     *
     * @throws ValidationException
     */
    public function pastikanDapatDicatat(JadwalLayanan $jadwal): void
    {
        if (! $jadwal->status_jadwal->isAktif()) {
            throw ValidationException::withMessages([
                'hasil' => "Jadwal berstatus \"{$jadwal->status_jadwal->label()}\" sudah tidak dapat dicatat hasilnya.",
            ]);
        }

        if ($jadwal->tanggal->isAfter(today())) {
            throw ValidationException::withMessages([
                'hasil' => 'Hasil dapat dicatat mulai tanggal kunjungan, '.$jadwal->tanggal->translatedFormat('d F Y').'.',
            ]);
        }
    }

    public function disk(): string
    {
        return config('jnet.unggahan.dokumentasi.disk', 'local');
    }

    /**
     * Simpan dengan nama acak di folder dokumentasi/{tahun}/{bulan}.
     * Nama file asli dari pengguna tidak dipakai.
     */
    private function simpanFile(UploadedFile $file): string
    {
        $folder = config('jnet.unggahan.dokumentasi.folder', 'dokumentasi').'/'.now()->format('Y/m');

        $path = $file->store($folder, $this->disk());

        if ($path === false) {
            throw ValidationException::withMessages([
                'dokumentasi' => 'Dokumentasi gagal disimpan. Coba unggah ulang.',
            ]);
        }

        return $path;
    }
}
