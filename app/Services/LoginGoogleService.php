<?php

namespace App\Services;

use App\Exceptions\LoginGoogleDitolak;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as AkunGoogle;

/**
 * Revisi mitra: menentukan akun JNET untuk pelanggan yang masuk dengan Google.
 *
 * Urutan pencarian:
 * 1. Akun yang sudah terhubung dengan akun Google ini (google_id sama).
 * 2. Akun yang terdaftar dengan email yang sama (misalnya dulu mendaftar dengan kata sandi):
 *    dihubungkan dengan akun Google ini. Aman karena Google sudah memverifikasi email tersebut.
 * 3. Jika belum ada, dibuat akun baru tanpa kata sandi.
 *
 * Kata sandi sementara dari admin (wajib_ganti_sandi) dinonaktifkan saat pelanggan masuk dengan
 * Google. Tanpa ini pelanggan terus diarahkan ke halaman "buat kata sandi" (WajibGantiSandi)
 * walaupun ia masuk dengan Google.
 */
class LoginGoogleService
{
    public function __construct(private KataSandiService $kataSandi) {}

    /**
     * @return array{0: User, 1: bool, 2: bool} akun pelanggan, apakah akun itu baru dibuat, dan apakah
     *                                          kata sandi sementara dari admin baru saja dinonaktifkan
     *
     * @throws LoginGoogleDitolak
     */
    public function akunUntuk(AkunGoogle $akunGoogle): array
    {
        $idGoogle = trim((string) $akunGoogle->getId());
        $email = mb_strtolower(trim((string) $akunGoogle->getEmail()));

        // Hanya email yang sudah diverifikasi Google yang boleh dipakai, agar akun JNET
        // milik orang lain tidak dapat diambil alih lewat akun Google dengan email palsu.
        $emailTerverifikasi = filter_var($akunGoogle->getRaw()['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($idGoogle === '' || $email === '' || ! $emailTerverifikasi) {
            throw new LoginGoogleDitolak('Email akun Google Anda belum terverifikasi oleh Google. Silakan masuk dengan email dan kata sandi.');
        }

        [$user, $akunBaru] = DB::transaction(function () use ($idGoogle, $email) {
            $user = User::query()->where('google_id', $idGoogle)->first();

            if ($user) {
                return [$user, false];
            }

            $user = User::query()->where('email', $email)->lockForUpdate()->first();

            if ($user) {
                if ($user->terhubungGoogle()) {
                    throw new LoginGoogleDitolak('Email ini sudah terhubung dengan akun Google lain. Silakan masuk dengan email dan kata sandi.');
                }

                $user->forceFill(['google_id' => $idGoogle])->save();

                return [$user, false];
            }

            $user = new User(['email' => $email]);
            $user->forceFill(['google_id' => $idGoogle])->save();

            return [$user, true];
        });

        $sandiSementaraDinonaktifkan = (bool) $user->wajib_ganti_sandi;

        if ($sandiSementaraDinonaktifkan) {
            $this->kataSandi->nonaktifkanSandiSementara($user);
        }

        return [$user, $akunBaru, $sandiSementaraDinonaktifkan];
    }
}
