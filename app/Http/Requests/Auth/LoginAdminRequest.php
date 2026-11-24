<?php

namespace App\Http\Requests\Auth;

use App\Support\Captcha;

/**
 * Login admin dengan username dan kata sandi (guard: admin).
 */
class LoginAdminRequest extends LoginRequest
{
    protected function guard(): string
    {
        return 'admin';
    }

    protected function fieldIdentitas(): string
    {
        return 'username';
    }

    protected function pesanGagal(): string
    {
        return 'Username atau kata sandi salah.';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => trim((string) $this->input('username')),
        ]);
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
            ...Captcha::aturan(),
        ];
    }
}
