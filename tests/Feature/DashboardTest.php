<?php

namespace Tests\Feature;

use App\Enums\JenisJadwal;
use App\Enums\StatusJadwal;
use App\Enums\StatusPengajuan;
use App\Models\Admin;
use App\Models\JadwalLayanan;
use App\Models\PaketInternet;
use App\Models\RegistrasiPelanggan;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingService;
use Database\Seeders\PaketInternetSeeder;
use Database\Seeders\SiteSettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\RefreshDatabaseAman;
use Tests\TestCase;

/**
 * Use Case: Melihat Dashboard Admin. Semua angka berasal dari database.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabaseAman;

    private Admin $admin;

    private PaketInternet $paket;

    protected function setUp(): void
    {
        parent::setUp();

        (new PaketInternetSeeder)->run();
        (new SiteSettingSeeder)->run();

        $this->admin = Admin::create(['nama' => 'Admin Uji', 'username' => 'admin_uji', 'password_hash' => 'rahasia-uji-123']);
        $this->paket = PaketInternet::first();
    }

    public function test_angka_tindakan_dihitung_dari_database(): void
    {
        $this->buatPengajuan(StatusPengajuan::Baru, 'Pemohon Baru Satu');
        $this->buatPengajuan(StatusPengajuan::Baru, 'Pemohon Baru Dua');
        $this->buatPengajuan(StatusPengajuan::Diverifikasi);
        // Data alur lama (menunggu pemasangan ulang) tetap dihitung siap dijadwalkan.
        $this->buatPengajuan(StatusPengajuan::Layak);

        $hariIni = $this->buatPengajuan(StatusPengajuan::PemasanganDijadwalkan, 'Pemohon Hari Ini');
        $this->buatJadwal($hariIni, today()->toDateString());

        $terlewat = $this->buatPengajuan(StatusPengajuan::PemasanganDijadwalkan);
        $this->buatJadwal($terlewat, today()->subDays(3)->toDateString());

        $selesai = $this->buatPengajuan(StatusPengajuan::Selesai);
        $this->buatJadwal($selesai, today()->subDays(5)->toDateString(), StatusJadwal::Selesai);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin')->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('totalPengajuan', 7)
            ->where('tindakan.0.angka', 2) // menunggu verifikasi
            ->where('tindakan.1.angka', 2) // siap dijadwalkan: 1 terverifikasi + 1 data lama
            ->where('tindakan.1.teks', 'Data sudah diverifikasi dan menunggu jadwal pemasangan.')
            ->where('tindakan.2.angka', 1) // kunjungan hari ini
            ->where('tindakan.3.angka', 1) // hasil belum dicatat
            ->where('tindakan.3.peringatan', true)
            ->where('jadwal.judul', 'Pemasangan mendatang')
            ->has('jadwal.daftar', 1));

        $this->assertHalamanInertia($response, 'Admin/Dashboard', 'Pemohon Baru Satu', 'Pemohon Baru Dua', 'Hari ini', 'Pemohon Hari Ini');

        // Status alur lama hanya tampil di ringkasan selama masih ada pengajuannya.
        $labelStatus = array_column(array_column($response->inertiaProps('ringkasanStatus'), 'status'), 'label');
        $this->assertContains(StatusPengajuan::Layak->label(), $labelStatus);
        $this->assertNotContains(StatusPengajuan::SurveiDijadwalkan->label(), $labelStatus);
    }

    public function test_dashboard_kosong_tetap_tampil(): void
    {
        $this->actingAs($this->admin, 'admin')->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('totalPengajuan', 0)
                ->has('pengajuanBaru', 0)
                ->has('jadwal.daftar', 0)
                ->where('jadwal.kosong', 'Belum ada jadwal pemasangan.'));
    }

    public function test_pengingat_informasi_website_belum_lengkap(): void
    {
        $this->actingAs($this->admin, 'admin')->get('/admin')
            ->assertSee('Informasi website belum lengkap')
            ->assertSee('nomor whatsapp resmi');

        foreach (['alamat_kantor' => 'Jl. Uji', 'whatsapp' => '6281234567890', 'telepon' => '0335 1', 'email' => 'a@b.test', 'deskripsi_usaha' => 'Profil'] as $kunci => $nilai) {
            SiteSetting::where('nama_setting', $kunci)->update(['nilai_setting' => $nilai]);
        }
        app(SiteSettingService::class)->lupakan();

        $this->get('/admin')->assertDontSee('Informasi website belum lengkap');
    }

    private function buatPengajuan(StatusPengajuan $status, string $nama = 'Pemohon Uji'): RegistrasiPelanggan
    {
        $registrasi = new RegistrasiPelanggan([
            'nama_pelanggan' => $nama, 'no_whatsapp' => '6281234567890',
            'alamat_pemasangan' => 'Jl. Uji No. 1', 'kecamatan' => 'Mayangan',
        ]);
        $registrasi->id_user = User::factory()->create()->id_user;
        $registrasi->id_paket = $this->paket->id_paket;
        $registrasi->harga_saat_pengajuan = $this->paket->harga;
        $registrasi->status_pengajuan = $status;
        $registrasi->save();

        return $registrasi;
    }

    private function buatJadwal(RegistrasiPelanggan $registrasi, string $tanggal, StatusJadwal $status = StatusJadwal::Terjadwal): void
    {
        $jadwal = new JadwalLayanan(['tanggal' => $tanggal, 'waktu_mulai' => '09:00']);
        $jadwal->id_registrasi = $registrasi->id_registrasi;
        $jadwal->id_admin = $this->admin->id_admin;
        $jadwal->jenis_jadwal = JenisJadwal::Pemasangan;
        $jadwal->status_jadwal = $status;
        $jadwal->save();
    }
}
