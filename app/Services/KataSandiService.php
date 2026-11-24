<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Revisi 5 (UML):
 * - Use Case "Mengubah Kata Sandi" (pelanggan)        -> ubah()
 * - Use Case "Mengatur Ulang Kata Sandi Pelanggan" (admin) -> aturUlang()
 * - Revisi mitra: kata sandi sementara gugur saat pelanggan masuk dengan Google -> nonaktifkanSandiSementara()
 *
 * Tidak memakai email atau layanan berbayar: kata sandi sementara ditampilkan
 * sekali kepada admin, lalu admin mengirimkannya ke pelanggan (misalnya lewat WhatsApp).
 */
class KataSandiService
{
    /** Huruf dan angka yang mudah dibaca (tanpa i, l, o, 0, 1). */
    private const HURUF = 'abcdefghjkmnpqrstuvwxyz';

    private const ANGKA = '23456789';

    public const PANJANG_SEMENTARA = 10;

    /**
     * Pelanggan mengganti kata sandinya sendiri. Sesi di perangkat lain ikut diakhiri.
     */
    public function ubah(User $user, string $kataSandiBaru, ?string $idSesiSekarang = null): void
    {
        DB::transaction(function () use ($user, $kataSandiBaru) {
            $user->forceFill([
                'password_hash' => $kataSandiBaru, // di-hash oleh cast 'hashed'
                'wajib_ganti_sandi' => false,
            ])->save();
        });

        $this->akhiriSesi($user, kecuali: $idSesiSekarang);
    }

    /**
     * Admin membuat kata sandi sementara untuk pelanggan yang lupa kata sandinya.
     * Semua sesi pelanggan diakhiri dan pelanggan wajib menggantinya saat masuk.
     *
     * @return string kata sandi sementara (hanya ditampilkan sekali, tidak disimpan dalam bentuk asli)
     */
    public function aturUlang(User $user): string
    {
        $sementara = $this->buatSandiSementara();

        DB::transaction(function () use ($user, $sementara) {
            $user->forceFill([
                'password_hash' => $sementara,
                'wajib_ganti_sandi' => true,
            ])->save();
        });

        $this->akhiriSesi($user);

        return $sementara;
    }

    /**
     * Pelanggan yang masih memegang kata sandi sementara dari admin masuk dengan Google.
     * Identitasnya sudah dibuktikan Google, jadi kata sandi sementara (yang juga diketahui admin)
     * dinonaktifkan dan pelanggan tidak lagi dipaksa membuat kata sandi. Ia tetap dapat membuat
     * kata sandi sendiri kapan saja di halaman Kata sandi, tanpa kata sandi lama.
     * Sesi lain ikut diakhiri, sama seperti saat kata sandi diganti.
     */
    public function nonaktifkanSandiSementara(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->forceFill([
                'password_hash' => null,
                'wajib_ganti_sandi' => false,
            ])->save();
        });

        $this->akhiriSesi($user);
    }

    public function buatSandiSementara(): string
    {
        $karakter = [
            self::HURUF[random_int(0, strlen(self::HURUF) - 1)],
            self::ANGKA[random_int(0, strlen(self::ANGKA) - 1)],
        ];

        $semua = self::HURUF.self::ANGKA;
        while (count($karakter) < self::PANJANG_SEMENTARA) {
            $karakter[] = $semua[random_int(0, strlen($semua) - 1)];
        }

        // Acak urutan dengan sumber acak yang aman.
        for ($i = count($karakter) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$karakter[$i], $karakter[$j]] = [$karakter[$j], $karakter[$i]];
        }

        return implode('', $karakter);
    }

    /**
     * Menghapus sesi login pelanggan yang tersimpan di database (SESSION_DRIVER=database).
     */
    private function akhiriSesi(User $user, ?string $kecuali = null): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->getKey())
            ->when($kecuali, fn ($query) => $query->where('id', '!=', $kecuali))
            ->delete();
    }
}
