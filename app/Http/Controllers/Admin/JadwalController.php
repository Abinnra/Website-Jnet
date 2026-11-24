<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisJadwal;
use App\Enums\StatusJadwal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JadwalRequest;
use App\Models\JadwalLayanan;
use App\Models\RegistrasiPelanggan;
use App\Services\JadwalService;
use App\Support\Tampilan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Use Case: Mengelola Jadwal Pemasangan (tabel jadwal_layanan).
 * Revisi mitra: tidak ada survei terpisah; jadwal survei lama tetap tampil sebagai riwayat.
 */
class JadwalController extends Controller
{
    /** Pilihan tampilan daftar jadwal. */
    private const TAMPILAN = [
        'mendatang' => 'Mendatang',
        'perlu-hasil' => 'Perlu dicatat hasilnya',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    public function __construct(private JadwalService $jadwalService) {}

    /**
     * Daftar jadwal dengan filter waktu.
     * Revisi (Vue): filter dikirim Inertia tanpa memuat ulang halaman.
     */
    public function index(Request $request): Response
    {
        $tampilan = array_key_exists($request->query('tampilan'), self::TAMPILAN) ? $request->query('tampilan') : 'mendatang';

        $query = JadwalLayanan::query()->with(['registrasi', 'admin']);

        match ($tampilan) {
            'mendatang' => $query->where('status_jadwal', StatusJadwal::Terjadwal->value)
                ->whereDate('tanggal', '>=', today())
                ->orderBy('tanggal')->orderBy('waktu_mulai'),
            'perlu-hasil' => $query->where('status_jadwal', StatusJadwal::Terjadwal->value)
                ->whereDate('tanggal', '<', today())
                ->orderBy('tanggal')->orderBy('waktu_mulai'),
            'selesai' => $query->where('status_jadwal', StatusJadwal::Selesai->value)
                ->orderByDesc('tanggal')->orderByDesc('waktu_mulai'),
            'dibatalkan' => $query->where('status_jadwal', StatusJadwal::Dibatalkan->value)
                ->orderByDesc('tanggal')->orderByDesc('waktu_mulai'),
        };

        $daftarJadwal = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Jadwal/Index', [
            'saringTampilan' => collect(self::TAMPILAN)->map(fn (string $label, string $kunci) => [
                'label' => $label,
                'tautan' => route('admin.jadwal.index', ['tampilan' => $kunci]),
                'aktif' => $tampilan === $kunci,
            ])->values()->all(),
            'teksKosong' => match ($tampilan) {
                'mendatang' => 'Belum ada jadwal mendatang. Jadwalkan pemasangan dari pengajuan berstatus "Data Diverifikasi".',
                'perlu-hasil' => 'Semua jadwal yang sudah lewat telah dicatat hasilnya.',
                default => 'Belum ada jadwal pada kategori ini.',
            },
            'jadwal' => $daftarJadwal->getCollection()->map(fn (JadwalLayanan $jadwal) => [
                'id' => $jadwal->id_jadwal,
                'tanggal' => $jadwal->tanggal->translatedFormat('D, d M Y'),
                'jam' => 'Pukul '.Tampilan::jam($jadwal->waktu_mulai).' WIB',
                // Hanya jadwal lama yang masih berjenis survei yang diberi keterangan.
                'jenisLama' => $jadwal->jenis_jadwal === JenisJadwal::Survei ? 'Survei (alur lama)' : null,
                'nomor' => $jadwal->registrasi->nomor_pengajuan,
                'nama' => $jadwal->registrasi->nama_pelanggan,
                'lokasi' => $jadwal->registrasi->lokasi,
                'status' => Tampilan::lencana($jadwal->status_jadwal),
                'tautanCatatHasil' => $jadwal->status_jadwal->isAktif() && ! $jadwal->tanggal->isAfter(today())
                    ? route('admin.hasil.create', $jadwal)
                    : null,
                'tautanUbah' => $jadwal->status_jadwal->isAktif() ? route('admin.jadwal.edit', $jadwal) : null,
                'tautanPengajuan' => route('admin.pengajuan.show', $jadwal->id_registrasi),
            ])->all(),
            'paginasi' => Tampilan::paginasi($daftarJadwal),
        ]);
    }

    /**
     * Form jadwal baru. Pengajuan berstatus Diverifikasi (atau Layak pada data lama)
     * langsung dijadwalkan pemasangan. Selain itu ditunda.
     */
    public function create(RegistrasiPelanggan $registrasi): Response|RedirectResponse
    {
        $jenis = JenisJadwal::untukStatus($registrasi->status_pengajuan);

        if (! $jenis) {
            return redirect()
                ->route('admin.pengajuan.show', $registrasi)
                ->with('gagal', "Penjadwalan ditunda: pengajuan berstatus \"{$registrasi->status_pengajuan->label()}\" belum memenuhi syarat untuk dijadwalkan.");
        }

        return $this->formulir($registrasi, new JadwalLayanan(['tanggal' => null, 'waktu_mulai' => null]), $jenis);
    }

    public function store(JadwalRequest $request, RegistrasiPelanggan $registrasi): RedirectResponse
    {
        $jenis = JenisJadwal::from($request->validated('jenis_jadwal'));

        $this->jadwalService->buat($registrasi, $jenis, $request->dataJadwal(), $request->user('admin'));

        return redirect()
            ->route('admin.pengajuan.show', $registrasi)
            ->with('sukses', "Jadwal {$this->kecil($jenis)} tersimpan. Status pengajuan menjadi \"{$jenis->statusSetelahDijadwalkan()->label()}\".");
    }

    public function edit(JadwalLayanan $jadwal): Response|RedirectResponse
    {
        if (! $jadwal->status_jadwal->isAktif()) {
            return redirect()
                ->route('admin.pengajuan.show', $jadwal->id_registrasi)
                ->with('gagal', "Jadwal berstatus \"{$jadwal->status_jadwal->label()}\" tidak dapat diubah.");
        }

        return $this->formulir($jadwal->registrasi, $jadwal, $jadwal->jenis_jadwal);
    }

    /**
     * Halaman formulir jadwal (baru atau ubah), Pages/Admin/Jadwal/Form.vue.
     */
    private function formulir(RegistrasiPelanggan $pengajuan, JadwalLayanan $jadwal, JenisJadwal $jenis): Response
    {
        $modeUbah = $jadwal->exists;
        $aturan = config('jnet.jadwal');

        return Inertia::render('Admin/Jadwal/Form', [
            'modeUbah' => $modeUbah,
            'judul' => ($modeUbah ? 'Ubah jadwal ' : 'Jadwalkan ').$this->kecil($jenis),
            'jenis' => [
                'nilai' => $jenis->value,
                'label' => $jenis->label(),
                'keterangan' => $modeUbah ? null : 'Tanpa survei terpisah: teknisi mengecek lokasi lalu langsung memasang. '
                    .'Setelah kunjungan, catat hasilnya: pemasangan berhasil atau tidak layak dipasang.',
                'statusSetelahDisimpan' => $modeUbah ? null : $jenis->statusSetelahDijadwalkan()->label(),
            ],
            'pengajuan' => [
                'nomor' => $pengajuan->nomor_pengajuan,
                'ringkas' => "{$pengajuan->nomor_pengajuan} – {$pengajuan->nama_pelanggan}, {$pengajuan->lokasi}",
                'alamat' => $pengajuan->alamat_pemasangan,
                'lokasi' => $pengajuan->lokasi,
                'whatsapp' => '+'.$pengajuan->no_whatsapp,
                'tautan' => route('admin.pengajuan.show', $pengajuan),
            ],
            'jadwal' => [
                'tanggal' => $jadwal->tanggal?->toDateString(),
                'waktu_mulai' => $jadwal->waktu_mulai ? Tampilan::jam($jadwal->waktu_mulai) : '',
                'catatan' => $jadwal->catatan ?? '',
            ],
            'tanggalMinimal' => today()->toDateString(),
            'bantuanJam' => ($aturan['jam_operasional_mulai'] && $aturan['jam_operasional_selesai'])
                ? 'Jam operasional: '.$aturan['jam_operasional_mulai'].'–'.$aturan['jam_operasional_selesai'].'.'
                : 'Jam operasional belum dikonfirmasi mitra.',
            'tautanSimpan' => $modeUbah ? route('admin.jadwal.update', $jadwal) : route('admin.jadwal.store', $pengajuan),
        ]);
    }

    public function update(JadwalRequest $request, JadwalLayanan $jadwal): RedirectResponse
    {
        try {
            $this->jadwalService->ubah($jadwal, $request->dataJadwal(), $request->user('admin'));
        } catch (ValidationException $e) {
            if (isset($e->errors()['jadwal'])) {
                return redirect()->route('admin.pengajuan.show', $jadwal->id_registrasi)
                    ->with('gagal', $e->errors()['jadwal'][0]);
            }

            throw $e;
        }

        return redirect()
            ->route('admin.pengajuan.show', $jadwal->id_registrasi)
            ->with('sukses', "Jadwal {$this->kecil($jadwal->jenis_jadwal)} berhasil diubah.");
    }

    private function kecil(JenisJadwal $jenis): string
    {
        return mb_strtolower($jenis->label());
    }
}
