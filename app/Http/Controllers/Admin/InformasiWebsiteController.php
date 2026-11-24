<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InformasiWebsiteRequest;
use App\Services\SiteSettingService;
use App\Support\Tampilan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Use Case: Mengelola Informasi Website.
 * Revisi (Vue): halaman dirender Vue (Pages/Admin/Informasi/Edit.vue).
 */
class InformasiWebsiteController extends Controller
{
    /**
     * Pengelompokan isian di formulir.
     *
     * @var array<string, list<string>>
     */
    private const KELOMPOK = [
        'Identitas perusahaan' => ['nama_perusahaan', 'deskripsi_usaha'],
        'Kontak' => ['alamat_kantor', 'telepon', 'whatsapp', 'email', 'jam_layanan_kantor'],
        'Informasi layanan' => ['info_pemasangan', 'info_jangkauan', 'info_pembayaran'],
        'Kemitraan bisnis (B2B)' => ['nama_direktur', 'telepon_direktur', 'whatsapp_direktur', 'email_direktur', 'info_kemitraan'],
    ];

    /**
     * Halaman publik tempat setiap informasi ditampilkan.
     *
     * @var array<string, string>
     */
    private const TAMPIL_DI = [
        'nama_perusahaan' => 'Semua halaman',
        'deskripsi_usaha' => 'Profil',
        'alamat_kantor' => 'Profil, Kontak, footer',
        'telepon' => 'Profil, Kontak, footer',
        'whatsapp' => 'Semua tombol WhatsApp',
        'email' => 'Profil, Kontak, footer',
        'jam_layanan_kantor' => 'Profil, Kontak',
        'info_pemasangan' => 'Beranda, Paket',
        'info_jangkauan' => 'Jangkauan',
        'info_pembayaran' => 'Beranda (ubin Bayar Tagihan), Bayar tagihan',
        'nama_direktur' => 'Kemitraan',
        'telepon_direktur' => 'Kemitraan',
        'whatsapp_direktur' => 'Kemitraan (tombol WhatsApp direktur)',
        'email_direktur' => 'Kemitraan',
        'info_kemitraan' => 'Kemitraan',
    ];

    public function __construct(private SiteSettingService $siteSettings) {}

    public function edit(): Response
    {
        $definisi = config('jnet.site_settings', []);
        $nilai = $this->siteSettings->semua();
        $rincian = $this->siteSettings->rincian();

        $kelompok = [];

        foreach (self::KELOMPOK as $judul => $daftarKunci) {
            $kelompok[] = [
                'judul' => $judul,
                'isian' => array_map(function (string $kunci) use ($definisi, $rincian) {
                    $d = $definisi[$kunci];
                    $catatan = $rincian->get($kunci);

                    return [
                        'kunci' => $kunci,
                        'label' => $d['label'],
                        'tipe' => match ($d['tipe']) {
                            'textarea' => 'textarea',
                            'email' => 'email',
                            'telepon', 'whatsapp' => 'tel',
                            default => 'text',
                        },
                        'baris' => in_array($kunci, ['deskripsi_usaha', 'info_jangkauan', 'info_kemitraan'], true) ? 5 : 3,
                        'wajib' => in_array('required', $d['aturan'], true),
                        'bantuan' => trim($d['bantuan'].' Tampil di: '.self::TAMPIL_DI[$kunci].'.'),
                        'riwayat' => $catatan?->updated_at && $catatan->admin
                            ? 'Terakhir diubah '.Tampilan::tanggal($catatan->updated_at, 'd F Y, H.i').' WIB oleh '.$catatan->admin->nama
                            : null,
                    ];
                }, $daftarKunci),
            ];
        }

        return Inertia::render('Admin/Informasi/Edit', [
            'kelompok' => $kelompok,
            'nilai' => $nilai,
            'tautanSimpan' => route('admin.informasi.update'),
            'tautanWebsite' => route('beranda'),
        ]);
    }

    public function update(InformasiWebsiteRequest $request): RedirectResponse
    {
        $berubah = $this->siteSettings->simpan($request->validated(), $request->user('admin'));

        return redirect()->route('admin.informasi.edit')->with(
            'sukses',
            $berubah === []
                ? 'Tidak ada perubahan yang disimpan.'
                : count($berubah).' informasi diperbarui dan langsung tampil di website.'
        );
    }
}
