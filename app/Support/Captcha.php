<?php

namespace App\Support;

use App\Rules\LolosCaptcha;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Captcha Cloudflare Turnstile (revisi mitra: mencegah brute force login dan pendaftaran spam).
 *
 * - Kunci diisi lewat .env (TURNSTILE_SITE_KEY dan TURNSTILE_SECRET_KEY).
 * - Di luar produksi (lokal), jika kunci kosong dipakai kunci uji resmi Cloudflare yang selalu lolos.
 * - Di produksi, jika kunci belum diisi, captcha tidak aktif dan dashboard admin menampilkan pengingat.
 * - CAPTCHA_AKTIF=false mematikan captcha (misalnya jika hosting tidak dapat menghubungi Cloudflare).
 */
class Captcha
{
    /** Nama isian token yang dikirim widget Turnstile. */
    public const NAMA_ISIAN = 'cf-turnstile-response';

    public const ALAMAT_VERIFIKASI = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public const ALAMAT_SKRIP = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

    /** Kunci uji resmi Cloudflare: widget tampil dan selalu lolos (hanya untuk lokal). */
    public const KUNCI_SITUS_UJI = '1x00000000000000000000AA';

    public const KUNCI_RAHASIA_UJI = '1x0000000000000000000000000000000AA';

    public static function aktif(): bool
    {
        return (bool) config('services.turnstile.aktif') && self::kunciSitus() !== null && self::kunciRahasia() !== null;
    }

    public static function kunciSitus(): ?string
    {
        return self::kunci('site_key', self::KUNCI_SITUS_UJI);
    }

    public static function kunciRahasia(): ?string
    {
        return self::kunci('secret_key', self::KUNCI_RAHASIA_UJI);
    }

    /**
     * True jika captcha diinginkan tetapi kunci produksi belum diisi (untuk pengingat di dashboard).
     */
    public static function kunciProduksiBelumDiisi(): bool
    {
        return (bool) config('services.turnstile.aktif') && app()->isProduction() && ! self::aktif();
    }

    /**
     * Aturan validasi untuk form yang dilindungi captcha (kosong jika captcha tidak aktif).
     *
     * @return array<string, list<mixed>>
     */
    public static function aturan(): array
    {
        return self::aktif()
            ? [self::NAMA_ISIAN => ['bail', 'required', 'string', 'max:2048', new LolosCaptcha]]
            : [];
    }

    /**
     * Tanyakan ke Cloudflare apakah token dari browser sah. Gagal terhubung dianggap tidak lolos.
     */
    public static function verifikasi(string $token, ?string $ip): bool
    {
        try {
            $respons = Http::asForm()->timeout(8)->post(self::ALAMAT_VERIFIKASI, [
                'secret' => self::kunciRahasia(),
                'response' => $token,
                'remoteip' => $ip,
            ]);
        } catch (ConnectionException $e) {
            Log::warning('Captcha: tidak dapat menghubungi Cloudflare Turnstile.', ['pesan' => $e->getMessage()]);

            return false;
        }

        return $respons->successful() && $respons->json('success') === true;
    }

    private static function kunci(string $nama, string $kunciUji): ?string
    {
        $kunci = trim((string) config('services.turnstile.'.$nama));

        if ($kunci !== '') {
            return $kunci;
        }

        return app()->isProduction() ? null : $kunciUji;
    }
}
