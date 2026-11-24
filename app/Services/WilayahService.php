<?php

namespace App\Services;

use App\Models\Kecamatan;
use App\Models\KotaKabupaten;
use App\Models\RegistrasiPelanggan;
use Illuminate\Support\Facades\DB;

/**
 * Membaca dan mengubah wilayah utama layanan (tabel kota_kabupaten dan kecamatan).
 *
 * Daftar dibaca sekali per request lalu disimpan sementara di objek ini, karena
 * dipakai berulang (misalnya penanda "luar wilayah" di setiap baris daftar pengajuan).
 */
class WilayahService
{
    /** @var array<string, list<string>>|null */
    private ?array $daftar = null;

    /**
     * Kota/kabupaten (urut sesuai waktu ditambahkan) beserta kecamatannya (urut abjad).
     * Contoh: ['Kota Probolinggo' => ['Kademangan', 'Kanigaran', ...], ...].
     *
     * @return array<string, list<string>>
     */
    public function daftar(): array
    {
        if ($this->daftar !== null) {
            return $this->daftar;
        }

        return $this->daftar = KotaKabupaten::query()
            ->with(['kecamatan' => fn ($query) => $query->orderBy('nama')])
            ->orderBy('id_kota_kabupaten')
            ->get()
            ->mapWithKeys(fn (KotaKabupaten $kota) => [$kota->nama => $kota->kecamatan->pluck('nama')->all()])
            ->all();
    }

    /**
     * Dipanggil setelah wilayah berubah agar daftar dibaca ulang.
     */
    public function lupakan(): void
    {
        $this->daftar = null;
    }

    /**
     * Mengganti nama kota/kabupaten. Pengajuan lama yang tercatat dengan nama lama ikut
     * diperbarui, agar tidak tiba-tiba dianggap luar wilayah.
     *
     * @return int Jumlah pengajuan yang ikut diperbarui.
     */
    public function ubahNamaKota(KotaKabupaten $kota, string $namaBaru): int
    {
        return DB::transaction(function () use ($kota, $namaBaru) {
            $namaLama = $kota->nama;
            $kota->update(['nama' => $namaBaru]);

            if ($namaLama === $namaBaru) {
                return 0;
            }

            return RegistrasiPelanggan::query()
                ->where('kota_kabupaten', $namaLama)
                ->toBase()
                ->update(['kota_kabupaten' => $namaBaru]);
        });
    }

    /**
     * Mengganti nama (misalnya membetulkan ejaan) kecamatan. Pengajuan lama di kecamatan
     * itu ikut diperbarui.
     *
     * @return int Jumlah pengajuan yang ikut diperbarui.
     */
    public function ubahNamaKecamatan(Kecamatan $kecamatan, string $namaBaru): int
    {
        return DB::transaction(function () use ($kecamatan, $namaBaru) {
            $namaLama = $kecamatan->nama;
            $kecamatan->update(['nama' => $namaBaru]);

            if ($namaLama === $namaBaru) {
                return 0;
            }

            return RegistrasiPelanggan::query()
                ->where('kecamatan', $namaLama)
                ->where('kota_kabupaten', $kecamatan->kotaKabupaten->nama)
                ->toBase()
                ->update(['kecamatan' => $namaBaru]);
        });
    }
}
