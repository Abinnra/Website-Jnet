<?php

namespace App\Support;

/**
 * Banner foto geser di beranda (config/banner.php).
 *
 * Banner yang file fotonya belum ada di folder public dilewati. Jika tidak ada
 * satu pun, beranda menampilkan hero teks bawaan.
 *
 * Revisi mitra (optimasi gambar): di samping foto asli dapat disiapkan varian
 * berukuran untuk srcset, dalam format AVIF, WebP, dan JPEG. Browser memilih format
 * terbaik yang didukungnya dan ukuran yang sesuai layar:
 *   banner-1-{640|960|1280|1920}.{avif|webp|jpg}  versi desktop (16:9)
 *   banner-1-hp-{480|768|1080}.{avif|webp|jpg}    versi khusus HP (≤ 767px)
 * Varian yang belum ada dilewati; tanpa varian sama sekali dipakai foto asli.
 *
 * Revisi mitra (tampilan HP seperti IndiHome): versi HP berbentuk tegak (4:5) berisi foto
 * di bagian atas dan tulisan promo di bagian bawah. Bila semua slide punya versi tegak,
 * banner di HP tampil penuh di belakang navbar dengan tombol dan titik geser di atas foto.
 */
class Banner
{
    private const POSISI = ['kiri', 'tengah', 'kanan'];

    /** Batas foto banner agar beranda tetap ringan dimuat. */
    public const MAKS_SLIDE = 6;

    /** Lebar varian (px) yang dicari untuk srcset. */
    public const LEBAR_DESKTOP = [640, 960, 1280, 1920];

    public const LEBAR_HP = [480, 768, 1080];

    /** Urutan format dari yang paling ringan; JPEG sebagai cadangan semua browser. */
    public const FORMAT = ['avif' => 'image/avif', 'webp' => 'image/webp', 'jpg' => 'image/jpeg'];

    /** Lebar tampilan banner: selebar layar, paling lebar 1920px (banner__bingkai). */
    public const SIZES = '(min-width: 1920px) 1920px, 100vw';

    /**
     * @return list<array{src: string, srcset: array<string, ?string>, srcset_hp: array<string, ?string>, lebar: ?int, tinggi: ?int, lebar_hp: ?int, tinggi_hp: ?int, alt: string, tautan: string, label_tombol: string, posisi: string}>
     */
    public static function slide(): array
    {
        $hasil = [];

        foreach (config('banner.slide', []) as $slide) {
            if (count($hasil) >= self::MAKS_SLIDE) {
                break;
            }

            $gambar = $slide['gambar'] ?? null;

            if (! is_string($gambar) || ! is_file(public_path($gambar))) {
                continue;
            }

            [$lebar, $tinggi] = @getimagesize(public_path($gambar)) ?: [null, null];
            $dasar = preg_replace('/\.[a-z0-9]+$/i', '', $gambar);
            $posisi = $slide['posisi_tombol'] ?? 'kiri';

            // Versi HP: 'gambar_hp' di config (beserta variannya), atau varian otomatis banner-N-hp-*.
            $gambarHp = $slide['gambar_hp'] ?? null;
            $adaGambarHp = is_string($gambarHp) && is_file(public_path($gambarHp));
            $dasarHp = $adaGambarHp ? preg_replace('/\.[a-z0-9]+$/i', '', $gambarHp) : $dasar.'-hp';
            $srcsetHp = self::srcset($dasarHp, self::LEBAR_HP);

            if ($adaGambarHp && array_filter($srcsetHp) === []) {
                $srcsetHp['jpg'] = self::versi($gambarHp);
            }

            $varianHp = self::varianPertama($dasarHp, self::LEBAR_HP) ?? ($adaGambarHp ? $gambarHp : null);
            [$lebarHp, $tinggiHp] = $varianHp ? (@getimagesize(public_path($varianHp)) ?: [null, null]) : [null, null];

            $srcset = self::srcset($dasar, self::LEBAR_DESKTOP);

            $hasil[] = [
                // Cadangan <img>: JPEG 1280px bila ada (jauh lebih ringan dari foto asli), selain itu foto asli.
                'src' => is_file(public_path($dasar.'-1280.jpg')) ? self::versi($dasar.'-1280.jpg') : self::versi($gambar),
                'srcset' => $srcset,
                'srcset_hp' => $srcsetHp,
                'lebar' => $lebar,
                'tinggi' => $tinggi,
                'lebar_hp' => $lebarHp,
                'tinggi_hp' => $tinggiHp,
                'alt' => trim((string) ($slide['alt'] ?? '')) ?: 'Promo JNET '.(count($hasil) + 1),
                'tautan' => self::tautan($slide['tautan'] ?? null),
                'label_tombol' => trim((string) ($slide['label_tombol'] ?? '')) ?: 'Langganan',
                'posisi' => in_array($posisi, self::POSISI, true) ? $posisi : 'kiri',
            ];
        }

        return $hasil;
    }

    /**
     * Semua slide punya versi HP tegak (lebih tinggi daripada lebarnya). Hanya dengan versi ini
     * banner di HP ditampilkan penuh di belakang navbar dengan tombol di atas foto; versi lain
     * (misalnya potongan persegi atau tanpa versi HP) tetap memakai susunan biasa, yaitu tombol
     * dan titik geser di bawah foto, agar tulisan di foto tidak tertutup.
     *
     * @param  list<array{lebar_hp: ?int, tinggi_hp: ?int}>  $slide
     */
    public static function versiHpTegak(array $slide): bool
    {
        return $slide !== [] && collect($slide)->every(
            fn (array $item): bool => $item['lebar_hp'] !== null && $item['tinggi_hp'] > $item['lebar_hp']
        );
    }

    /**
     * Isi atribut srcset per format, misalnya "…-640.webp 640w, …-1280.webp 1280w"; null jika tidak ada varian.
     *
     * @param  list<int>  $daftarLebar
     * @return array<string, ?string>
     */
    private static function srcset(string $dasar, array $daftarLebar): array
    {
        $hasil = [];

        foreach (array_keys(self::FORMAT) as $format) {
            $kandidat = [];

            foreach ($daftarLebar as $lebar) {
                $jalur = "{$dasar}-{$lebar}.{$format}";

                if (is_file(public_path($jalur))) {
                    $kandidat[] = self::versi($jalur).' '.$lebar.'w';
                }
            }

            $hasil[$format] = $kandidat === [] ? null : implode(', ', $kandidat);
        }

        return $hasil;
    }

    /**
     * Varian pertama yang ada (untuk membaca ukuran asli versi HP).
     *
     * @param  list<int>  $daftarLebar
     */
    private static function varianPertama(string $dasar, array $daftarLebar): ?string
    {
        foreach ($daftarLebar as $lebar) {
            foreach (array_keys(self::FORMAT) as $format) {
                if (is_file(public_path("{$dasar}-{$lebar}.{$format}"))) {
                    return "{$dasar}-{$lebar}.{$format}";
                }
            }
        }

        return null;
    }

    /**
     * Tautan tombol: kosong berarti formulir pengajuan; hanya alamat di website ini
     * (diawali "/") yang diterima agar tombol tidak mengarah ke situs lain.
     */
    private static function tautan(mixed $tautan): string
    {
        if (is_string($tautan) && str_starts_with($tautan, '/') && ! str_starts_with($tautan, '//')) {
            return url($tautan);
        }

        return route('pengajuan.create');
    }

    private static function versi(string $path): string
    {
        return asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');
    }
}
