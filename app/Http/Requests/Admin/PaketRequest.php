<?php

namespace App\Http\Requests\Admin;

use App\Models\PaketInternet;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah dan ubah paket internet (Use Case: Mengelola Paket Internet).
 */
class PaketRequest extends FormRequest
{
    public const MAKS_KEUNGGULAN = 3;

    public const MAKS_PANJANG_KEUNGGULAN = 60;

    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    /**
     * Admin boleh menulis harga dengan format Indonesia, misalnya
     * "125.000" atau "Rp125.000". Nilainya diubah menjadi angka biasa
     * sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_paket' => trim((string) $this->input('nama_paket')),
            'harga' => self::normalisasiRupiah($this->input('harga')),
            'biaya_pemasangan' => self::normalisasiRupiah($this->input('biaya_pemasangan')),
            'status_aktif' => $this->boolean('status_aktif'),
            'label' => trim((string) $this->input('label')) ?: null,
            'keunggulan' => self::normalisasiKeunggulan($this->input('keunggulan')),
        ]);
    }

    public function rules(): array
    {
        /** @var PaketInternet|null $paket */
        $paket = $this->route('paket');

        return [
            'nama_paket' => [
                'required', 'string', 'max:100',
                Rule::unique('paket_internet', 'nama_paket')->ignore($paket?->id_paket, 'id_paket'),
            ],
            'kecepatan_mbps' => ['required', 'integer', 'min:1', 'max:10000'],
            'harga' => ['required', 'numeric', 'min:0', 'max:99999999', 'decimal:0,2'],
            'biaya_pemasangan' => ['required', 'numeric', 'min:0', 'max:99999999', 'decimal:0,2'],
            'status_aktif' => ['boolean'],
            'label' => ['nullable', 'string', 'max:30'],
            'keunggulan' => [
                'nullable', 'string',
                function (string $atribut, mixed $nilai, Closure $gagal) {
                    $baris = explode("\n", (string) $nilai);

                    if (count($baris) > self::MAKS_KEUNGGULAN) {
                        $gagal('Keunggulan maksimal '.self::MAKS_KEUNGGULAN.' baris.');
                    } elseif (collect($baris)->contains(fn (string $teks) => mb_strlen($teks) > self::MAKS_PANJANG_KEUNGGULAN)) {
                        $gagal('Setiap baris keunggulan maksimal '.self::MAKS_PANJANG_KEUNGGULAN.' karakter.');
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return ['label' => 'label kartu'];
    }

    /**
     * Rapikan isian keunggulan: satu keunggulan per baris, baris kosong dibuang,
     * spasi di sekitar tanda | diseragamkan. Null jika kosong.
     */
    public static function normalisasiKeunggulan(mixed $nilai): ?string
    {
        if (! is_string($nilai)) {
            return null;
        }

        $baris = collect(preg_split('/\R/', $nilai) ?: [])
            ->map(fn (string $teks) => implode(' | ', array_map('trim', explode('|', trim($teks), 2))))
            ->map(fn (string $teks) => trim($teks, ' |'))
            ->filter(fn (string $teks) => $teks !== '')
            ->values();

        return $baris->isEmpty() ? null : $baris->implode("\n");
    }

    public function messages(): array
    {
        return [
            'nama_paket.unique' => 'Nama paket sudah dipakai paket lain.',
            'harga.numeric' => 'Harga harus berupa angka, contoh: 125000 atau 125.000.',
            'biaya_pemasangan.numeric' => 'Biaya pemasangan harus berupa angka. Isi 0 jika gratis.',
        ];
    }

    /**
     * "Rp 125.000" -> "125000", "125.000,50" -> "125000.50", "125000" -> "125000".
     * Nilai yang tidak dikenali dikembalikan apa adanya agar ditolak validasi.
     */
    public static function normalisasiRupiah(mixed $nilai): mixed
    {
        if (! is_string($nilai) && ! is_numeric($nilai)) {
            return $nilai;
        }

        $teks = preg_replace('/^rp\.?/i', '', str_replace(' ', '', trim((string) $nilai)));

        if (str_contains($teks, ',')) {
            // Format Indonesia: titik pemisah ribuan, koma desimal.
            return str_replace(['.', ','], ['', '.'], $teks);
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $teks)) {
            return str_replace('.', '', $teks);
        }

        return $teks;
    }
}
