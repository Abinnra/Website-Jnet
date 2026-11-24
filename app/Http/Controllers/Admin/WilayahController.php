<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KecamatanRequest;
use App\Http\Requests\Admin\KotaKabupatenRequest;
use App\Models\Kecamatan;
use App\Models\KotaKabupaten;
use App\Models\RegistrasiPelanggan;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\Support\Header as InertiaHeader;

/**
 * Revisi mitra: Mengelola Wilayah Layanan (kota/kabupaten dan kecamatan).
 *
 * Wilayah di sini tampil di halaman Jangkauan dan menjadi daftar pilihan kecamatan
 * pada formulir pengajuan. Pengajuan dari kota/kabupaten lain ditandai "luar wilayah".
 */
class WilayahController extends Controller
{
    public function __construct(private WilayahService $wilayahService) {}

    /**
     * Revisi (Vue): halaman dirender Vue (Pages/Admin/Wilayah/Index.vue); setiap kartu kota punya
     * formulir sendiri, sehingga pesan galat tampil di formulir yang dikirim.
     */
    public function index(): Response
    {
        $daftarKota = KotaKabupaten::query()
            ->with(['kecamatan' => fn ($query) => $query->orderBy('nama')])
            ->orderBy('id_kota_kabupaten')
            ->get();

        // Jumlah pengajuan per kota/kabupaten, untuk mencegah penghapusan yang membuat data lama "luar wilayah".
        $pengajuanPerKota = RegistrasiPelanggan::query()
            ->whereIn('kota_kabupaten', $daftarKota->pluck('nama'))
            ->selectRaw('kota_kabupaten, COUNT(*) AS jumlah')
            ->groupBy('kota_kabupaten')
            ->pluck('jumlah', 'kota_kabupaten');

        return Inertia::render('Admin/Wilayah/Index', [
            'kota' => $daftarKota->map(function (KotaKabupaten $kota) use ($pengajuanPerKota) {
                $jumlahPengajuan = (int) ($pengajuanPerKota[$kota->nama] ?? 0);

                return [
                    'id' => $kota->id_kota_kabupaten,
                    'nama' => $kota->nama,
                    'jumlahPengajuan' => $jumlahPengajuan,
                    'kecamatan' => $kota->kecamatan->map(fn (Kecamatan $kecamatan) => [
                        'id' => $kecamatan->id_kecamatan,
                        'nama' => $kecamatan->nama,
                        'tautanUbah' => route('admin.wilayah.kecamatan.update', $kecamatan),
                        'tautanHapus' => route('admin.wilayah.kecamatan.destroy', $kecamatan),
                    ])->all(),
                    'tautanTambahKecamatan' => route('admin.wilayah.kecamatan.store', $kota),
                    'tautanUbah' => route('admin.wilayah.kota.update', $kota),
                    // Kota/kabupaten yang sudah dipakai pengajuan tidak dapat dihapus.
                    'tautanHapus' => $jumlahPengajuan > 0 ? null : route('admin.wilayah.kota.destroy', $kota),
                    'labelHapus' => 'Hapus '.$kota->nama.($kota->kecamatan->isNotEmpty() ? ' beserta '.$kota->kecamatan->count().' kecamatannya' : ''),
                ];
            })->all(),
            'tautanTambahKota' => route('admin.wilayah.kota.store'),
            'tautanJangkauan' => route('jangkauan'),
        ]);
    }

    public function simpanKota(KotaKabupatenRequest $request): RedirectResponse
    {
        $kota = KotaKabupaten::create(['nama' => $request->validated('nama_kota')]);

        return $this->kembali($kota, "{$kota->nama} ditambahkan. Tambahkan kecamatannya agar dapat dipilih pelanggan.");
    }

    public function ubahKota(KotaKabupatenRequest $request, KotaKabupaten $kota): RedirectResponse
    {
        $namaLama = $kota->nama;
        $jumlahPengajuan = $this->wilayahService->ubahNamaKota($kota, $request->validated('nama_kota'));

        if ($namaLama === $kota->nama) {
            return $this->kembali($kota, 'Tidak ada perubahan yang disimpan.');
        }

        return $this->kembali($kota, "{$namaLama} diganti menjadi {$kota->nama}".$this->keteranganPengajuan($jumlahPengajuan));
    }

    /**
     * Kota/kabupaten yang sudah dipakai pengajuan tidak dapat dihapus, agar pengajuan
     * lamanya tidak berubah menjadi "luar wilayah". Kecamatannya ikut terhapus.
     */
    public function hapusKota(KotaKabupaten $kota): RedirectResponse
    {
        $jumlahPengajuan = $kota->jumlahPengajuan();

        if ($jumlahPengajuan > 0) {
            return $this->kembali($kota, "{$kota->nama} tidak dapat dihapus karena sudah dipakai {$jumlahPengajuan} pengajuan. Ganti namanya jika perlu.", 'gagal');
        }

        $kota->delete();

        return redirect()->route('admin.wilayah.index')
            ->with('sukses', "{$kota->nama} beserta kecamatannya dihapus dari wilayah layanan.");
    }

    public function simpanKecamatan(KecamatanRequest $request, KotaKabupaten $kota): RedirectResponse
    {
        $kecamatan = $kota->kecamatan()->create(['nama' => $request->validated('nama_kecamatan')]);

        // Kembali ke formulir tambah, agar admin dapat langsung menambahkan kecamatan berikutnya.
        return $this->kembali($kota, "Kecamatan {$kecamatan->nama} ditambahkan ke {$kota->nama}.", tempat: 'tambah-kecamatan-'.$kota->id_kota_kabupaten);
    }

    public function ubahKecamatan(KecamatanRequest $request, Kecamatan $kecamatan): RedirectResponse
    {
        $namaLama = $kecamatan->nama;
        $jumlahPengajuan = $this->wilayahService->ubahNamaKecamatan($kecamatan, $request->validated('nama_kecamatan'));

        if ($namaLama === $kecamatan->nama) {
            return $this->kembali($kecamatan->kotaKabupaten, 'Tidak ada perubahan yang disimpan.');
        }

        return $this->kembali(
            $kecamatan->kotaKabupaten,
            "Kecamatan {$namaLama} diganti menjadi {$kecamatan->nama}".$this->keteranganPengajuan($jumlahPengajuan),
        );
    }

    /**
     * Pengajuan lama tetap menyimpan nama kecamatannya; kecamatan hanya tidak dapat dipilih lagi.
     */
    public function hapusKecamatan(Kecamatan $kecamatan): RedirectResponse
    {
        $kota = $kecamatan->kotaKabupaten;
        $kecamatan->delete();

        return $this->kembali($kota, "Kecamatan {$kecamatan->nama} dihapus dan tidak dapat dipilih lagi pada pengajuan baru.");
    }

    /**
     * Kembali ke bagian halaman yang baru diubah (bawaan: kartu kota/kabupatennya);
     * pesannya tampil di bagian itu, bukan di atas halaman.
     */
    private function kembali(KotaKabupaten $kota, string $pesan, string $jenis = 'sukses', ?string $tempat = null): RedirectResponse
    {
        $tempat ??= 'kota-'.$kota->id_kota_kabupaten;

        // Dari halaman Vue (Inertia) posisi gulir tetap di tempat; tanpa JavaScript kembali ke bagian yang diubah.
        $alamat = route('admin.wilayah.index').(request()->hasHeader(InertiaHeader::INERTIA) ? '' : '#'.$tempat);

        return redirect()
            ->to($alamat)
            ->with('pesanWilayah', ['tempat' => $tempat, 'jenis' => $jenis, 'teks' => $pesan]);
    }

    private function keteranganPengajuan(int $jumlah): string
    {
        return $jumlah > 0 ? ". {$jumlah} pengajuan lama ikut diperbarui." : '.';
    }
}
