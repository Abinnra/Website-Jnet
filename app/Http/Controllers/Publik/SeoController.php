<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\KotaKabupaten;
use App\Models\PaketInternet;
use App\Models\SiteSetting;
use App\Services\SiteSettingService;
use Illuminate\Http\Response;

/**
 * Revisi mitra (SEO): peta situs dan aturan perayap untuk mesin pencari.
 * Alamat dibuat dari APP_URL/permintaan, sehingga otomatis benar saat di hosting.
 */
class SeoController extends Controller
{
    /**
     * sitemap.xml: semua halaman publik yang boleh muncul di hasil pencarian.
     * Halaman akun, admin, masuk, dan daftar tidak dimasukkan.
     */
    public function sitemap(SiteSettingService $situs): Response
    {
        $paketTerakhir = PaketInternet::query()->max('updated_at');
        $informasiTerakhir = SiteSetting::query()->max('updated_at');
        $terakhir = collect([$paketTerakhir, $informasiTerakhir])->filter()->max();
        // Halaman Jangkauan juga berubah saat admin mengubah daftar wilayah layanan.
        $jangkauanTerakhir = collect([
            $informasiTerakhir,
            KotaKabupaten::query()->max('updated_at'),
            Kecamatan::query()->max('updated_at'),
        ])->filter()->max();

        $halaman = [
            ['route' => 'beranda', 'prioritas' => '1.0', 'frekuensi' => 'weekly', 'diubah' => $terakhir],
            ['route' => 'paket', 'prioritas' => '0.9', 'frekuensi' => 'weekly', 'diubah' => $paketTerakhir],
            ['route' => 'jangkauan', 'prioritas' => '0.8', 'frekuensi' => 'monthly', 'diubah' => $jangkauanTerakhir],
            ['route' => 'alur', 'prioritas' => '0.7', 'frekuensi' => 'monthly', 'diubah' => null],
            ['route' => 'kemitraan', 'prioritas' => '0.6', 'frekuensi' => 'monthly', 'diubah' => $informasiTerakhir],
            ['route' => 'profil', 'prioritas' => '0.6', 'frekuensi' => 'monthly', 'diubah' => $informasiTerakhir],
            ['route' => 'kontak', 'prioritas' => '0.6', 'frekuensi' => 'monthly', 'diubah' => $informasiTerakhir],
        ];

        // Halaman Bayar Tagihan hanya berisi informasi jika admin sudah mengisinya.
        if ($situs->ada('info_pembayaran')) {
            $halaman[] = ['route' => 'bayar-tagihan', 'prioritas' => '0.5', 'frekuensi' => 'monthly', 'diubah' => $informasiTerakhir];
        }

        return response()
            ->view('seo.sitemap', ['halaman' => $halaman], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * robots.txt: area pribadi (admin, akun, pengajuan) tidak dijelajahi perayap.
     */
    public function robots(): Response
    {
        $isi = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /akun',
            'Disallow: /pengajuan',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($isi, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
