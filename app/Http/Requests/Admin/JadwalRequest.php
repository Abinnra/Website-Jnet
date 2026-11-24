<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisJadwal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * "Validasi data jadwal" pada Activity Diagram Pengelolaan Jadwal.
 * Aturan yang bergantung pada status pengajuan dan keputusan mitra
 * diperiksa di JadwalService.
 */
class JadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'catatan' => trim((string) $this->input('catatan')) ?: null,
        ]);
    }

    public function rules(): array
    {
        $aturan = [
            // Aturan sementara yang disepakati: tanggal tidak boleh lampau.
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];

        // Saat membuat jadwal, jenis dikirim dari form untuk memastikan
        // form yang dibuka masih sesuai dengan status pengajuan terbaru.
        if ($this->isMethod('post')) {
            $aturan['jenis_jadwal'] = ['required', Rule::enum(JenisJadwal::class)];
        }

        return $aturan;
    }

    public function messages(): array
    {
        return [
            'tanggal.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
            'tanggal.date_format' => 'Format tanggal tidak valid.',
            'waktu_mulai.date_format' => 'Format jam tidak valid. Contoh: 09:00.',
        ];
    }

    /**
     * @return array{tanggal: string, waktu_mulai: string, catatan: string|null}
     */
    public function dataJadwal(): array
    {
        return $this->safe()->only(['tanggal', 'waktu_mulai', 'catatan']);
    }
}
