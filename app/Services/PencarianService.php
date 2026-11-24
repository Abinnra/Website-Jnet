<?php

namespace App\Services;

use App\Models\PaketInternet;
use App\Support\Format;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Pencarian situs publik (revisi: kolom "Cari apa?" di navbar HP, seperti IndiHome).
 *
 * Mencari halaman publik, paket aktif, dan kecamatan wilayah layanan. Datanya sedikit, jadi cukup dicocokkan
 * langsung tanpa indeks tambahan: setiap kata yang diketik harus ada di judul, keterangan, atau kata kunci
 * (tanpa membedakan huruf besar-kecil).
 */
class PencarianService
{
    public function __construct(
        private SiteSettingService $situs,
        private WilayahService $wilayah,
    ) {}

    /**
     * @return array{halaman: list<array{judul: string, keterangan: string, tautan: string}>, paket: list<array{judul: string, keterangan: string, tautan: string}>, wilayah: list<array{judul: string, keterangan: string, tautan: string}>}
     */
    public function cari(string $kataKunci): array
    {
        $kata = $this->pecah($kataKunci);

        if ($kata === []) {
            return ['halaman' => [], 'paket' => [], 'wilayah' => []];
        }

        $halaman = array_values(array_filter(
            $this->halaman(),
            fn (array $item) => $this->cocok($kata, $item['judul'].' '.$item['keterangan'].' '.$item['kata']),
        ));

        $paket = PaketInternet::aktif()->orderBy('kecepatan_mbps')->get()
            ->filter(fn (PaketInternet $item) => $this->cocok($kata, "{$item->nama_paket} {$item->kecepatan_mbps} mbps paket internet harga"))
            ->map(fn (PaketInternet $item) => [
                'judul' => $item->nama_paket,
                'keterangan' => "{$item->kecepatan_mbps} Mbps, ".Format::rupiah($item->harga).config('jnet.paket.label_periode_harga'),
                'tautan' => route('paket').'#paket-'.$item->id_paket,
            ])
            ->values()
            ->all();

        $wilayah = [];
        foreach ($this->wilayah->daftar() as $kotaKabupaten => $daftarKecamatan) {
            foreach ($daftarKecamatan as $kecamatan) {
                if ($this->cocok($kata, "{$kecamatan} {$kotaKabupaten} kecamatan wilayah jangkauan")) {
                    $wilayah[] = [
                        'judul' => $kecamatan,
                        'keterangan' => "{$kotaKabupaten}, termasuk wilayah layanan JNET",
                        'tautan' => route('jangkauan'),
                    ];
                }
            }
        }

        return ['halaman' => $halaman, 'paket' => $paket, 'wilayah' => $wilayah];
    }

    /**
     * Pintasan yang ditampilkan selama kolom pencarian masih kosong.
     *
     * @return list<array{judul: string, keterangan: string, tautan: string}>
     */
    public function saran(): array
    {
        $penting = ['paket', 'jangkauan', 'pengajuan.create', 'alur', 'kontak'];

        return array_values(array_map(
            fn (array $item) => ['judul' => $item['judul'], 'keterangan' => $item['keterangan'], 'tautan' => $item['tautan']],
            array_filter($this->halaman(), fn (array $item) => in_array($item['route'], $penting, true)),
        ));
    }

    /**
     * Halaman publik yang dapat dicari, beserta kata kunci yang biasa diketik pengunjung.
     *
     * @return list<array{route: string, judul: string, keterangan: string, kata: string, tautan: string}>
     */
    private function halaman(): array
    {
        $halaman = [
            ['beranda', 'Beranda', 'Halaman utama JNET: promo, pilihan paket, dan tanya jawab.', 'utama home promo tanya jawab faq'],
            ['paket', 'Paket internet', 'Daftar paket internet rumah JNET beserta kecepatan dan harganya.', 'harga kecepatan mbps wifi langganan biaya promo'],
            ['jangkauan', 'Jangkauan', 'Wilayah layanan JNET di Kota dan Kabupaten Probolinggo.', 'wilayah area cakupan coverage lokasi kecamatan kota kabupaten probolinggo cek'],
            ['alur', 'Alur berlangganan', 'Langkah dari pengajuan sampai internet terpasang.', 'cara langganan berlangganan daftar pasang pemasangan langkah proses'],
            ['pengajuan.create', 'Ajukan pemasangan', 'Isi formulir pengajuan pemasangan internet secara online.', 'pengajuan pasang pemasangan daftar langganan berlangganan formulir baru'],
            ['akun', 'Lacak pengajuan', 'Cek status pengajuan dan jadwal pemasangan di akun Anda.', 'lacak status cek jadwal akun pengajuan saya'],
            ['profil', 'Profil JNET', 'Mengenal JNET dan layanan kami.', 'profil tentang perusahaan jnet kami'],
            ['kontak', 'Kontak', 'Telepon, WhatsApp, email, dan alamat kantor JNET.', 'hubungi telepon whatsapp wa email alamat kantor cs layanan pelanggan jam'],
            ['kemitraan', 'Kemitraan (B2B)', 'Kerja sama bisnis langsung dengan direktur JNET.', 'mitra bisnis b2b kerja sama perusahaan kantor direktur'],
            ['login', 'Masuk', 'Masuk ke akun pelanggan JNET.', 'login akun masuk'],
            ['daftar', 'Daftar akun', 'Buat akun pelanggan untuk mengajukan pemasangan.', 'registrasi daftar akun buat'],
        ];

        // Halaman Bayar Tagihan hanya berisi informasi jika admin sudah mengisinya.
        if ($this->situs->ada('info_pembayaran')) {
            $halaman[] = ['bayar-tagihan', 'Bayar tagihan', 'Cara membayar tagihan internet JNET.', 'bayar tagihan pembayaran transfer rekening'];
        }

        return array_values(array_map(
            fn (array $item) => ['route' => $item[0], 'judul' => $item[1], 'keterangan' => $item[2], 'kata' => $item[3], 'tautan' => route($item[0])],
            array_filter($halaman, fn (array $item) => Route::has($item[0])),
        ));
    }

    /**
     * Kata kunci dipecah menjadi kata-kata kecil (huruf kecil, tanpa tanda baca), paling banyak 8 kata.
     *
     * @return list<string>
     */
    private function pecah(string $kataKunci): array
    {
        $bersih = Str::of($kataKunci)->lower()->replaceMatches('/[^\p{L}\p{N}]+/u', ' ')->squish()->value();

        return array_slice(array_values(array_filter(explode(' ', $bersih), fn (string $kata) => $kata !== '')), 0, 8);
    }

    /**
     * @param  list<string>  $kata
     */
    private function cocok(array $kata, string $teks): bool
    {
        $teks = Str::lower($teks);

        foreach ($kata as $satu) {
            if (! str_contains($teks, $satu)) {
                return false;
            }
        }

        return true;
    }
}
