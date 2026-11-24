<?php

namespace App\Support;

use Illuminate\Foundation\ViteException;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;

/**
 * "Pulau" Vue di halaman Blade publik (revisi: tampilan memakai Vue), misalnya ubin aksi cepat beranda.
 *
 * HTML-nya tetap dirender server, sehingga SEO dan pengunjung tanpa JavaScript tidak terpengaruh; Vue hanya
 * menambahkan interaksi di atasnya. Karena itu, bila aset Vite belum dibangun (npm run build) atau entrinya
 * belum ada di manifest, tag skripnya dilewati alih-alih menggagalkan seluruh halaman.
 */
class PulauVue
{
    /**
     * Tag <script> (dan modulepreload) untuk entri Vite, atau kosong bila asetnya belum tersedia.
     */
    public static function skrip(string $entri): HtmlString
    {
        try {
            return Vite::__invoke($entri);
        } catch (ViteException) {
            // Tidak dicatat ke log agar tidak berulang di setiap kunjungan; panel admin (Vue penuh) akan
            // langsung menunjukkan galat manifest bila build memang belum diunggah.
            return new HtmlString('');
        }
    }
}
