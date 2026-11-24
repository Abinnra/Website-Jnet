<?php

namespace App\Http\Requests\Admin;

use App\Support\Format;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi Use Case: Mengelola Informasi Website.
 * Aturan setiap kunci diambil dari config/jnet.php bagian 'site_settings'.
 */
class InformasiWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (config('jnet.site_settings', []) as $kunci => $definisi) {
            $nilai = trim((string) $this->input($kunci));

            // 0812-3456-7890 atau +62 812... disimpan sebagai 6281234567890.
            if ($definisi['tipe'] === 'whatsapp' && $nilai !== '') {
                $nilai = Format::nomorWhatsapp($nilai) ?? $nilai;
            }

            if ($definisi['tipe'] === 'email') {
                $nilai = mb_strtolower($nilai);
            }

            $data[$kunci] = $nilai;
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return collect(config('jnet.site_settings', []))
            ->map(fn (array $definisi) => $definisi['aturan'])
            ->all();
    }

    public function attributes(): array
    {
        return collect(config('jnet.site_settings', []))
            ->map(fn (array $definisi) => mb_strtolower($definisi['label']))
            ->all();
    }

    public function messages(): array
    {
        $pesan = [];

        foreach (config('jnet.site_settings', []) as $kunci => $definisi) {
            if ($definisi['tipe'] === 'telepon') {
                $pesan[$kunci.'.regex'] = 'Nomor telepon hanya boleh berisi angka, spasi, tanda kurung, +, dan -.';
            }

            if ($definisi['tipe'] === 'whatsapp') {
                $pesan[$kunci.'.regex'] = 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.';
            }
        }

        return $pesan;
    }
}
