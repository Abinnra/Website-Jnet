<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Revisi 5 (UML): Use Case "Mengatur Ulang Kata Sandi Pelanggan".
 */
class AturUlangSandiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
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
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email akun pelanggan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Tidak ada akun pelanggan dengan email ini.',
        ];
    }
}
