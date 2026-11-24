<?php

namespace Tests\Feature;

use App\Enums\StatusPengajuan;
use App\Models\Admin;
use App\Models\PaketInternet;
use App\Models\RegistrasiPelanggan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\RefreshDatabaseAman;
use Tests\TestCase;

/**
 * Registrasi, login, logout, dan pemisahan hak akses pelanggan dan admin.
 */
class AutentikasiTest extends TestCase
{
    use RefreshDatabaseAman;

    private function buatAdmin(): Admin
    {
        return Admin::create([
            'nama' => 'Admin Uji',
            'username' => 'admin_uji',
            'password_hash' => 'rahasia-uji-123',
        ]);
    }

    // ---------- Registrasi ----------

    public function test_halaman_daftar_dapat_dibuka(): void
    {
        $this->get('/daftar')->assertOk()->assertSee('Buat akun');
    }

    public function test_kolom_kata_sandi_memiliki_tombol_lihat_sandi(): void
    {
        // Halaman daftar hanya punya satu kolom kata sandi (tidak lagi diminta dua kali).
        foreach (['/masuk' => 1, '/daftar' => 1, '/admin/masuk' => 1] as $alamat => $jumlah) {
            $isi = $this->get($alamat)->assertOk()->getContent();

            // Tombol bertipe button (bukan submit), terhubung ke kolomnya, tersembunyi sampai JavaScript aktif.
            $this->assertSame($jumlah, substr_count($isi, 'data-lihat-sandi'), $alamat);
            $this->assertStringContainsString('aria-controls="field-password" aria-pressed="false"', $isi);
            $this->assertStringContainsString('aria-label="Tampilkan kata sandi"', $isi);
            // Kolom sandi tetap bertipe password dan tidak pernah diisi ulang dari server.
            $this->assertMatchesRegularExpression('/id="field-password"\s+name="password"\s+type="password"/', $isi);
        }
    }

    public function test_halaman_masuk_menampilkan_logo_ke_beranda_dan_poin_sesuai_area(): void
    {
        $this->get('/masuk')->assertOk()
            ->assertSee('class="auth__logo" href="'.route('beranda').'"', false)
            ->assertSee('Pantau status pengajuan dan jadwal pemasangan')
            ->assertDontSee('Atur jadwal dan catat hasil pemasangan');

        $this->get('/admin/masuk')->assertOk()
            ->assertSee('Panel admin JNET')
            ->assertSee('Atur jadwal dan catat hasil pemasangan')
            ->assertDontSee('Pantau status pengajuan dan jadwal pemasangan');
    }

    public function test_registrasi_berhasil_menyimpan_hash_dan_langsung_login(): void
    {
        // Kata sandi cukup diisi sekali; email dikenali pengelola kata sandi browser.
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('autocomplete="username"', false)
            ->assertDontSee('name="password_confirmation"', false);

        $response = $this->post('/daftar', [
            'email' => '  Pelanggan@Example.com ',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect('/akun');
        $user = User::first();
        $this->assertSame('pelanggan@example.com', $user->email);
        $this->assertNotSame('rahasia123', $user->password_hash);
        $this->assertTrue(Hash::check('rahasia123', $user->password_hash));
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertGuest('admin');
    }

    public function test_registrasi_menolak_email_yang_sudah_terdaftar_tanpa_melihat_huruf_besar(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->from('/daftar')->post('/daftar', [
            'email' => 'BUDI@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/daftar')->assertSessionHasErrors(['email' => 'Email sudah terdaftar.']);

        $this->assertSame(1, User::count());
    }

    public function test_registrasi_memvalidasi_kata_sandi_dengan_pesan_bahasa_indonesia(): void
    {
        $this->post('/daftar', [
            'email' => 'baru@example.com',
            'password' => 'pendek',
        ])->assertSessionHasErrors(['password' => 'Kata sandi minimal 8 karakter.']);

        $this->post('/daftar', [
            'email' => 'baru@example.com',
            'password' => 'hanyahuruf',
        ])->assertSessionHasErrors(['password' => 'Kata sandi harus mengandung minimal satu angka.']);

        $this->post('/daftar', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'Email wajib diisi.']);

        $this->assertSame(0, User::count());
    }

    // ---------- Login dan logout pelanggan ----------

    public function test_login_pelanggan_berhasil_dan_sesi_diganti(): void
    {
        $user = User::factory()->create(['email' => 'siti@example.com']);

        $this->post('/masuk', ['email' => 'SITI@example.com', 'password' => 'password'])
            ->assertRedirect('/akun');

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_login_pelanggan_gagal_dengan_pesan_umum(): void
    {
        User::factory()->create(['email' => 'siti@example.com']);

        $this->from('/masuk')->post('/masuk', ['email' => 'siti@example.com', 'password' => 'salah'])
            ->assertRedirect('/masuk')
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        // Email yang tidak terdaftar mendapat pesan yang sama persis.
        $this->post('/masuk', ['email' => 'tidakada@example.com', 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest('web');
    }

    public function test_login_dikunci_setelah_lima_percobaan_gagal(): void
    {
        User::factory()->create(['email' => 'siti@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/masuk', ['email' => 'siti@example.com', 'password' => 'salah']);
        }

        $this->post('/masuk', ['email' => 'siti@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertStringContainsString('Terlalu banyak percobaan masuk', session('errors')->first('email'));
        $this->assertGuest('web');
    }

    public function test_logout_pelanggan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->post('/keluar')->assertRedirect('/');
        $this->assertGuest('web');
    }

    public function test_logout_hanya_menerima_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->get('/keluar')->assertMethodNotAllowed();
    }

    // ---------- Area pelanggan ----------

    public function test_akun_hanya_untuk_pelanggan_yang_login(): void
    {
        $this->get('/akun')->assertRedirect('/masuk');
    }

    public function test_pelanggan_yang_sudah_login_diarahkan_dari_halaman_masuk(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->get('/masuk')->assertRedirect('/akun');
        $this->actingAs($user, 'web')->get('/daftar')->assertRedirect('/akun');
    }

    public function test_akun_hanya_menampilkan_pengajuan_milik_sendiri(): void
    {
        $paket = PaketInternet::create([
            'nama_paket' => 'Paket 25 Mbps', 'kecepatan_mbps' => 25, 'harga' => 150000,
            'biaya_pemasangan' => 0, 'status_aktif' => true,
        ]);
        $saya = User::factory()->create();
        $oranglain = User::factory()->create();

        $milikSaya = $this->buatPengajuan($saya, $paket, 'Pemohon Saya');
        $this->buatPengajuan($oranglain, $paket, 'Pemohon Lain');

        // Nomor yang tampil ke pelanggan hanya tanggal pesan (sama untuk keduanya), jadi dicek lewat tautannya.
        $this->actingAs($saya, 'web')->get('/akun')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('pengajuan', 1)
                ->where('pengajuan.0.tautan', route('pengajuan.show', $milikSaya->id_registrasi))
                ->where('pengajuan.0.status.label', 'Baru'));
    }

    public function test_akun_menampilkan_keadaan_kosong(): void
    {
        // Tanpa pengajuan, Pages/Akun/Index.vue menampilkan "Anda belum memiliki pengajuan" beserta tombol ajukan.
        $this->actingAs(User::factory()->create(), 'web')->get('/akun')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Akun/Index')->has('pengajuan', 0));
    }

    // ---------- Admin ----------

    public function test_login_admin_berhasil(): void
    {
        $admin = $this->buatAdmin();

        $this->post('/admin/masuk', ['username' => 'admin_uji', 'password' => 'rahasia-uji-123'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
    }

    public function test_login_admin_gagal_dengan_pesan_umum(): void
    {
        $this->buatAdmin();

        $this->post('/admin/masuk', ['username' => 'admin_uji', 'password' => 'salah'])
            ->assertSessionHasErrors(['username' => 'Username atau kata sandi salah.']);

        $this->assertGuest('admin');
    }

    public function test_akun_pelanggan_tidak_bisa_login_di_halaman_admin(): void
    {
        User::factory()->create(['email' => 'pelanggan@example.com']);

        $this->post('/admin/masuk', ['username' => 'pelanggan@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $this->assertGuest('admin');
    }

    public function test_tamu_diarahkan_ke_login_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/masuk');
    }

    public function test_pelanggan_yang_login_tetap_ditolak_di_panel_admin(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->get('/admin')
            ->assertRedirect('/admin/masuk');
    }

    public function test_admin_yang_login_tidak_dianggap_pelanggan(): void
    {
        $this->actingAs($this->buatAdmin(), 'admin')
            ->get('/akun')
            ->assertRedirect('/masuk');
    }

    public function test_admin_yang_sudah_login_diarahkan_dari_halaman_masuk_admin(): void
    {
        $this->actingAs($this->buatAdmin(), 'admin')->get('/admin/masuk')->assertRedirect('/admin');
    }

    public function test_logout_admin(): void
    {
        $this->actingAs($this->buatAdmin(), 'admin')->post('/admin/keluar')->assertRedirect('/admin/masuk');
        $this->assertGuest('admin');
    }

    public function test_dashboard_menghitung_pengajuan_dari_database(): void
    {
        $paket = PaketInternet::create([
            'nama_paket' => 'Paket 15 Mbps', 'kecepatan_mbps' => 15, 'harga' => 125000,
            'biaya_pemasangan' => 0, 'status_aktif' => true,
        ]);
        $user = User::factory()->create();

        $this->buatPengajuan($user, $paket, 'A');
        $this->buatPengajuan($user, $paket, 'B');
        $this->buatPengajuan($user, $paket, 'C', StatusPengajuan::Ditolak);

        $response = $this->actingAs($this->buatAdmin(), 'admin')->get('/admin')->assertOk();

        // Ringkasan per status, dikunci dengan label status yang tampil di dashboard.
        $ringkasan = collect($response->inertiaProps('ringkasanStatus'))
            ->mapWithKeys(fn (array $baris) => [$baris['status']['label'] => $baris['jumlah']]);

        $this->assertSame(3, $response->inertiaProps('totalPengajuan'));
        $this->assertSame(2, $ringkasan[StatusPengajuan::Baru->label()]);
        $this->assertSame(1, $ringkasan[StatusPengajuan::Ditolak->label()]);
        $this->assertSame(0, $ringkasan[StatusPengajuan::Selesai->label()]);
        // 9 status dikurangi 2 status alur lama (survei) yang tidak punya pengajuan.
        $this->assertCount(7, $ringkasan);
        $response->assertInertia(fn (Assert $page) => $page
            ->where('jadwal.kosong', 'Belum ada jadwal pemasangan.')
            ->has('jadwal.daftar', 0));
    }

    private function buatPengajuan(User $user, PaketInternet $paket, string $nama, StatusPengajuan $status = StatusPengajuan::Baru): RegistrasiPelanggan
    {
        $registrasi = new RegistrasiPelanggan([
            'nama_pelanggan' => $nama,
            'no_whatsapp' => '6281234567890',
            'alamat_pemasangan' => 'Jl. Uji No. 1',
            'kecamatan' => 'Mayangan',
        ]);
        $registrasi->id_user = $user->id_user;
        $registrasi->id_paket = $paket->id_paket;
        $registrasi->harga_saat_pengajuan = $paket->harga;
        $registrasi->status_pengajuan = $status;
        $registrasi->save();

        return $registrasi;
    }
}
