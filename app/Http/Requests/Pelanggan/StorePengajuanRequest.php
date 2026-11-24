<?php

namespace App\Http\Requests\Pelanggan;

use App\Support\Format;
use App\Support\Wilayah;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi server untuk Use Case: Mengajukan Berlangganan.
 *
 * Hanya field di bawah yang diterima. Field lain yang mungkin dikirim
 * (misalnya id_user, harga, status) diabaikan.
 *
 * Revisi 2: wilayah "probolinggo" memilih kecamatan dari daftar; wilayah "luar"
 * mengetik kabupaten/kota dan kecamatan, dengan titik lokasi wajib diisi.
 */
class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    protected function prepareForValidation(): void
    {
        $nomorWa = $this->input('no_whatsapp');

        $this->merge([
            // Tanpa pilihan (misalnya form lama) dianggap Kota/Kabupaten Probolinggo.
            'wilayah' => $this->input('wilayah', 'probolinggo'),
            'kota_kabupaten' => trim((string) $this->input('kota_kabupaten')),
            'kecamatan_luar' => trim((string) $this->input('kecamatan_luar')),
            'nama_pelanggan' => trim((string) $this->input('nama_pelanggan')),
            // 0812-3456-7890 / +62 812... -> 6281234567890; jika tidak valid dibiarkan agar ditolak.
            'no_whatsapp' => Format::nomorWhatsapp($nomorWa) ?? trim((string) $nomorWa),
            'alamat_pemasangan' => trim((string) $this->input('alamat_pemasangan')),
            'catatan' => trim((string) $this->input('catatan')) ?: null,
            'latitude' => $this->filled('latitude') ? $this->input('latitude') : null,
            'longitude' => $this->filled('longitude') ? $this->input('longitude') : null,
        ]);
    }

    public function rules(): array
    {
        $daftarKecamatan = Wilayah::semuaKecamatan();

        return [
            'id_paket' => [
                'required', 'integer',
                Rule::exists('paket_internet', 'id_paket')->where('status_aktif', true),
            ],
            'nama_pelanggan' => ['required', 'string', 'min:3', 'max:100'],
            'no_whatsapp' => ['required', 'string', 'regex:/^628\d{7,11}$/'],
            'alamat_pemasangan' => ['required', 'string', 'min:10', 'max:1000'],
            'wilayah' => ['required', Rule::in(['probolinggo', 'luar'])],
            'kecamatan' => ['exclude_unless:wilayah,probolinggo', 'required', 'string', Rule::in($daftarKecamatan)],
            'kota_kabupaten' => [
                'exclude_unless:wilayah,luar', 'required', 'string', 'min:3', 'max:100',
                function (string $atribut, mixed $nilai, Closure $gagal) {
                    if (Wilayah::menyebutWilayahUtama((string) $nilai)) {
                        $gagal('Untuk wilayah Probolinggo, pilih "Kota/Kabupaten Probolinggo" lalu pilih kecamatan dari daftar.');
                    }
                },
            ],
            'kecamatan_luar' => ['exclude_unless:wilayah,luar', 'required', 'string', 'min:3', 'max:100'],
            'latitude' => ['nullable', 'required_if:wilayah,luar', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_if:wilayah,luar', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_paket.required' => 'Pilih salah satu paket.',
            'id_paket.exists' => 'Paket yang dipilih tidak tersedia. Silakan pilih paket lain.',
            'no_whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.',
            'kecamatan.in' => 'Pilih kecamatan dari daftar.',
            'kecamatan.required' => 'Pilih kecamatan dari daftar.',
            'wilayah.in' => 'Pilih wilayah lokasi pemasangan.',
            'kota_kabupaten.required' => 'Tulis nama kabupaten atau kota, misalnya Kabupaten Lumajang.',
            'kecamatan_luar.required' => 'Tulis nama kecamatan lokasi pemasangan.',
            'latitude.required_if' => 'Untuk lokasi di luar Probolinggo, tandai titik lokasi di peta agar admin dapat menilai jangkauannya.',
            'longitude.required_if' => 'Untuk lokasi di luar Probolinggo, tandai titik lokasi di peta agar admin dapat menilai jangkauannya.',
            'alamat_pemasangan.min' => 'Tulis alamat pemasangan lebih lengkap (minimal 10 karakter).',
            'latitude.required_with' => 'Titik lokasi tidak lengkap. Pilih ulang titik di peta atau hapus titiknya.',
            'longitude.required_with' => 'Titik lokasi tidak lengkap. Pilih ulang titik di peta atau hapus titiknya.',
            'latitude.between' => 'Titik lokasi tidak valid. Pilih ulang titik di peta.',
            'longitude.between' => 'Titik lokasi tidak valid. Pilih ulang titik di peta.',
        ];
    }

    public function attributes(): array
    {
        return [
            'kota_kabupaten' => 'kabupaten/kota',
            'kecamatan_luar' => 'kecamatan',
        ];
    }

    /**
     * Data siap simpan: kecamatan dan kota_kabupaten sesuai wilayah yang dipilih.
     *
     * @return array<string, mixed>
     */
    public function dataPengajuan(): array
    {
        $data = $this->validated();

        if ($data['wilayah'] === 'luar') {
            $data['kecamatan'] = $data['kecamatan_luar'];
        } else {
            $data['kota_kabupaten'] = Wilayah::kotaKabupatenUntuk($data['kecamatan']);
        }

        unset($data['wilayah'], $data['kecamatan_luar']);

        return $data;
    }
}
