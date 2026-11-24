<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AturUlangSandiRequest;
use App\Models\User;
use App\Services\KataSandiService;
use App\Support\Format;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Revisi 5 (UML): Use Case "Mengatur Ulang Kata Sandi Pelanggan"
 * (Class Diagram: Admin.aturUlangSandiPelanggan()).
 * Revisi (Vue): halaman dirender Vue (Pages/Admin/SandiPelanggan).
 */
class SandiPelangganController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Admin/SandiPelanggan/Buat', [
            'email' => (string) $request->query('email', ''),
            'tautanSimpan' => route('admin.sandi-pelanggan.store'),
        ]);
    }

    /**
     * Hasil ditampilkan langsung (tanpa redirect) agar kata sandi sementara tidak pernah disimpan
     * ke sesi atau database dalam bentuk asli. Riwayat browser untuk halaman ini dienkripsi Inertia
     * (di koneksi aman), dan halaman Vue menghapus nilainya dari riwayat begitu ditampilkan.
     */
    public function store(AturUlangSandiRequest $request, KataSandiService $service): SymfonyResponse
    {
        $user = User::where('email', $request->validated('email'))->firstOrFail();
        $sementara = $service->aturUlang($user);

        // Nomor WhatsApp diambil dari pengajuan terakhir pelanggan (jika ada).
        $pengajuan = $user->registrasi()->latest('created_at')->latest('id_registrasi')->first();
        $tautanWa = $pengajuan
            ? Format::tautanWhatsapp($pengajuan->no_whatsapp, "Halo {$pengajuan->nama_pelanggan}, kata sandi akun JNET Anda ({$user->email}) sudah diatur ulang.\n\nKata sandi sementara: {$sementara}\n\nSilakan masuk di ".route('login').' lalu buat kata sandi baru.')
            : null;

        Inertia::encryptHistory();

        $respons = Inertia::render('Admin/SandiPelanggan/Hasil', [
            'email' => $user->email,
            'sementara' => $sementara,
            'namaPelanggan' => $pengajuan?->nama_pelanggan,
            'tautanWa' => $tautanWa,
            'tautanLagi' => route('admin.sandi-pelanggan.create'),
        ])->toResponse($request);

        $respons->headers->set('Cache-Control', 'no-store, private');

        return $respons;
    }
}
