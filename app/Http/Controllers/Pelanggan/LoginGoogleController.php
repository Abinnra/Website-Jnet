<?php

namespace App\Http\Controllers\Pelanggan;

use App\Exceptions\LoginGoogleDitolak;
use App\Http\Controllers\Controller;
use App\Services\LoginGoogleService;
use App\Support\LoginGoogle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as RedirectKeLuar;

/**
 * Revisi mitra: Login dan Registrasi Akun pelanggan dengan akun Google (Laravel Socialite).
 *
 * Alur: tombol "Masuk dengan Google" -> arahkan() mengirim pelanggan ke halaman Google ->
 * pelanggan memilih akun -> Google mengembalikan pelanggan ke kembali() -> akun JNET
 * dicari atau dibuat (LoginGoogleService) -> pelanggan masuk.
 */
class LoginGoogleController extends Controller
{
    public function arahkan(): RedirectKeLuar
    {
        abort_unless(LoginGoogle::aktif(), 404);

        // select_account: pelanggan dengan beberapa akun Google (atau perangkat bersama) dapat memilih akunnya.
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function kembali(Request $request, LoginGoogleService $loginGoogle): RedirectResponse
    {
        abort_unless(LoginGoogle::aktif(), 404);

        // Pelanggan menekan "Batal" di halaman Google.
        if ($request->filled('error')) {
            return $this->gagal('Masuk dengan Google dibatalkan.');
        }

        try {
            $akunGoogle = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            // Kode "state" tidak cocok dengan sesi: halaman dibuka ulang atau sesi sudah habis.
            return $this->gagal('Sesi masuk dengan Google sudah habis. Silakan coba lagi.');
        } catch (GuzzleException $e) {
            report($e);

            return $this->gagal('Google tidak dapat dihubungi saat ini. Silakan coba lagi atau masuk dengan email dan kata sandi.');
        }

        try {
            [$user, $akunBaru, $sandiSementaraDinonaktifkan] = $loginGoogle->akunUntuk($akunGoogle);
        } catch (LoginGoogleDitolak $e) {
            return $this->gagal($e->getMessage());
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('akun'))->with('sukses', match (true) {
            $akunBaru => 'Akun berhasil dibuat dengan Google. Selamat datang di JNET.',
            $sandiSementaraDinonaktifkan => 'Berhasil masuk dengan Google. Kata sandi sementara dari admin tidak berlaku lagi; buat kata sandi baru di menu Kata sandi bila ingin masuk dengan email.',
            default => 'Berhasil masuk dengan Google.',
        });
    }

    private function gagal(string $pesan): RedirectResponse
    {
        return redirect()->route('login')->with('gagal', $pesan);
    }
}
