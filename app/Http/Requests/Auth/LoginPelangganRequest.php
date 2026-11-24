<?php

namespace App\Http\Requests\Auth;

use App\Support\Captcha;

/**
 * Login pelanggan dengan email dan kata sandi (guard: web).
 */
class LoginPelangganRequest extends LoginRequest
{
    protected function guard(): string
    {
        return 'web';
    }

    protected function fieldIdentitas(): string
    {
        return 'email';
    }

    protected function pesanGagal(): string
    {
        return 'Email atau kata sandi salah.';
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
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            ...Captcha::aturan(),
        ];
    }
}
