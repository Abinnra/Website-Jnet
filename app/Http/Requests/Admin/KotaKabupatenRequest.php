<?php

namespace App\Http\Requests\Admin;

use App\Models\KotaKabupaten;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Inertia\Support\Header as InertiaHeader;

/**
 * Validasi tambah/ubah nama kota/kabupaten wilayah utama (revisi mitra: wilayah dapat diubah admin).
 */
class KotaKabupatenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    protected function prepareForValidation(): void
    {
        // Spasi ganda dirapikan: "Kabupaten   Lumajang " -> "Kabupaten Lumajang".
        $this->merge([
            'nama_kota' => trim((string) preg_replace('/\s+/u', ' ', (string) $this->input('nama_kota'))),
        ]);
    }

    public function rules(): array
    {
        /** @var KotaKabupaten|null $kotaDiubah */
        $kotaDiubah = $this->route('kota');

        return [
            'nama_kota' => [
                'required', 'string', 'min:3', 'max:100',
                // Pengecekan tanpa membedakan huruf besar/kecil, sama di MariaDB dan SQLite.
                function (string $atribut, mixed $nilai, Closure $gagal) use ($kotaDiubah) {
                    $sudahAda = KotaKabupaten::query()
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower((string) $nilai)])
                        ->when($kotaDiubah, fn ($query) => $query->whereKeyNot($kotaDiubah->getKey()))
                        ->exists();

                    if ($sudahAda) {
                        $gagal("{$nilai} sudah ada di daftar wilayah.");
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return ['nama_kota' => 'nama kota/kabupaten'];
    }

    /**
     * Saat gagal, halaman kembali ke formulir yang diisi (bukan ke atas halaman).
     */
    protected function getRedirectUrl(): string
    {
        // Revisi (Vue): dari halaman Vue (Inertia) pesan tampil di formulir yang dikirim, tanpa melompat.
        if ($this->hasHeader(InertiaHeader::INERTIA)) {
            return route('admin.wilayah.index');
        }

        $kota = $this->route('kota');

        return route('admin.wilayah.index').'#'.($kota instanceof KotaKabupaten ? 'ubah-kota-'.$kota->id_kota_kabupaten : 'tambah-kota');
    }
}
