<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\SiteSetting;
use App\Support\Format;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Membaca informasi website (tabel site_settings) untuk ditampilkan.
 *
 * Hanya kunci yang terdaftar di config/jnet.php yang dikenali. Nilai
 * dibaca sekali per request lalu disimpan sementara di objek ini.
 */
class SiteSettingService
{
    /** @var array<string, string>|null */
    private ?array $nilai = null;

    /**
     * Semua informasi website dalam bentuk kunci => nilai.
     *
     * @return array<string, string>
     */
    public function semua(): array
    {
        if ($this->nilai !== null) {
            return $this->nilai;
        }

        $tersimpan = SiteSetting::query()->pluck('nilai_setting', 'nama_setting')->all();
        $hasil = [];

        foreach (config('jnet.site_settings', []) as $kunci => $definisi) {
            $hasil[$kunci] = trim((string) ($tersimpan[$kunci] ?? $definisi['default'] ?? ''));
        }

        return $this->nilai = $hasil;
    }

    /**
     * Nilai satu informasi; string kosong berarti belum tersedia.
     */
    public function get(string $kunci): string
    {
        return $this->semua()[$kunci] ?? '';
    }

    public function ada(string $kunci): bool
    {
        return $this->get($kunci) !== '';
    }

    /**
     * Tautan WhatsApp resmi, atau null jika nomor belum diisi.
     */
    public function tautanWhatsapp(string $pesan = ''): ?string
    {
        return Format::tautanWhatsapp($this->get('whatsapp'), $pesan);
    }

    /**
     * Tautan WhatsApp direktur untuk calon mitra bisnis (B2B), atau null jika
     * nomornya belum diisi.
     */
    public function tautanWhatsappDirektur(string $pesan = ''): ?string
    {
        return Format::tautanWhatsapp($this->get('whatsapp_direktur'), $pesan);
    }

    /**
     * Dipanggil setelah admin menyimpan perubahan agar nilai dibaca ulang.
     */
    public function lupakan(): void
    {
        $this->nilai = null;
    }

    /**
     * updateInformasi() pada Class Diagram.
     *
     * Hanya kunci yang terdaftar di config/jnet.php yang disimpan. Kunci
     * yang nilainya berubah dicatat dengan admin pengubahnya.
     *
     * @param  array<string, string|null>  $data  Data yang sudah divalidasi.
     * @return list<string> Kunci yang berubah.
     */
    public function simpan(array $data, Admin $admin): array
    {
        $berubah = DB::transaction(function () use ($data, $admin) {
            $berubah = [];

            foreach (array_keys(config('jnet.site_settings', [])) as $kunci) {
                if (! array_key_exists($kunci, $data)) {
                    continue;
                }

                $nilaiBaru = trim((string) $data[$kunci]);
                $setting = SiteSetting::firstOrNew(['nama_setting' => $kunci]);

                if ($setting->exists && $setting->nilai_setting === $nilaiBaru) {
                    continue;
                }

                $setting->nilai_setting = $nilaiBaru;
                $setting->id_admin = $admin->id_admin;
                $setting->save();

                $berubah[] = $kunci;
            }

            return $berubah;
        });

        $this->lupakan();

        return $berubah;
    }

    /**
     * Data per kunci untuk form admin, termasuk siapa dan kapan terakhir mengubah.
     *
     * @return Collection<string, SiteSetting>
     */
    public function rincian(): Collection
    {
        return SiteSetting::query()->with('admin')->get()->keyBy('nama_setting');
    }

    /**
     * Kunci kontak penting yang masih kosong (untuk pengingat di dashboard).
     *
     * @return list<string>
     */
    public function kontakBelumLengkap(): array
    {
        return array_values(array_filter(
            ['alamat_kantor', 'whatsapp', 'telepon', 'email', 'deskripsi_usaha'],
            fn (string $kunci) => ! $this->ada($kunci),
        ));
    }
}
