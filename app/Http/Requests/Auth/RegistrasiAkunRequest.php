<?php

namespace App\Http\Requests\Auth;

use App\Support\Captcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Registrasi akun pelanggan (Use Case: Registrasi Akun).
 * Sesuai ERD, akun hanya berisi email dan kata sandi.
 */
class RegistrasiAkunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // Revisi mitra: kata sandi tidak lagi diminta dua kali (formulir memiliki tombol lihat kata sandi).
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            // Revisi mitra: captcha mencegah pendaftaran akun spam oleh bot.
            ...Captcha::aturan(),
        ];
    }
}
