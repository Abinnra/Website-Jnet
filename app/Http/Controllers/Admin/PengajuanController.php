<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPengajuan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PesanPelangganRequest;
use App\Http\Requests\Admin\UbahStatusPengajuanRequest;
use App\Models\RegistrasiPelanggan;
use App\Services\PengajuanService;
use App\Support\DetailPengajuanAdmin;
use App\Support\Format;
use App\Support\Tampilan;
use App\Support\Wilayah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Use Case: Mengelola Pengajuan (admin).
 */
class PengajuanController extends Controller
{
    public function __construct(private PengajuanService $pengajuanService) {}

    /**
     * Daftar pengajuan dengan filter status, filter wilayah, dan pencarian.
     * Revisi (Vue): filter dan pencarian langsung saat mengetik dikirim Inertia tanpa memuat ulang halaman.
     */
    public function index(Request $request): Response
    {
        $statusFilter = StatusPengajuan::tryFrom((string) $request->query('status'));
        $kataKunci = trim((string) $request->query('q'));
        $hanyaLuar = $request->query('wilayah') === 'luar';

        $pengajuan = RegistrasiPelanggan::query()
            ->with(['paket', 'user'])
            ->when($statusFilter, fn (Builder $query) => $query->where('status_pengajuan', $statusFilter->value))
            ->when($hanyaLuar, fn (Builder $query) => $query->whereNotIn('kota_kabupaten', Wilayah::wilayahUtama()))
            ->when($kataKunci !== '', fn (Builder $query) => $this->terapkanPencarian($query, $kataKunci))
            ->orderByDesc('created_at')
            ->orderByDesc('id_registrasi')
            ->paginate(15)
            ->withQueryString();

        $jumlahPerStatus = RegistrasiPelanggan::query()
            ->selectRaw('status_pengajuan, COUNT(*) as jumlah')
            ->groupBy('status_pengajuan')
            ->pluck('jumlah', 'status_pengajuan');

        // Alamat filter selalu membawa filter lain yang sedang dipakai.
        $alamat = fn (?string $status, bool $luar, ?string $q = null) => route('admin.pengajuan.index', array_filter([
            'status' => $status,
            'q' => $q ?? $kataKunci,
            'wilayah' => $luar ? 'luar' : null,
        ], fn ($nilai) => $nilai !== null && $nilai !== ''));

        $saringStatus = [[
            'label' => 'Semua',
            'jumlah' => (int) $jumlahPerStatus->sum(),
            'tautan' => $alamat(null, $hanyaLuar),
            'aktif' => $statusFilter === null,
        ]];

        foreach (StatusPengajuan::cases() as $status) {
            // Status alur lama (survei) hanya muncul sebagai filter selama masih ada datanya.
            if (! $status->masihDipakai() && empty($jumlahPerStatus[$status->value]) && $statusFilter !== $status) {
                continue;
            }

            $saringStatus[] = [
                'label' => $status->label(),
                'jumlah' => (int) ($jumlahPerStatus[$status->value] ?? 0),
                'tautan' => $alamat($status->value, $hanyaLuar),
                'aktif' => $statusFilter === $status,
            ];
        }

        return Inertia::render('Admin/Pengajuan/Index', [
            'filter' => [
                'status' => $statusFilter?->value,
                'q' => $kataKunci,
                'wilayah' => $hanyaLuar ? 'luar' : null,
            ],
            'saringStatus' => $saringStatus,
            'saringWilayah' => [
                ['label' => 'Semua wilayah', 'jumlah' => null, 'tautan' => $alamat($statusFilter?->value, false), 'aktif' => ! $hanyaLuar],
                [
                    'label' => 'Luar Probolinggo',
                    'jumlah' => RegistrasiPelanggan::query()->whereNotIn('kota_kabupaten', Wilayah::wilayahUtama())->count(),
                    'tautan' => $alamat($statusFilter?->value, true),
                    'aktif' => $hanyaLuar,
                ],
            ],
            'tautanCari' => route('admin.pengajuan.index'),
            'tautanHapusCari' => $alamat($statusFilter?->value, $hanyaLuar, ''),
            'adaFilter' => $kataKunci !== '' || $statusFilter !== null || $hanyaLuar,
            'pengajuan' => $pengajuan->getCollection()->map(fn (RegistrasiPelanggan $item) => [
                'nomor' => $item->nomor_pengajuan,
                'tautan' => route('admin.pengajuan.show', $item),
                'nama' => $item->nama_pelanggan,
                'whatsapp' => '+'.$item->no_whatsapp,
                'catatan' => $item->catatan ? Str::limit($item->catatan, 60, preserveWords: true) : null,
                'paket' => $item->paket->nama_paket,
                'kecamatan' => $item->kecamatan,
                'kota' => $item->kota_kabupaten,
                'luarWilayah' => $item->luar_wilayah
                    ? 'Luar wilayah'.($item->jarak_km !== null ? ' · '.Format::jarak($item->jarak_km) : '')
                    : null,
                'diajukan' => Tampilan::tanggal($item->created_at, 'd M Y'),
                'status' => Tampilan::lencana($item->status_pengajuan),
            ])->all(),
            'paginasi' => Tampilan::paginasi($pengajuan),
        ]);
    }

    public function show(RegistrasiPelanggan $registrasi): Response
    {
        $registrasi->load([
            'paket',
            'user',
            'diperbaruiOleh',
            'jadwal' => fn ($query) => $query->with(['admin', 'hasil.admin'])->orderBy('tanggal')->orderBy('waktu_mulai'),
        ]);

        return Inertia::render('Admin/Pengajuan/Show', DetailPengajuanAdmin::data($registrasi));
    }

    /**
     * updatePengajuan() pada Class Diagram: perubahan status manual.
     */
    public function ubahStatus(UbahStatusPengajuanRequest $request, RegistrasiPelanggan $registrasi): RedirectResponse
    {
        $tujuan = $request->statusTujuan();

        $this->pengajuanService->ubahStatus($registrasi, $tujuan, $request->user('admin'), $request->validated('pesan_untuk_pelanggan'));

        return redirect()
            ->route('admin.pengajuan.show', $registrasi)
            ->with('sukses', "Status {$registrasi->nomor_pengajuan} diubah menjadi \"{$tujuan->label()}\".");
    }

    /**
     * Cari berdasarkan nomor pengajuan, nama pemohon, nomor WhatsApp,
     * email akun, atau alamat.
     */
    /**
     * Pesan dari admin yang tampil di halaman detail pengajuan pelanggan (Revisi 3).
     */
    public function simpanPesan(PesanPelangganRequest $request, RegistrasiPelanggan $registrasi): RedirectResponse
    {
        $pesan = $request->validated('pesan_untuk_pelanggan');
        $berubah = $this->pengajuanService->simpanPesan($registrasi, $pesan, $request->user('admin'));

        $teks = match (true) {
            ! $berubah => 'Tidak ada perubahan pada pesan untuk pelanggan.',
            $pesan === null => 'Pesan untuk pelanggan dihapus.',
            default => 'Pesan untuk pelanggan disimpan dan tampil di halaman pengajuan pelanggan.',
        };

        return redirect()
            ->route('admin.pengajuan.show', $registrasi)
            ->with('sukses', $teks);
    }

    private function terapkanPencarian(Builder $query, string $kataKunci): void
    {
        $awalan = RegistrasiPelanggan::AWALAN_NOMOR;
        // Nomor pengajuan "JNET-261119-01", juga tanpa awalan atau tanda hubung ("261119-01", "jnet26111901").
        $nomorDicari = preg_match('/^(?:jnet-?)?(\d{6})-?(\d{2,})$/i', $kataKunci, $cocok)
            ? $awalan.$cocok[1].'-'.$cocok[2]
            : null;
        // Nomor yang dilihat pelanggan, yaitu tanggal pesannya saja ("JNET-261119" atau "jnet261119"):
        // semua pengajuan pada tanggal itu.
        $tanggalDicari = preg_match('/^jnet-?(\d{6})$/i', $kataKunci, $cocok) ? $awalan.$cocok[1].'-' : null;
        // Nomor format lama ("JNET-000012" atau "jnet12") yang mungkin masih ada di riwayat chat WhatsApp.
        $idNomorLama = preg_match('/^jnet-?0*(\d{1,10})$/i', $kataKunci, $cocok) ? (int) $cocok[1] : null;

        $nomorWa = Format::nomorWhatsapp($kataKunci);
        $pola = '%'.$this->escapeLike($kataKunci).'%';

        $query->where(function (Builder $query) use ($nomorDicari, $tanggalDicari, $idNomorLama, $nomorWa, $pola) {
            if ($nomorDicari !== null) {
                $query->orWhere('nomor_pengajuan', $nomorDicari);
            }

            if ($tanggalDicari !== null) {
                $query->orWhere('nomor_pengajuan', 'like', $tanggalDicari.'%');
            }

            if ($idNomorLama !== null) {
                $query->orWhere('id_registrasi', $idNomorLama);
            }

            if ($nomorWa !== null) {
                $query->orWhere('no_whatsapp', $nomorWa);
            }

            $query->orWhereRaw("nomor_pengajuan LIKE ? ESCAPE '!'", [$pola])
                ->orWhereRaw("nama_pelanggan LIKE ? ESCAPE '!'", [$pola])
                ->orWhereRaw("no_whatsapp LIKE ? ESCAPE '!'", [$pola])
                ->orWhereRaw("alamat_pemasangan LIKE ? ESCAPE '!'", [$pola])
                ->orWhereRaw("kecamatan LIKE ? ESCAPE '!'", [$pola])
                ->orWhereRaw("kota_kabupaten LIKE ? ESCAPE '!'", [$pola])
                ->orWhereHas('user', fn (Builder $user) => $user->whereRaw("email LIKE ? ESCAPE '!'", [$pola]));
        });
    }

    /**
     * Karakter % dan _ dari kata kunci dicari apa adanya, bukan sebagai wildcard.
     */
    private function escapeLike(string $teks): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $teks);
    }
}
