<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusPengajuan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Perubahan status manual oleh admin. Kesesuaian transisinya diperiksa
 * oleh PengajuanService::ubahStatus().
 */
class UbahStatusPengajuanRequest extends FormRequest
{
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
            'status' => ['required', Rule::enum(StatusPengajuan::class)],
            'pesan_untuk_pelanggan' => ['nullable', 'string', 'max:'.PesanPelangganRequest::MAKS],
        ];
    }

    public function attributes(): array
    {
        return ['pesan_untuk_pelanggan' => 'pesan untuk pelanggan'];
    }

    public function statusTujuan(): StatusPengajuan
    {
        return StatusPengajuan::from($this->validated('status'));
    }
}
