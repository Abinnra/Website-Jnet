<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pelanggan\StorePengajuanRequest;
use App\Models\PaketInternet;
use App\Services\PengajuanService;
use App\Support\DetailPengajuanPelanggan;
use App\Support\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Use Case: Mengajukan Berlangganan dan Melihat Konfirmasi Pengajuan.
 * Berperan sebagai PengajuanController pada Sequence Diagram.
 */
class PengajuanController extends Controller
{
    public function __construct(private PengajuanService $pengajuanService) {}

    public function create(Request $request): View
    {
        $daftarPaket = PaketInternet::aktif()->orderBy('kecepatan_mbps')->get();

        // Paket terpilih dari tombol "Pilih paket" (?paket=ID), hanya jika aktif.
        $paketTerpilih = $daftarPaket->firstWhere('id_paket', (int) $request->query('paket'));

        return view('pengajuan.create', [
            'daftarPaket' => $daftarPaket,
            'idPaketTerpilih' => old('id_paket', $paketTerpilih?->id_paket),
            'wilayah' => Wilayah::daftar(),
        ]);
    }

    /**
     * submit(data_pengajuan) pada Sequence Diagram.
     */
    public function store(StorePengajuanRequest $request): RedirectResponse
    {
        $registrasi = $this->pengajuanService->submit($request->user('web'), $request->dataPengajuan());

        // Pola POST -> redirect -> GET: memuat ulang halaman tidak mengirim pengajuan dua kali.
        return redirect()
            ->route('pengajuan.show', $registrasi->id_registrasi)
            ->with('pengajuan_baru', true);
    }

    /**
     * Detail dan status satu pengajuan. Pencarian selalu dimulai dari
     * pengajuan milik pengguna yang login, sehingga pengajuan orang lain
     * menghasilkan 404 walaupun ID-nya diketik di URL.
     *
     * Revisi (Vue): Pages/Akun/Pengajuan.vue. Selama pengajuan masih berjalan, halaman Vue meminta
     * ulang prop "pengajuan" setiap menit (Inertia polling), sehingga perubahan dari admin langsung tampil.
     */
    public function show(Request $request, int $registrasi): Response
    {
        $pengajuan = $request->user('web')
            ->registrasi()
            ->with(['paket', 'jadwal' => fn ($query) => $query->orderBy('tanggal')->orderBy('waktu_mulai')])
            ->findOrFail($registrasi);

        return Inertia::render('Akun/Pengajuan', [
            'pengajuan' => DetailPengajuanPelanggan::data($pengajuan),
            'tautanKembali' => route('akun'),
        ]);
    }
}
