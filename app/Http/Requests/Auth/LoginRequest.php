<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Dasar login untuk pelanggan dan admin.
 *
 * - Pesan gagal dibuat umum ("... atau kata sandi salah") agar tidak
 *   membocorkan apakah sebuah akun terdaftar.
 * - Kata sandi hanya dicocokkan dengan hash; tidak pernah dicatat ke log.
 * - Maksimal 5 percobaan gagal per akun dan alamat IP; setelah itu
 *   login dikunci sementara.
 * - Revisi mitra: captcha Turnstile (lihat App\Support\Captcha::aturan()) di aturan
 *   validasi turunan kelas ini, sehingga bot tidak dapat mencoba kata sandi berulang kali.
 */
abstract class LoginRequest extends FormRequest
{
    protected const MAKS_PERCOBAAN = 5;

    /**
     * Nama guard: 'web' atau 'admin'.
     */
    abstract protected function guard(): string;

    /**
     * Nama field identitas di form dan di tabel: 'email' atau 'username'.
     */
    abstract protected function fieldIdentitas(): string;

    /**
     * Pesan jika login gagal. Sengaja tidak menyebut bagian mana yang salah.
     */
    abstract protected function pesanGagal(): string;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Coba login. Melempar ValidationException jika gagal atau terkunci.
     */
    public function authenticate(): void
    {
        $this->pastikanTidakTerkunci();

        $kredensial = [
            $this->fieldIdentitas() => $this->input($this->fieldIdentitas()),
            'password' => $this->input('password'),
        ];

        if (! Auth::guard($this->guard())->attempt($kredensial)) {
            RateLimiter::hit($this->kunciPembatas());

            throw ValidationException::withMessages([
                $this->fieldIdentitas() => $this->pesanGagal(),
            ]);
        }

        RateLimiter::clear($this->kunciPembatas());
    }

    protected function pastikanTidakTerkunci(): void
    {
        if (! RateLimiter::tooManyAttempts($this->kunciPembatas(), self::MAKS_PERCOBAAN)) {
            return;
        }

        event(new Lockout($this));

        $detik = RateLimiter::availableIn($this->kunciPembatas());

        throw ValidationException::withMessages([
            $this->fieldIdentitas() => trans('auth.throttle', [
                'seconds' => $detik,
                'minutes' => (int) ceil($detik / 60),
            ]),
        ]);
    }

    protected function kunciPembatas(): string
    {
        return $this->guard().'|'.Str::transliterate(Str::lower((string) $this->input($this->fieldIdentitas()))).'|'.$this->ip();
    }
}
