<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KeputusanHasil;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HasilLayananRequest;
use App\Models\HasilLayanan;
use App\Models\JadwalLayanan;
use App\Services\HasilLayananService;
use App\Support\Tampilan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Use Case: Mencatat Hasil Layanan. Revisi mitra (tanpa survei): hasil kunjungan
 * pemasangan hanya "Pemasangan berhasil" atau "Tidak layak dipasang".
 */
class HasilLayananController extends Controller
{
    public function __construct(private HasilLayananService $hasilService) {}

    /**
     * Revisi (Vue): formulir dirender Vue (Pages/Admin/Hasil/Form.vue).
     */
    public function create(JadwalLayanan $jadwal): Response|RedirectResponse
    {
        try {
            $this->hasilService->pastikanDapatDicatat($jadwal);
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin.pengajuan.show', $jadwal->id_registrasi)
                ->with('gagal', $e->errors()['hasil'][0]);
        }

        $jadwal->load('registrasi');
        $pengajuan = $jadwal->registrasi;
        $unggahan = config('jnet.unggahan.dokumentasi');

        return Inertia::render('Admin/Hasil/Form', [
            'judul' => 'Catat hasil pemasangan',
            'keterangan' => "{$pengajuan->nomor_pengajuan} – {$pengajuan->nama_pelanggan}. Kunjungan "
                .$jadwal->tanggal->translatedFormat('l, d F Y').', pukul '.Tampilan::jam($jadwal->waktu_mulai).' WIB.',
            'pengajuan' => ['nomor' => $pengajuan->nomor_pengajuan, 'tautan' => route('admin.pengajuan.show', $pengajuan)],
            'pilihanKeputusan' => array_map(fn (KeputusanHasil $keputusan) => [
                'nilai' => $keputusan->value,
                'label' => $keputusan->label(),
                'keterangan' => "Status pengajuan menjadi \"{$keputusan->statusPengajuanBerikutnya()->label()}\"."
                    .($keputusan->wajibUraian() ? ' Tulis alasannya di uraian hasil.' : ''),
            ], KeputusanHasil::pilihan()),
            // Pesan galat "hasil" yang menyangkut pilihan keputusan tampil di bawah pilihan; selain itu di atas formulir.
            'galatPilihan' => ['Pilih hasil pemasangan.', 'Pilih "Pemasangan berhasil" atau "Tidak layak dipasang".'],
            'unggahan' => [
                'accept' => collect($unggahan['mimes'])->map(fn (string $ekstensi) => '.'.$ekstensi)->implode(','),
                'maksMb' => $unggahan['maks_kb'] / 1024,
            ],
            'tautanSimpan' => route('admin.hasil.store', $jadwal),
        ]);
    }

    public function store(HasilLayananRequest $request, JadwalLayanan $jadwal): RedirectResponse
    {
        $keputusan = $request->keputusan();

        $this->hasilService->simpan(
            $jadwal,
            $keputusan,
            $request->validated('catatan'),
            $request->file('dokumentasi'),
            $request->user('admin'),
            $request->validated('pesan_untuk_pelanggan'),
        );

        return redirect()
            ->route('admin.pengajuan.show', $jadwal->id_registrasi)
            ->with('sukses', "Hasil pemasangan tersimpan: {$keputusan->label()}. "
                ."Status pengajuan menjadi \"{$keputusan->statusPengajuanBerikutnya()->label()}\".");
    }

    /**
     * Tampilkan file dokumentasi dari disk privat. Hanya dapat diakses
     * melalui route admin ini, tidak ada URL publik ke file tersebut.
     */
    public function dokumentasi(HasilLayanan $hasil): StreamedResponse
    {
        $disk = Storage::disk($this->hasilService->disk());
        $path = $hasil->dokumentasi_url;

        abort_unless($path && $disk->exists($path), 404);

        $hasil->loadMissing('jadwal.registrasi');
        $ekstensi = pathinfo($path, PATHINFO_EXTENSION);
        $namaFile = 'dokumentasi-'.$hasil->jadwal->registrasi->nomor_pengajuan.'-'.$hasil->jadwal->jenis_jadwal->slug().'.'.$ekstensi;

        return $disk->response($path, $namaFile, [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'",
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
