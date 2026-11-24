<?php

namespace App\Http\Middleware;

use App\Enums\StatusPengajuan;
use App\Models\RegistrasiPelanggan;
use App\Services\SiteSettingService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revisi (Vue): panel admin dan halaman akun pelanggan dirender Vue 3 lewat Inertia.
 * Halaman publik tetap Blade (cepat dan mudah dibaca mesin pencari), sehingga middleware ini
 * hanya dipasang pada grup rute admin dan akun (lihat routes/web.php).
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * Pesan flash sesi Laravel yang diteruskan ke flash data Inertia (page.flash di Vue).
     *
     * @var list<string>
     */
    private const FLASH = ['sukses', 'gagal', 'pengajuan_baru', 'pesanWilayah'];

    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Controller tetap memakai ->with('sukses', ...). Nilainya diteruskan ke flash data Inertia, yang
     * tidak ikut tersimpan di riwayat browser, sehingga pesan tidak muncul lagi saat tombol Kembali ditekan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $flash = [];

        foreach (self::FLASH as $kunci) {
            if ($request->session()->has($kunci)) {
                $flash[$kunci] = $request->session()->get($kunci);
            }
        }

        if ($flash !== []) {
            Inertia::flash($flash);
        }

        return parent::handle($request, $next);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'aplikasi' => fn () => $request->routeIs('admin.*') ? $this->areaAdmin($request) : $this->areaAkun($request),
        ];
    }

    /**
     * Menu samping, admin yang login, dan tautan umum panel admin.
     *
     * @return array<string, mixed>
     */
    private function areaAdmin(Request $request): array
    {
        $admin = $request->user('admin');

        // [nama route, label, ikon, pola route yang membuat menu ini aktif, menu harian di bilah bawah HP]
        $menu = [
            ['admin.dashboard', 'Dashboard', 'dasbor', ['admin.dashboard'], true],
            ['admin.pengajuan.index', 'Pengajuan', 'pengajuan', ['admin.pengajuan.*'], true],
            ['admin.jadwal.index', 'Jadwal', 'jadwal', ['admin.jadwal.*', 'admin.hasil.*'], true],
            ['admin.paket.index', 'Paket internet', 'paket', ['admin.paket.*'], false],
            ['admin.wilayah.index', 'Wilayah layanan', 'wilayah', ['admin.wilayah.*'], false],
            ['admin.informasi.edit', 'Informasi website', 'informasi', ['admin.informasi.*'], false],
            ['admin.sandi-pelanggan.create', 'Sandi pelanggan', 'kunci', ['admin.sandi-pelanggan.*'], false],
        ];

        $menungguVerifikasi = RegistrasiPelanggan::query()
            ->where('status_pengajuan', StatusPengajuan::Baru->value)
            ->count();

        return [
            'area' => 'admin',
            'akun' => [
                'nama' => $admin?->nama,
                'username' => $admin?->username,
                'inisial' => self::inisial($admin?->nama),
            ],
            'menu' => array_map(fn (array $item) => [
                'label' => $item[1],
                'href' => route($item[0]),
                'ikon' => $item[2],
                'aktif' => $request->routeIs(...$item[3]),
                // Jumlah pengajuan yang menunggu verifikasi tampil di samping menu Pengajuan.
                'jumlah' => $item[0] === 'admin.pengajuan.index' && $menungguVerifikasi > 0 ? $menungguVerifikasi : null,
                // Di HP, menu harian tampil langsung di bilah navigasi bawah; sisanya di lembar "Lainnya".
                'utama' => $item[4],
            ], $menu),
            'tautan' => [
                'beranda' => route('admin.dashboard'),
                'logo' => asset(config('jnet.brand.logo')),
                'website' => route('beranda'),
                'keluar' => route('admin.logout'),
            ],
        ];
    }

    /**
     * Inisial admin (paling banyak dua huruf) untuk lingkaran akun di sidebar dan lembar "Lainnya".
     */
    private static function inisial(?string $nama): string
    {
        $kata = preg_split('/\s+/u', trim((string) $nama), -1, PREG_SPLIT_NO_EMPTY) ?: ['Admin'];

        return mb_strtoupper(implode('', array_map(fn (string $bagian) => mb_substr($bagian, 0, 1), array_slice($kata, 0, 2))));
    }

    /**
     * Navbar halaman akun pelanggan (sama seperti situs publik) dan tombol CS WhatsApp.
     *
     * @return array<string, mixed>
     */
    private function areaAkun(Request $request): array
    {
        $user = $request->user('web');
        $situs = app(SiteSettingService::class);

        return [
            'area' => 'akun',
            'akun' => ['email' => $user?->email],
            'menu' => [
                ['label' => 'Pengajuan saya', 'href' => route('akun'), 'aktif' => $request->routeIs('akun'), 'inertia' => true],
                ['label' => 'Paket', 'href' => route('paket'), 'aktif' => false, 'inertia' => false],
            ],
            'tautan' => [
                'beranda' => route('beranda'),
                'logo' => asset(config('jnet.brand.logo')),
                'keluar' => route('logout'),
            ],
            // Pesan pembuka menyertakan email akun agar admin langsung tahu akun mana yang bertanya.
            'cs' => [
                'tautan' => $situs->tautanWhatsapp('Halo admin JNET, saya pelanggan dengan email akun '.$user?->email.'. Saya ingin bertanya: '),
            ],
        ];
    }
}
