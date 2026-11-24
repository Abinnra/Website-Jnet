<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrasiAkunRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Use Case: Registrasi Akun (Class Diagram: User.register()).
 */
class RegistrasiAkunController extends Controller
{
    public function create(): View
    {
        return view('auth.daftar');
    }

    public function store(RegistrasiAkunRequest $request): RedirectResponse
    {
        $user = User::create([
            'email' => $request->validated('email'),
            'password_hash' => $request->validated('password'), // di-hash otomatis oleh cast 'hashed'
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('akun')
            ->with('sukses', 'Akun berhasil dibuat. Selamat datang di JNET.');
    }
}
