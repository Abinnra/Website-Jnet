<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginPelangganRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Use Case: Login dan Logout pelanggan (Class Diagram: User.login(), User.logout()).
 */
class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.masuk');
    }

    public function store(LoginPelangganRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('akun'));
    }

    /**
     * Beranda berupa Blade (bukan halaman Vue), jadi permintaan dari halaman akun Vue dibuka
     * dengan muat ulang penuh lewat Inertia::location (permintaan biasa tetap redirect 302).
     */
    public function destroy(Request $request): SymfonyResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('sukses', 'Anda telah keluar.');

        return Inertia::location(route('beranda'));
    }
}
