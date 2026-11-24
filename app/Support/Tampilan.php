<?php

namespace App\Support;

use App\Enums\StatusJadwal;
use App\Enums\StatusPengajuan;
use Carbon\CarbonInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Revisi (Vue): bentuk data siap tampil untuk halaman Vue (Inertia). Teks tanggal, label status,
 * dan warna lencana disiapkan di server, sehingga isinya sama persis dengan versi Blade sebelumnya
 * dan komponen Vue cukup menampilkannya.
 */
class Tampilan
{
    /**
     * Lencana status: teks dan varian warna (kelas lencana--{warna} di Vue).
     *
     * @return array{label: string, warna: string}
     */
    public static function lencana(StatusPengajuan|StatusJadwal $status): array
    {
        return ['label' => $status->label(), 'warna' => $status->warna()];
    }

    /**
     * Tanggal berformat Indonesia, misalnya "23 November 2026" atau "Senin, 23 November 2026".
     */
    public static function tanggal(?CarbonInterface $waktu, string $format = 'd F Y'): ?string
    {
        return $waktu?->translatedFormat($format);
    }

    /**
     * Jam kunjungan "08:30" dari kolom TIME ("08:30:00").
     */
    public static function jam(?string $waktu): string
    {
        return substr((string) $waktu, 0, 5);
    }

    /**
     * Data paginasi untuk komponen Vue: rentang yang ditampilkan dan tautan nomor halaman
     * (label "..." untuk lompatan, url null).
     *
     * @return array{dari: ?int, sampai: ?int, total: int, sebelumnya: ?string, berikutnya: ?string, tautan: list<array{label: string, url: ?string, aktif: bool}>}
     */
    public static function paginasi(LengthAwarePaginator $paginator): array
    {
        return [
            'dari' => $paginator->firstItem(),
            'sampai' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'sebelumnya' => $paginator->previousPageUrl(),
            'berikutnya' => $paginator->nextPageUrl(),
            'tautan' => $paginator->hasPages()
                ? $paginator->linkCollection()->slice(1, -1)->map(fn (array $tautan) => [
                    'label' => (string) $tautan['label'],
                    'url' => $tautan['url'],
                    'aktif' => (bool) $tautan['active'],
                ])->values()->all()
                : [],
        ];
    }
}
