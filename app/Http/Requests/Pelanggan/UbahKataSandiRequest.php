<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Revisi 5 (UML): Use Case "Mengubah Kata Sandi".
 * Aturan kata sandi baru sama dengan saat registrasi akun.
 *
 * Revisi mitra: akun yang dibuat lewat Google belum punya kata sandi, sehingga kata sandi
 * pertamanya dibuat tanpa isian kata sandi lama (pelanggan sudah masuk lewat Google).
 */
class UbahKataSandiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        if (! $this->user('web')->punyaKataSandi()) {
            return [
                'kata_sandi_lama' => ['exclude'],
                'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
            ];
        }

        return [
            'kata_sandi_lama' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'different:kata_sandi_lama', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'kata_sandi_lama.required' => 'Kata sandi saat ini wajib diisi.',
            'kata_sandi_lama.current_password' => 'Kata sandi saat ini salah.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
            'password.confirmed' => 'Ulangi kata sandi baru tidak cocok.',
        ];
    }

    public function attributes(): array
    {
        return [
            'kata_sandi_lama' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ];
    }
}
