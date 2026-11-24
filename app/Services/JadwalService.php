<?php

namespace App\Services;

use App\Enums\JenisJadwal;
use App\Enums\StatusJadwal;
use App\Enums\StatusPengajuan;
use App\Models\Admin;
use App\Models\JadwalLayanan;
use App\Models\RegistrasiPelanggan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Aturan bisnis penjadwalan kunjungan pemasangan.
 *
 * Mengikuti Activity Diagram Pengelolaan Jadwal Revisi 1:
 * periksa syarat (jika tidak memenuhi: tunda) -> validasi jadwal ->
 * simpan jadwal -> perbarui status pengajuan.
 *
 * Aturan:
 * - MITRA     : tanpa survei terpisah; PEMASANGAN dijadwalkan langsung untuk status
 *               Diverifikasi (atau Layak pada data lama), lihat JenisJadwal::untukStatus().
 * - SEMENTARA : tanggal tidak boleh lampau (di JadwalRequest); maksimal satu
 *               jadwal TERJADWAL per pengajuan.
 * - MITRA     : jam operasional, hari libur, dan bentrok slot dari config/jnet.php,
 *               hanya aktif jika nilainya sudah diisi.
 */
class JadwalService
{
    /**
     * aturJadwal() pada Class Diagram: buat jadwal baru.
     *
     * @param  array{tanggal: string, waktu_mulai: string, catatan?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function buat(RegistrasiPelanggan $registrasi, JenisJadwal $jenis, array $data, Admin $admin): JadwalLayanan
    {
        return DB::transaction(function () use ($registrasi, $jenis, $data, $admin) {
            $terkini = RegistrasiPelanggan::query()->lockForUpdate()->findOrFail($registrasi->id_registrasi);

            // "Memenuhi syarat?" pada Activity Diagram.
            if (JenisJadwal::untukStatus($terkini->status_pengajuan) !== $jenis) {
                throw ValidationException::withMessages([
                    'jadwal' => "Penjadwalan {$this->kecil($jenis)} ditunda: pengajuan harus berstatus "
                        .'"'.StatusPengajuan::Diverifikasi->label()."\", sedangkan status saat ini \"{$terkini->status_pengajuan->label()}\".",
                ]);
            }

            $adaJadwalAktif = $terkini->jadwal()
                ->where('status_jadwal', StatusJadwal::Terjadwal->value)
                ->exists();

            if ($adaJadwalAktif) {
                throw ValidationException::withMessages([
                    'jadwal' => 'Pengajuan ini sudah memiliki jadwal kunjungan yang aktif. Ubah jadwal tersebut jika perlu.',
                ]);
            }

            $this->periksaAturanMitra($data['tanggal'], $data['waktu_mulai']);

            $jadwal = new JadwalLayanan([
                'tanggal' => $data['tanggal'],
                'waktu_mulai' => $data['waktu_mulai'],
                'catatan' => $data['catatan'] ?? null,
            ]);
            $jadwal->id_registrasi = $terkini->id_registrasi;
            $jadwal->id_admin = $admin->id_admin;
            $jadwal->jenis_jadwal = $jenis;
            $jadwal->status_jadwal = StatusJadwal::Terjadwal;
            $jadwal->save();

            // "Perbarui status pengajuan" (Revisi 1): otomatis dalam transaksi yang sama.
            $terkini->status_pengajuan = $jenis->statusSetelahDijadwalkan();
            $terkini->updated_by_admin_id = $admin->id_admin;
            $terkini->save();

            return $jadwal;
        });
    }

    /**
     * Ubah tanggal, jam, atau catatan jadwal yang masih TERJADWAL.
     * Jenis jadwal tidak dapat diubah.
     *
     * @param  array{tanggal: string, waktu_mulai: string, catatan?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function ubah(JadwalLayanan $jadwal, array $data, Admin $admin): JadwalLayanan
    {
        return DB::transaction(function () use ($jadwal, $data, $admin) {
            $terkini = JadwalLayanan::query()->lockForUpdate()->findOrFail($jadwal->id_jadwal);

            if (! $terkini->status_jadwal->isAktif()) {
                throw ValidationException::withMessages([
                    'jadwal' => "Jadwal berstatus \"{$terkini->status_jadwal->label()}\" tidak dapat diubah.",
                ]);
            }

            $this->periksaAturanMitra($data['tanggal'], $data['waktu_mulai'], $terkini->id_jadwal);

            $terkini->fill([
                'tanggal' => $data['tanggal'],
                'waktu_mulai' => $data['waktu_mulai'],
                'catatan' => $data['catatan'] ?? null,
            ]);
            $terkini->id_admin = $admin->id_admin;
            $terkini->save();

            return $terkini;
        });
    }

    /**
     * Aturan yang menunggu keputusan mitra. Tidak aktif selama nilainya
     * di config/jnet.php masih null, array kosong, atau false.
     *
     * @throws ValidationException
     */
    private function periksaAturanMitra(string $tanggal, string $waktuMulai, ?int $abaikanIdJadwal = null): void
    {
        $aturan = config('jnet.jadwal', []);
        $jam = substr($waktuMulai, 0, 5);

        $mulai = $aturan['jam_operasional_mulai'] ?? null;
        $selesai = $aturan['jam_operasional_selesai'] ?? null;

        if ($mulai && $selesai && ($jam < $mulai || $jam > $selesai)) {
            throw ValidationException::withMessages([
                'waktu_mulai' => "Jam mulai harus di dalam jam operasional {$mulai}–{$selesai}.",
            ]);
        }

        $hariLibur = $aturan['hari_libur'] ?? [];

        if ($hariLibur && in_array(Carbon::parse($tanggal)->dayOfWeekIso, $hariLibur, true)) {
            throw ValidationException::withMessages([
                'tanggal' => 'Tanggal yang dipilih adalah hari libur layanan. Pilih tanggal lain.',
            ]);
        }

        if (! empty($aturan['cegah_bentrok_slot'])) {
            $bentrok = JadwalLayanan::query()
                ->whereDate('tanggal', $tanggal)
                ->where('waktu_mulai', 'like', $jam.'%')
                ->where('status_jadwal', StatusJadwal::Terjadwal->value)
                ->when($abaikanIdJadwal, fn ($query) => $query->whereKeyNot($abaikanIdJadwal))
                ->exists();

            if ($bentrok) {
                throw ValidationException::withMessages([
                    'waktu_mulai' => 'Sudah ada jadwal lain pada tanggal dan jam yang sama. Pilih jam lain.',
                ]);
            }
        }
    }

    private function kecil(JenisJadwal $jenis): string
    {
        return mb_strtolower($jenis->label());
    }
}
