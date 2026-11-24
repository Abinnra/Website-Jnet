<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaketRequest;
use App\Models\PaketInternet;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Use Case: Mengelola Paket Internet.
 *
 * Paket tidak dapat dihapus, hanya dinonaktifkan, karena pengajuan lama
 * tetap merujuk ke paketnya (foreign key RESTRICT di database).
 * Revisi (Vue): halaman dirender Vue (Pages/Admin/Paket).
 */
class PaketController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Paket/Index', [
            'paket' => PaketInternet::query()
                ->withCount('registrasi')
                ->orderByDesc('status_aktif')
                ->orderBy('kecepatan_mbps')
                ->get()
                ->map(fn (PaketInternet $paket) => [
                    'id' => $paket->id_paket,
                    'nama' => $paket->nama_paket,
                    'label' => $paket->label,
                    'kecepatan' => $paket->kecepatan_mbps.' Mbps',
                    'harga' => Format::rupiah($paket->harga),
                    'biayaPasang' => (float) $paket->biaya_pemasangan > 0 ? Format::rupiah($paket->biaya_pemasangan) : 'Rp0',
                    'jumlahPengajuan' => $paket->registrasi_count,
                    'aktif' => (bool) $paket->status_aktif,
                    'tautanUbah' => route('admin.paket.edit', $paket),
                    'tautanStatus' => route('admin.paket.status', $paket),
                ])->all(),
            'catatanBiaya' => config('jnet.paket.tampilkan_biaya_pemasangan')
                ? null
                : 'Biaya pasang saat ini tidak ditampilkan di website (menunggu konfirmasi mitra).',
            'tautanTambah' => route('admin.paket.create'),
        ]);
    }

    public function create(): Response
    {
        return $this->formulir(new PaketInternet(['status_aktif' => true, 'biaya_pemasangan' => 0]), 0);
    }

    public function store(PaketRequest $request): RedirectResponse
    {
        $paket = PaketInternet::create($request->validated());

        return redirect()->route('admin.paket.index')
            ->with('sukses', "{$paket->nama_paket} berhasil ditambahkan.");
    }

    public function edit(PaketInternet $paket): Response
    {
        return $this->formulir($paket, $paket->registrasi()->count());
    }

    public function update(PaketRequest $request, PaketInternet $paket): RedirectResponse
    {
        $paket->update($request->validated());

        return redirect()->route('admin.paket.index')
            ->with('sukses', "Perubahan {$paket->nama_paket} berhasil disimpan.");
    }

    /**
     * Aktifkan atau nonaktifkan paket. Paket nonaktif tidak tampil di
     * halaman publik dan tidak dapat dipilih untuk pengajuan baru.
     */
    public function ubahStatus(PaketInternet $paket): RedirectResponse
    {
        $paket->update(['status_aktif' => ! $paket->status_aktif]);

        $keterangan = $paket->status_aktif
            ? 'diaktifkan dan kembali tampil di website'
            : 'dinonaktifkan dan tidak lagi tampil di website';

        return redirect()->route('admin.paket.index')
            ->with('sukses', "{$paket->nama_paket} {$keterangan}.");
    }

    /**
     * Formulir tambah/ubah paket (Pages/Admin/Paket/Form.vue).
     */
    private function formulir(PaketInternet $paket, int $jumlahPengajuan): Response
    {
        $modeUbah = $paket->exists;
        // Tampilkan 150000 (bukan 150000.00) agar mudah dibaca di formulir.
        $angka = fn ($nilai) => $nilai === null ? '' : (fmod((float) $nilai, 1) == 0 ? (string) (int) $nilai : (string) $nilai);

        return Inertia::render('Admin/Paket/Form', [
            'modeUbah' => $modeUbah,
            'judul' => $modeUbah ? 'Ubah '.$paket->nama_paket : 'Tambah paket',
            'jumlahPengajuan' => $jumlahPengajuan,
            'paket' => [
                'nama_paket' => $paket->nama_paket ?? '',
                'kecepatan_mbps' => $paket->kecepatan_mbps ?? '',
                'harga' => $angka($paket->harga),
                'biaya_pemasangan' => $angka($paket->biaya_pemasangan),
                'label' => $paket->label ?? '',
                'keunggulan' => $paket->keunggulan ?? '',
                'status_aktif' => (bool) $paket->status_aktif,
            ],
            'tautanSimpan' => $modeUbah ? route('admin.paket.update', $paket) : route('admin.paket.store'),
            'tautanKembali' => route('admin.paket.index'),
        ]);
    }
}
