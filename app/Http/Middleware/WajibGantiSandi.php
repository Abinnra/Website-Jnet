<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revisi 5: pelanggan yang kata sandinya baru diatur ulang admin harus membuat
 * kata sandi baru dulu sebelum dapat membuka halaman akun lainnya.
 */
class WajibGantiSandi
{
    private const BOLEH = ['akun.sandi.edit', 'akun.sandi.update', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if ($user?->wajib_ganti_sandi && ! $request->routeIs(...self::BOLEH)) {
            return redirect()->route('akun.sandi.edit');
        }

        return $next($request);
    }
}
