<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Pesan dari admin yang tampil di halaman detail pengajuan pelanggan.
 * Dikosongkan berarti pesan dihapus dari halaman pelanggan.
 */
class PesanPelangganRequest extends FormRequest
{
    public const MAKS = 1000;

    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'pesan_untuk_pelanggan' => trim((string) $this->input('pesan_untuk_pelanggan')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'pesan_untuk_pelanggan' => ['nullable', 'string', 'max:'.self::MAKS],
        ];
    }

    public function attributes(): array
    {
        return ['pesan_untuk_pelanggan' => 'pesan untuk pelanggan'];
    }
}
