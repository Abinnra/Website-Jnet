<?php

namespace App\Http\Requests\Admin;

use App\Enums\KeputusanHasil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi pencatatan hasil kunjungan pemasangan: "Pemasangan berhasil" atau
 * "Tidak layak dipasang" (uraian wajib untuk tidak layak).
 */
class HasilLayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'catatan' => trim((string) $this->input('catatan')) ?: null,
            'pesan_untuk_pelanggan' => trim((string) $this->input('pesan_untuk_pelanggan')) ?: null,
        ]);
    }

    public function rules(): array
    {
        $unggahan = config('jnet.unggahan.dokumentasi');
        $pilihan = KeputusanHasil::pilihan();
        $wajibUraian = array_filter($pilihan, fn (KeputusanHasil $keputusan) => $keputusan->wajibUraian());

        return [
            'hasil' => ['required', Rule::in(array_map(fn (KeputusanHasil $keputusan) => $keputusan->value, $pilihan))],
            'catatan' => [
                'nullable',
                'required_if:hasil,'.implode(',', array_map(fn (KeputusanHasil $keputusan) => $keputusan->value, $wajibUraian)),
                'string',
                'max:2000',
            ],
            'pesan_untuk_pelanggan' => ['nullable', 'string', 'max:'.PesanPelangganRequest::MAKS],
            'dokumentasi' => [
                'nullable',
                'file',
                // mimes memeriksa ISI file, extensions memeriksa nama file; keduanya harus cocok.
                'mimes:'.implode(',', $unggahan['mimes']),
                'extensions:'.implode(',', $unggahan['mimes']),
                'max:'.$unggahan['maks_kb'],
            ],
        ];
    }

    public function messages(): array
    {
        $unggahan = config('jnet.unggahan.dokumentasi');
        $tipe = strtoupper(implode(', ', array_unique(array_map(fn ($ekstensi) => $ekstensi === 'jpeg' ? 'jpg' : $ekstensi, $unggahan['mimes']))));

        return [
            'hasil.required' => 'Pilih hasil pemasangan.',
            'hasil.in' => 'Pilih "Pemasangan berhasil" atau "Tidak layak dipasang".',
            'catatan.required_if' => 'Tulis alasan lokasi tidak layak dipasang.',
            'dokumentasi.mimes' => "Dokumentasi harus berupa file {$tipe}.",
            'dokumentasi.extensions' => "Dokumentasi harus berupa file {$tipe}.",
            'dokumentasi.max' => 'Ukuran dokumentasi maksimal '.($unggahan['maks_kb'] / 1024).' MB.',
        ];
    }

    public function keputusan(): KeputusanHasil
    {
        return KeputusanHasil::from($this->validated('hasil'));
    }
}
