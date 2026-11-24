<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan dasar untuk setiap respons, termasuk halaman error.
 *
 * - nosniff: browser tidak menebak jenis file (penting untuk file unggahan).
 * - X-Frame-Options DENY: website tidak dapat dimuat di dalam iframe situs lain (clickjacking).
 * - Referrer-Policy: situs lain hanya menerima nama domain, bukan alamat lengkap halaman.
 *   Ubin peta OpenStreetMap tetap menerima Referer sesuai kebijakan penggunaannya.
 * - Permissions-Policy: kamera, mikrofon, dan pembayaran dimatikan; lokasi hanya untuk website ini.
 * - HSTS: hanya dikirim saat produksi dan diakses lewat HTTPS.
 * - Content-Security-Policy (revisi mitra, perlindungan XSS): skrip hanya boleh berasal dari
 *   website ini, skrip inline yang membawa nonce acak per permintaan ($cspNonce di view),
 *   Leaflet (unpkg), dan captcha Cloudflare Turnstile. Skrip sisipan tanpa nonce diblokir browser.
 */
class HeaderKeamanan
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nonce acak untuk <script> inline milik layout (lihat atribut nonce="{{ $cspNonce }}").
        // Didaftarkan juga ke Vite agar skrip yang disisipkan paket Laravel (misalnya alat
        // pengembangan Boost di lokal) ikut membawa nonce yang sama.
        $nonce = Vite::useCspNonce(base64_encode(random_bytes(18)));
        View::share('cspNonce', $nonce);

        $response = $next($request);

        $header = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), payment=(), geolocation=(self)',
            'Content-Security-Policy' => self::kebijakanKonten($nonce, $request->isSecure(), self::asalViteDev()),
        ];

        if ($request->isSecure() && app()->isProduction()) {
            $header['Strict-Transport-Security'] = 'max-age=31536000';
        }

        // Respons yang sudah menentukan header sendiri (misalnya file dokumentasi) tidak ditimpa.
        foreach ($header as $nama => $nilai) {
            if (! $response->headers->has($nama)) {
                $response->headers->set($nama, $nilai);
            }
        }

        return $response;
    }

    /**
     * Daftar sumber yang diizinkan. style-src memakai 'unsafe-inline' karena atribut style
     * (misalnya panjang batang kecepatan) dipakai di markup; skrip tetap dikunci dengan nonce.
     */
    public static function kebijakanKonten(string $nonce, bool $https, ?string $viteDev = null): string
    {
        // Saat pengembangan (npm run dev), aplikasi Vue dimuat dari server Vite beserta koneksi HMR (ws://).
        $dev = $viteDev !== null ? ' '.$viteDev : '';
        $devWs = $viteDev !== null ? ' '.preg_replace('#^http#', 'ws', $viteDev) : '';

        $aturan = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'{$dev} https://unpkg.com https://challenges.cloudflare.com",
            "style-src 'self' 'unsafe-inline'{$dev} https://unpkg.com",
            "img-src 'self' data: blob:{$dev} https://tile.openstreetmap.org https://unpkg.com",
            "font-src 'self'{$dev}",
            "connect-src 'self'{$dev}{$devWs}",
            "media-src 'self'",
            'frame-src https://challenges.cloudflare.com',
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ];

        // Di hosting HTTPS, alamat http:// yang tersisa otomatis dimuat lewat https://.
        if ($https) {
            $aturan[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $aturan);
    }

    /**
     * Revisi (Vue): origin server pengembangan Vite (misalnya "http://127.0.0.1:5173") selama
     * "npm run dev" berjalan di luar produksi; null jika tidak. vite.config.js memakai host IPv4,
     * karena CSP tidak dapat menuliskan alamat IPv6 seperti [::1].
     */
    private static function asalViteDev(): ?string
    {
        if (app()->isProduction() || ! Vite::isRunningHot()) {
            return null;
        }

        $alamat = parse_url(trim((string) @file_get_contents(Vite::hotFile())));

        if (! isset($alamat['scheme'], $alamat['host'])) {
            return null;
        }

        return $alamat['scheme'].'://'.$alamat['host'].(isset($alamat['port']) ? ':'.$alamat['port'] : '');
    }
}
