<?php

namespace App\Http\Requests\Admin;

use App\Models\Kecamatan;
use App\Models\KotaKabupaten;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Inertia\Support\Header as InertiaHeader;

/**
 * Validasi tambah/ubah nama kecamatan wilayah utama (revisi mitra: wilayah dapat diubah admin).
 *
 * Nama kecamatan harus unik di seluruh wilayah, karena kota/kabupaten sebuah
 * pengajuan ditentukan dari kecamatan yang dipilih pelanggan.
 */
class KecamatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_kecamatan' => trim((string) preg_replace('/\s+/u', ' ', (string) $this->input('nama_kecamatan'))),
        ]);
    }

    public function rules(): array
    {
        /** @var Kecamatan|null $kecamatanDiubah */
        $kecamatanDiubah = $this->route('kecamatan');

        return [
            'nama_kecamatan' => [
                'required', 'string', 'min:3', 'max:100',
                function (string $atribut, mixed $nilai, Closure $gagal) use ($kecamatanDiubah) {
                    $sudahAda = Kecamatan::query()
                        ->with('kotaKabupaten')
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower((string) $nilai)])
                        ->when($kecamatanDiubah, fn ($query) => $query->whereKeyNot($kecamatanDiubah->getKey()))
                        ->first();

                    if ($sudahAda) {
                        $gagal("Kecamatan {$sudahAda->nama} sudah terdaftar di {$sudahAda->kotaKabupaten->nama}.");
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return ['nama_kecamatan' => 'nama kecamatan'];
    }

    /**
     * Saat gagal, halaman kembali tepat ke formulir yang diisi (tambah kecamatan atau
     * kecamatan yang diubah), sehingga pesan kesalahannya langsung terlihat.
     */
    protected function getRedirectUrl(): string
    {
        // Revisi (Vue): dari halaman Vue (Inertia) pesan tampil di formulir yang dikirim, tanpa melompat.
        if ($this->hasHeader(InertiaHeader::INERTIA)) {
            return route('admin.wilayah.index');
        }

        $kota = $this->route('kota');
        $kecamatan = $this->route('kecamatan');

        $tempat = match (true) {
            $kota instanceof KotaKabupaten => '#tambah-kecamatan-'.$kota->id_kota_kabupaten,
            $kecamatan instanceof Kecamatan => '#kecamatan-'.$kecamatan->id_kecamatan,
            default => '',
        };

        return route('admin.wilayah.index').$tempat;
    }
}
