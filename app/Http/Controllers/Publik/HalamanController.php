<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\PaketInternet;
use App\Support\Banner;
use App\Support\Wilayah;
use Illuminate\View\View;

/**
 * Halaman publik untuk aktor Pengunjung (Use Case: Melihat Beranda,
 * Profil Perusahaan, Paket Internet, Wilayah Layanan, Alur Berlangganan,
 * dan Menghubungi via WhatsApp).
 */
class HalamanController extends Controller
{
    public function beranda(): View
    {
        $banner = Banner::slide();

        return view('publik.beranda', [
            'paket' => $this->paketAktif(),
            // Foto banner (config/banner.php); kosong = hero teks bawaan.
            'banner' => $banner,
            // Versi HP tegak: di HP banner tampil penuh di belakang navbar (gaya IndiHome).
            'bannerTegak' => Banner::versiHpTegak($banner),
        ]);
    }

    public function paket(): View
    {
        return view('publik.paket', [
            'paket' => $this->paketAktif(),
        ]);
    }

    public function jangkauan(): View
    {
        return view('publik.jangkauan', [
            'wilayah' => Wilayah::daftar(),
        ]);
    }

    public function alur(): View
    {
        return view('publik.alur');
    }

    public function profil(): View
    {
        return view('publik.profil');
    }

    public function kontak(): View
    {
        return view('publik.kontak');
    }

    /**
     * Kemitraan bisnis (B2B), revisi mitra: calon mitra menghubungi direktur
     * secara langsung. Kontak direktur diatur admin di menu Informasi Website.
     */
    public function kemitraan(): View
    {
        return view('publik.kemitraan');
    }

    /**
     * Informasi cara membayar tagihan (Revisi UI). Website tidak menerima
     * pembayaran online; isi halaman diatur admin di menu Informasi Website.
     */
    public function bayarTagihan(): View
    {
        return view('publik.bayar-tagihan');
    }

    /**
     * Hanya paket aktif yang ditampilkan ke publik, urut dari yang paling lambat.
     */
    private function paketAktif()
    {
        return PaketInternet::aktif()->orderBy('kecepatan_mbps')->get();
    }
}
