<?php

namespace Tests\Feature;

use App\Enums\JenisJadwal;
use App\Enums\KeputusanHasil;
use App\Enums\StatusJadwal;
use App\Enums\StatusPengajuan;
use App\Models\Admin;
use App\Models\HasilLayanan;
use App\Models\JadwalLayanan;
use App\Models\Kecamatan;
use App\Models\KotaKabupaten;
use App\Models\PaketInternet;
use App\Models\RegistrasiPelanggan;
use App\Models\User;
use Database\Seeders\PaketInternetSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Routing\Route as RouteItem;
use Illuminate\Support\Facades\Route;
use Tests\RefreshDatabaseAman;
use Tests\TestCase;

/**
 * Tahap 6 — Matriks hak akses untuk SEMUA route aplikasi.
 *
 * Test ini membaca daftar route langsung dari Laravel. Jika nanti ada route
 * baru yang belum dikelompokkan di bawah, test gagal sehingga hak aksesnya
 * harus diputuskan dengan sengaja.
 */
class HakAksesRouteTest extends TestCase
{
    use RefreshDatabaseAman;

    /** Halaman yang boleh dibuka siapa saja. */
    private const PUBLIK = ['beranda', 'paket', 'jangkauan', 'alur', 'profil', 'kontak', 'kemitraan', 'bayar-tagihan', 'cari', 'sitemap', 'robots'];

    /** Hanya untuk yang belum masuk. */
    private const TAMU_PELANGGAN = ['daftar', 'daftar.store', 'login', 'login.store', 'login.google', 'login.google.callback'];

    private const TAMU_ADMIN = ['admin.login', 'admin.login.store'];

    /** Hanya pelanggan yang sudah masuk. */
    private const PELANGGAN = ['akun', 'akun.sandi.edit', 'akun.sandi.update', 'logout', 'pengajuan.create', 'pengajuan.store', 'pengajuan.show'];

    private Admin $admin;

    private User $pelanggan;

    private array $parameter;

    protected function setUp(): void
    {
        parent::setUp();

        (new PaketInternetSeeder)->run();
        (new SiteSettingSeeder)->run();

        $this->admin = Admin::create(['nama' => 'Admin Uji', 'username' => 'admin_uji', 'password_hash' => 'rahasia-uji-123']);
        $this->pelanggan = User::factory()->create();

        $paket = PaketInternet::first();
        $registrasi = new RegistrasiPelanggan([
            'nama_pelanggan' => 'Pemohon Uji', 'no_whatsapp' => '6281234567890',
            'alamat_pemasangan' => 'Jl. Uji No. 1', 'kecamatan' => 'Mayangan',
        ]);
        $registrasi->id_user = $this->pelanggan->id_user;
        $registrasi->id_paket = $paket->id_paket;
        $registrasi->harga_saat_pengajuan = $paket->harga;
        $registrasi->status_pengajuan = StatusPengajuan::Layak;
        $registrasi->save();

        $jadwal = new JadwalLayanan(['tanggal' => today()->toDateString(), 'waktu_mulai' => '09:00']);
        $jadwal->id_registrasi = $registrasi->id_registrasi;
        $jadwal->id_admin = $this->admin->id_admin;
        $jadwal->jenis_jadwal = JenisJadwal::Survei;
        $jadwal->status_jadwal = StatusJadwal::Selesai;
        $jadwal->save();

        $hasil = new HasilLayanan(['catatan' => 'uji']);
        $hasil->id_jadwal = $jadwal->id_jadwal;
        $hasil->id_admin = $this->admin->id_admin;
        $hasil->hasil = KeputusanHasil::Layak;
        $hasil->save();

        $this->parameter = [
            'registrasi' => $registrasi->id_registrasi,
            'paket' => $paket->id_paket,
            'jadwal' => $jadwal->id_jadwal,
            'hasil' => $hasil->id_hasil,
            'kota' => KotaKabupaten::where('nama', 'Kota Probolinggo')->value('id_kota_kabupaten'),
            'kecamatan' => Kecamatan::where('nama', 'Mayangan')->value('id_kecamatan'),
        ];
    }

    public function test_setiap_route_sudah_dikelompokkan_dan_memakai_middleware_yang_benar(): void
    {
        $belumDikelompokkan = [];

        foreach ($this->routeAplikasi() as $nama => $route) {
            $middleware = $route->gatherMiddleware();

            match (true) {
                in_array($nama, self::PUBLIK, true) => $this->assertEmpty(array_filter($middleware, fn ($m) => str_starts_with($m, 'auth')), "$nama seharusnya publik"),
                in_array($nama, self::TAMU_PELANGGAN, true) => $this->assertContains('guest:web', $middleware, $nama),
                in_array($nama, self::TAMU_ADMIN, true) => $this->assertContains('guest:admin', $middleware, $nama),
                in_array($nama, self::PELANGGAN, true) => $this->assertContains('auth:web', $middleware, $nama),
                str_starts_with($nama, 'admin.') => $this->assertContains('auth:admin', $middleware, $nama),
                default => $belumDikelompokkan[] = $nama,
            };
        }

        $this->assertSame([], $belumDikelompokkan, 'Route baru belum ditentukan hak aksesnya.');
    }

    public function test_route_bawaan_penyajian_file_privat_dimatikan(): void
    {
        $this->assertFalse(Route::has('storage.local'));
        $this->assertFalse(Route::has('storage.local.upload'));
    }

    public function test_tamu_dan_pelanggan_ditolak_di_semua_route_admin(): void
    {
        foreach ($this->routeAdmin() as $nama => [$metode, $url]) {
            $this->flushSession();
            $this->call($metode, $url)->assertRedirect('/admin/masuk');

            $this->flushSession();
            $this->actingAs($this->pelanggan, 'web')->call($metode, $url)->assertRedirect('/admin/masuk');
            $this->app['auth']->forgetGuards();
        }

        $this->assertSame(1, HasilLayanan::count());
        $this->assertSame(1, JadwalLayanan::count());
        $this->assertSame(2, KotaKabupaten::count());
        $this->assertSame(29, Kecamatan::count());
    }

    public function test_tamu_dan_admin_ditolak_di_semua_route_pelanggan(): void
    {
        foreach (self::PELANGGAN as $nama) {
            [$metode, $url] = $this->permintaan($this->routeAplikasi()[$nama]);

            $this->flushSession();
            $this->call($metode, $url)->assertRedirect('/masuk');

            $this->flushSession();
            $this->actingAs($this->admin, 'admin')->call($metode, $url)->assertRedirect('/masuk');
            $this->app['auth']->forgetGuards();
        }

        $this->assertSame(1, RegistrasiPelanggan::count());
    }

    public function test_admin_dapat_membuka_semua_halaman_admin(): void
    {
        foreach ($this->routeAdmin() as $nama => [$metode, $url]) {
            if ($metode !== 'GET' || $nama === 'admin.hasil.create' || $nama === 'admin.jadwal.edit' || $nama === 'admin.hasil.dokumentasi') {
                continue; // Halaman ini bergantung pada status data, sudah diuji di test fiturnya.
            }

            $this->actingAs($this->admin, 'admin')->get($url)->assertOk();
        }
    }

    public function test_pengunjung_dapat_membuka_semua_halaman_publik(): void
    {
        foreach (self::PUBLIK as $nama) {
            $this->get(route($nama))->assertOk();
        }
    }

    // ---------- Bantuan ----------

    /** @return array<string, RouteItem> */
    private function routeAplikasi(): array
    {
        $hasil = [];
        foreach (Route::getRoutes() as $route) {
            if ($route->getName() && ! str_starts_with($route->getName(), 'storage.')) {
                $hasil[$route->getName()] = $route;
            }
        }

        return $hasil;
    }

    /** @return array<string, array{0: string, 1: string}> */
    private function routeAdmin(): array
    {
        $hasil = [];
        foreach ($this->routeAplikasi() as $nama => $route) {
            if (str_starts_with($nama, 'admin.') && ! in_array($nama, self::TAMU_ADMIN, true)) {
                $hasil[$nama] = $this->permintaan($route);
            }
        }

        return $hasil;
    }

    /** @return array{0: string, 1: string} */
    private function permintaan(RouteItem $route): array
    {
        $parameter = array_intersect_key($this->parameter, array_flip($route->parameterNames()));

        return [$route->methods()[0], route($route->getName(), $parameter)];
    }
}
