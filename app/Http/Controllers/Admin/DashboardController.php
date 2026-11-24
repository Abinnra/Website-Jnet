<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusJadwal;
use App\Enums\StatusPengajuan;
use App\Http\Controllers\Controller;
use App\Models\JadwalLayanan;
use App\Models\RegistrasiPelanggan;
use App\Services\SiteSettingService;
use App\Support\Captcha;
use App\Support\Tampilan;
use App\Support\Wilayah;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Use Case: Melihat Dashboard Admin.
 * Semua angka dihitung dari database, bukan data contoh.
 * Revisi (Vue): halaman dirender Vue (resources/js/Pages/Admin/Dashboard.vue).
 */
class DashboardController extends Controller
{
    public function __invoke(SiteSettingService $siteSettings): Response
    {
        // Jumlah pengajuan per status; status yang belum dipakai tetap tampil 0.
        $jumlahPerStatus = RegistrasiPelanggan::query()
            ->selectRaw('status_pengajuan, COUNT(*) as jumlah')
            ->groupBy('status_pengajuan')
            ->pluck('jumlah', 'status_pengajuan');

        $jumlah = fn (StatusPengajuan $status) => (int) ($jumlahPerStatus[$status->value] ?? 0);
        $total = (int) $jumlahPerStatus->sum();

        $jadwalAktif = fn () => JadwalLayanan::query()->where('status_jadwal', StatusJadwal::Terjadwal->value);

        // Hal yang menunggu tindakan admin.
        $menungguVerifikasi = $jumlah(StatusPengajuan::Baru);
        $menungguVerifikasiLuar = RegistrasiPelanggan::query()
            ->where('status_pengajuan', StatusPengajuan::Baru->value)
            ->whereNotIn('kota_kabupaten', Wilayah::wilayahUtama())
            ->count();
        // Siap dijadwalkan pemasangan: data terverifikasi (Layak hanya pada data alur lama).
        $siapDiverifikasi = $jumlah(StatusPengajuan::Diverifikasi);
        $siapLama = $jumlah(StatusPengajuan::Layak);
        $kunjunganHariIni = $jadwalAktif()->whereDate('tanggal', today())->count();
        $hasilTertunda = $jadwalAktif()->whereDate('tanggal', '<', today())->count();

        $labelInformasi = collect(config('jnet.site_settings'))
            ->only($siteSettings->kontakBelumLengkap())
            ->pluck('label')
            ->map(fn (string $label) => mb_strtolower($label));

        return Inertia::render('Admin/Dashboard', [
            'tanggal' => now()->translatedFormat('l, d F Y'),
            'informasiKosong' => $labelInformasi->isNotEmpty()
                ? 'Informasi website belum lengkap: '.$labelInformasi->implode(', ').'.'
                : null,
            'tautanInformasi' => route('admin.informasi.edit'),
            'captchaBelumAktif' => Captcha::kunciProduksiBelumDiisi(),

            'tindakan' => [
                [
                    'ikon' => 'pengajuan',
                    'angka' => $menungguVerifikasi,
                    'judul' => 'Menunggu verifikasi',
                    'teks' => $menungguVerifikasiLuar > 0
                        ? "Pengajuan baru yang datanya perlu diperiksa, {$menungguVerifikasiLuar} di antaranya dari luar Probolinggo."
                        : 'Pengajuan baru yang datanya perlu diperiksa.',
                    'tautan' => route('admin.pengajuan.index', ['status' => StatusPengajuan::Baru->value]),
                    'peringatan' => false,
                ],
                [
                    'ikon' => 'jadwal-tambah',
                    'angka' => $siapDiverifikasi + $siapLama,
                    'judul' => 'Siap dijadwalkan',
                    'teks' => 'Data sudah diverifikasi dan menunggu jadwal pemasangan.',
                    'tautan' => route('admin.pengajuan.index', [
                        'status' => $siapDiverifikasi > 0 || $siapLama === 0 ? StatusPengajuan::Diverifikasi->value : StatusPengajuan::Layak->value,
                    ]),
                    'peringatan' => false,
                ],
                [
                    'ikon' => 'jadwal',
                    'angka' => $kunjunganHariIni,
                    'judul' => 'Kunjungan hari ini',
                    'teks' => 'Pemasangan yang dijadwalkan hari ini.',
                    'tautan' => route('admin.jadwal.index'),
                    'peringatan' => false,
                ],
                [
                    'ikon' => 'catat',
                    'angka' => $hasilTertunda,
                    'judul' => 'Hasil belum dicatat',
                    'teks' => 'Jadwal yang sudah lewat tanpa hasil.',
                    'tautan' => route('admin.jadwal.index', ['tampilan' => 'perlu-hasil']),
                    'peringatan' => $hasilTertunda > 0,
                ],
            ],

            'totalPengajuan' => $total,
            // Status alur lama (survei) hanya tampil selama masih ada pengajuannya.
            'ringkasanStatus' => collect(StatusPengajuan::cases())
                ->filter(fn (StatusPengajuan $status) => $status->masihDipakai() || $jumlah($status) > 0)
                ->map(fn (StatusPengajuan $status) => [
                    'status' => Tampilan::lencana($status),
                    'jumlah' => $jumlah($status),
                    'persen' => $total > 0 ? round($jumlah($status) / $total * 100, 1) : 0,
                    'tautan' => route('admin.pengajuan.index', ['status' => $status->value]),
                ])->values()->all(),

            'pengajuanBaru' => RegistrasiPelanggan::query()
                ->with('paket')
                ->where('status_pengajuan', StatusPengajuan::Baru->value)
                ->orderByDesc('created_at')
                ->orderByDesc('id_registrasi')
                ->limit(5)
                ->get()
                ->map(fn (RegistrasiPelanggan $item) => [
                    'nomor' => $item->nomor_pengajuan,
                    'nama' => $item->nama_pelanggan,
                    'tautan' => route('admin.pengajuan.show', $item),
                    'keterangan' => "{$item->paket->nama_paket}, {$item->lokasi}. Diajukan ".Tampilan::tanggal($item->created_at, 'd M Y, H.i'),
                    'catatan' => $item->catatan ? Str::limit($item->catatan, 80, preserveWords: true) : null,
                ])->all(),
            'tautanPengajuanBaru' => route('admin.pengajuan.index', ['status' => StatusPengajuan::Baru->value]),

            'jadwal' => [
                'judul' => 'Pemasangan mendatang',
                'kosong' => 'Belum ada jadwal pemasangan.',
                'tautan' => route('admin.jadwal.index'),
                'daftar' => $this->jadwalMendatang(),
            ],
        ]);
    }

    /**
     * Jadwal kunjungan aktif mulai hari ini, diurutkan dari yang paling dekat.
     *
     * @return list<array{waktu: string, tautan: string, nomor: string, keterangan: string}>
     */
    private function jadwalMendatang(): array
    {
        return JadwalLayanan::query()
            ->with('registrasi')
            ->where('status_jadwal', StatusJadwal::Terjadwal->value)
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->limit(5)
            ->get()
            ->map(fn (JadwalLayanan $jadwal) => [
                'waktu' => ($jadwal->tanggal->isToday() ? 'Hari ini' : $jadwal->tanggal->translatedFormat('D, d M Y')).', '.Tampilan::jam($jadwal->waktu_mulai),
                'tautan' => route('admin.pengajuan.show', $jadwal->id_registrasi),
                'nomor' => $jadwal->registrasi->nomor_pengajuan,
                'keterangan' => "{$jadwal->registrasi->nama_pelanggan}, {$jadwal->registrasi->lokasi}",
            ])->all();
    }
}
