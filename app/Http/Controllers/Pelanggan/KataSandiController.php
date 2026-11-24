<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pelanggan\UbahKataSandiRequest;
use App\Services\KataSandiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Revisi 5 (UML): Use Case "Mengubah Kata Sandi" (Class Diagram: User.ubahKataSandi()).
 */
class KataSandiController extends Controller
{
    /**
     * Revisi mitra: akun yang dibuat lewat Google belum punya kata sandi, jadi halaman ini menjadi
     * "Buat kata sandi" tanpa isian kata sandi lama. Revisi (Vue): Pages/Akun/KataSandi.vue.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user('web');

        return Inertia::render('Akun/KataSandi', [
            'email' => $user->email,
            'punyaSandi' => $user->punyaKataSandi(),
            'wajibGanti' => (bool) $user->wajib_ganti_sandi,
            'terhubungGoogle' => $user->terhubungGoogle(),
            'tautanSimpan' => route('akun.sandi.update'),
            'tautanKembali' => route('akun'),
        ]);
    }

    public function update(UbahKataSandiRequest $request, KataSandiService $service): RedirectResponse
    {
        $user = $request->user('web');
        $kataSandiPertama = ! $user->punyaKataSandi(); // akun Google yang baru membuat kata sandi

        $request->session()->regenerate();

        $service->ubah(
            $user,
            $request->validated('password'),
            $request->session()->getId(),
        );

        return redirect()->route('akun')->with('sukses', $kataSandiPertama
            ? 'Kata sandi berhasil dibuat. Anda kini juga dapat masuk dengan email dan kata sandi.'
            : 'Kata sandi berhasil diubah. Gunakan kata sandi baru saat masuk berikutnya.');
    }
}
