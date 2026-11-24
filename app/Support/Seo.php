<?php

namespace App\Support;

use App\Models\PaketInternet;
use App\Services\SiteSettingService;

/**
 * Data SEO halaman publik (revisi mitra: website mudah ditemukan di mesin pencari).
 *
 * Menyediakan gambar pratinjau tautan (Open Graph) dan data terstruktur schema.org
 * (JSON-LD) yang dibaca Google. Isinya hanya data yang benar-benar ada di sistem:
 * informasi website yang diisi admin dan paket yang sedang aktif.
 */
class Seo
{
    /** Deskripsi halaman jika halaman tidak mengisi deskripsi sendiri. */
    public const DESKRIPSI_BAWAAN = 'Layanan internet rumah JNET dengan wilayah utama Kota dan Kabupaten Probolinggo. Pemasangan di luar Probolinggo juga dapat diajukan.';

    /** Gambar pratinjau khusus 1200x630 (rasio 1,91:1 yang dianjurkan Facebook, WhatsApp, dan X). */
    public const GAMBAR_BAGIKAN = 'images/og/og-jnet.jpg';

    /**
     * Gambar pratinjau saat tautan dibagikan (WhatsApp, Facebook, X, dan lain-lain):
     * gambar khusus 1200x630, foto banner pertama, atau logo (PNG) jika keduanya belum ada.
     *
     * @return array{url: string, lebar: ?int, tinggi: ?int, jenis: ?string}
     */
    public static function gambarBagikan(): array
    {
        $kandidat = [self::GAMBAR_BAGIKAN];

        foreach (config('banner.slide', []) as $slide) {
            $kandidat[] = $slide['gambar'] ?? null;
        }

        $kandidat[] = config('jnet.brand.logo_raster');

        foreach ($kandidat as $gambar) {
            if (is_string($gambar) && is_file(public_path($gambar))) {
                $info = @getimagesize(public_path($gambar)) ?: [null, null, 'mime' => null];

                return ['url' => asset($gambar), 'lebar' => $info[0], 'tinggi' => $info[1], 'jenis' => $info['mime'] ?? null];
            }
        }

        return ['url' => asset(config('jnet.brand.logo_raster')), 'lebar' => null, 'tinggi' => null, 'jenis' => null];
    }

    /**
     * Profil usaha JNET untuk Google (schema.org LocalBusiness, atau Organization
     * selama alamat kantor belum diisi) beserta paket aktif sebagai penawaran.
     *
     * @return array<string, mixed>
     */
    public static function dataTerstruktur(SiteSettingService $situs): array
    {
        $alamat = trim((string) preg_replace('/\s+/', ' ', $situs->get('alamat_kantor')));

        $data = [
            '@context' => 'https://schema.org',
            '@type' => $alamat !== '' ? 'LocalBusiness' : 'Organization',
            '@id' => url('/').'#organisasi',
            'name' => $situs->get('nama_perusahaan'),
            'alternateName' => config('jnet.brand.nama_singkat'),
            'url' => url('/'),
            // Google dan media sosial membaca logo raster (PNG); SVG dipakai untuk tampilan di website.
            'logo' => asset(config('jnet.brand.logo_raster')),
            'image' => self::gambarBagikan()['url'],
            'description' => $situs->get('deskripsi_usaha') ?: self::DESKRIPSI_BAWAAN,
            'areaServed' => [
                ['@type' => 'City', 'name' => 'Kota Probolinggo'],
                ['@type' => 'AdministrativeArea', 'name' => 'Kabupaten Probolinggo'],
            ],
        ];

        if ($alamat !== '') {
            $data['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $alamat, 'addressCountry' => 'ID'];
        }

        if ($situs->ada('telepon')) {
            $data['telephone'] = $situs->get('telepon');
        }

        if ($situs->ada('email')) {
            $data['email'] = $situs->get('email');
        }

        if ($nomorWa = Format::nomorWhatsapp($situs->get('whatsapp'))) {
            $data['contactPoint'] = [
                '@type' => 'ContactPoint',
                'telephone' => '+'.$nomorWa,
                'contactType' => 'customer service',
                'availableLanguage' => 'Indonesian',
            ];
        }

        $paket = PaketInternet::aktif()->orderBy('kecepatan_mbps')->get();

        if ($paket->isNotEmpty()) {
            $data['makesOffer'] = $paket->map(fn (PaketInternet $item) => [
                '@type' => 'Offer',
                'name' => $item->nama_paket,
                'price' => number_format((float) $item->harga, 0, '.', ''),
                'priceCurrency' => 'IDR',
                'url' => route('paket').'#paket-'.$item->id_paket,
                'itemOffered' => [
                    '@type' => 'Service',
                    'name' => $item->nama_paket,
                    'serviceType' => 'Internet rumah '.$item->kecepatan_mbps.' Mbps',
                ],
            ])->all();
        }

        return $data;
    }

    /**
     * JSON untuk tag <script type="application/ld+json">. Karakter < dan > di-escape
     * agar isi dari admin tidak dapat menutup tag script.
     *
     * @param  array<string, mixed>  $data
     */
    public static function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
    }
}
