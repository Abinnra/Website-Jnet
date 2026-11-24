<?php

use App\Http\Middleware\HeaderKeamanan;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Support\Header as InertiaHeader;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Header keamanan dipasang global agar halaman error (misalnya 404) juga mendapatkannya.
        $middleware->append(HeaderKeamanan::class);

        // Belum login: halaman admin diarahkan ke login admin,
        // halaman pelanggan diarahkan ke login pelanggan.
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('admin', 'admin/*')
                ? route('admin.login')
                : route('login')
        );

        // Sudah login lalu membuka halaman masuk/daftar: arahkan ke area masing-masing.
        $middleware->redirectUsersTo(
            fn (Request $request) => $request->is('admin', 'admin/*')
                ? route('admin.dashboard')
                : route('akun')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // File unggahan melebihi post_max_size di php.ini: tampilkan pesan, bukan halaman error.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return back()->with('gagal', 'Data yang dikirim terlalu besar. Pastikan ukuran dokumentasi maksimal 5 MB, lalu coba lagi.');
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Revisi (Vue): sesi habis saat panel admin/akun (Vue) terbuka. Halaman masuk berupa Blade,
        // jadi dibuka dengan muat ulang penuh, bukan dimuat ke dalam aplikasi Vue.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->hasHeader(InertiaHeader::INERTIA)) {
                return Inertia::location($e->redirectTo($request) ?? route('login'));
            }

            return null;
        });

        // Token formulir kedaluwarsa (419) pada permintaan Vue: kembali ke halaman sebelumnya dengan pesan.
        $exceptions->respond(function (SymfonyResponse $response, Throwable $e, Request $request) {
            if ($response->getStatusCode() === 419 && $request->hasHeader(InertiaHeader::INERTIA)) {
                return back()->with('gagal', 'Halaman sudah terlalu lama dibuka. Silakan coba lagi.');
            }

            return $response;
        });
    })->create();
