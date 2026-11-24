<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Services\PencarianService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Halaman pencarian situs (revisi: kolom "Cari apa?" di navbar HP). Kata kunci dikirim lewat GET (?q=),
 * jadi hasilnya dapat dibagikan dan tombol Back browser tetap bekerja. Halaman ini tidak diindeks mesin pencari.
 */
class CariController extends Controller
{
    public function __invoke(Request $request, PencarianService $pencarian): View
    {
        $kataKunci = Str::limit(trim((string) $request->query('q', '')), 100, '');

        return view('publik.cari', [
            'kataKunci' => $kataKunci,
            'hasil' => $pencarian->cari($kataKunci),
            'saran' => $kataKunci === '' ? $pencarian->saran() : [],
        ]);
    }
}
