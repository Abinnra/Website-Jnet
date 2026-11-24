<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginAdminRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Use Case: Login dan Logout admin (guard terpisah: admin).
 */
class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.masuk-admin');
    }

    public function store(LoginAdminRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Halaman masuk berupa Blade (bukan halaman Vue), jadi permintaan dari panel Vue dibuka
     * dengan muat ulang penuh lewat Inertia::location (permintaan biasa tetap redirect 302).
     */
    public function destroy(Request $request): SymfonyResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('sukses', 'Anda telah keluar dari panel admin.');

        return Inertia::location(route('admin.login'));
    }
}
