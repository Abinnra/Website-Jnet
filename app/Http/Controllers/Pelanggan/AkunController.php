<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\RegistrasiPelanggan;
use App\Support\Tampilan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman awal pelanggan setelah login.
 *
 * Hanya menampilkan pengajuan milik akun yang sedang login (query selalu
 * dimulai dari relasi user->registrasi, bukan dari ID di URL).
 * Revisi (Vue): halaman dirender Vue (Pages/Akun/Index.vue).
 */
class AkunController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user('web');

        return Inertia::render('Akun/Index', [
            'email' => $user->email,
            // Akun yang dibuat lewat Google belum punya kata sandi: tautannya "Buat kata sandi".
            'labelSandi' => $user->punyaKataSandi() ? 'Ubah kata sandi' : 'Buat kata sandi',
            'tautanSandi' => route('akun.sandi.edit'),
            'tautanAjukan' => route('pengajuan.create'),
            'pengajuan' => $user->registrasi()
                ->with('paket')
                ->latest('created_at')
                ->latest('id_registrasi')
                ->get()
                ->map(fn (RegistrasiPelanggan $item) => [
                    'nomor' => $item->nomor_pengajuan_pelanggan,
                    'tautan' => route('pengajuan.show', $item->id_registrasi),
                    'ringkas' => "{$item->paket->nama_paket} – {$item->lokasi}",
                    'diajukan' => 'Diajukan '.Tampilan::tanggal($item->created_at),
                    'adaPesan' => filled($item->pesan_untuk_pelanggan),
                    'status' => Tampilan::lencana($item->status_pengajuan),
                ])->all(),
        ]);
    }
}
