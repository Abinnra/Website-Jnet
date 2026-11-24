<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Support\Captcha;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\RefreshDatabaseAman;
use Tests\TestCase;

/**
 * Revisi mitra: captcha Cloudflare Turnstile di form Daftar, Masuk pelanggan, dan Masuk admin.
 */
class CaptchaTest extends TestCase
{
    use RefreshDatabaseAman;

    private const TOKEN = 'token-dari-widget';

    protected function setUp(): void
    {
        parent::setUp();

        (new SiteSettingSeeder)->run();

        config([
            'services.turnstile.aktif' => true,
            'services.turnstile.site_key' => 'kunci-situs-uji',
            'services.turnstile.secret_key' => 'kunci-rahasia-uji',
        ]);
    }

    private function cloudflareMenjawab(bool $lolos): void
    {
        Http::fake([
            Captcha::ALAMAT_VERIFIKASI => Http::response(['success' => $lolos, 'error-codes' => $lolos ? [] : ['invalid-input-response']]),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function dataDaftar(array $tambahan = []): array
    {
        return ['email' => 'calon@contoh.test', 'password' => 'rahasiaUji123', 'password_confirmation' => 'rahasiaUji123'] + $tambahan;
    }

    public function test_widget_captcha_tampil_di_form_daftar_dan_masuk(): void
    {
        foreach (['/daftar', '/masuk', '/admin/masuk'] as $alamat) {
            $this->get($alamat)
                ->assertOk()
                ->assertSee('class="cf-turnstile', false)
                ->assertSee('data-sitekey="kunci-situs-uji"', false)
                ->assertSee('data-language="id"', false)
                ->assertSee(Captcha::ALAMAT_SKRIP, false);
        }
    }

    public function test_daftar_tanpa_captcha_ditolak_tanpa_menghubungi_cloudflare(): void
    {
        Http::fake();

        $this->post('/daftar', $this->dataDaftar())
            ->assertSessionHasErrors([Captcha::NAMA_ISIAN => 'Selesaikan verifikasi keamanan (captcha) terlebih dahulu.']);

        $this->assertSame(0, User::count());
        Http::assertNothingSent();
    }

    public function test_daftar_dengan_captcha_sah_berhasil_dan_token_diperiksa_ke_cloudflare(): void
    {
        $this->cloudflareMenjawab(true);

        $this->post('/daftar', $this->dataDaftar([Captcha::NAMA_ISIAN => self::TOKEN]))->assertRedirect('/akun');

        $this->assertAuthenticated('web');
        Http::assertSent(fn (Request $permintaan) => $permintaan->url() === Captcha::ALAMAT_VERIFIKASI
            && $permintaan['secret'] === 'kunci-rahasia-uji'
            && $permintaan['response'] === self::TOKEN);
    }

    public function test_token_yang_ditolak_cloudflare_menggagalkan_login_walaupun_sandi_benar(): void
    {
        $this->cloudflareMenjawab(false);
        $pelanggan = User::factory()->create();

        $this->post('/masuk', ['email' => $pelanggan->email, 'password' => 'password', Captcha::NAMA_ISIAN => self::TOKEN])
            ->assertSessionHasErrors([Captcha::NAMA_ISIAN => 'Verifikasi keamanan (captcha) gagal. Muat ulang halaman, lalu coba lagi.']);

        $this->assertGuest('web');
    }

    public function test_cloudflare_tidak_dapat_dihubungi_dianggap_gagal(): void
    {
        Http::fake(fn () => throw new ConnectionException('Waktu habis'));
        Admin::create(['nama' => 'Admin Uji', 'username' => 'admin_uji', 'password_hash' => 'rahasia-uji-123']);

        $this->post('/admin/masuk', ['username' => 'admin_uji', 'password' => 'rahasia-uji-123', Captcha::NAMA_ISIAN => self::TOKEN])
            ->assertSessionHasErrors(Captcha::NAMA_ISIAN);

        $this->assertGuest('admin');
    }

    public function test_login_admin_dengan_captcha_sah_berhasil(): void
    {
        $this->cloudflareMenjawab(true);
        Admin::create(['nama' => 'Admin Uji', 'username' => 'admin_uji', 'password_hash' => 'rahasia-uji-123']);

        $this->post('/admin/masuk', ['username' => 'admin_uji', 'password' => 'rahasia-uji-123', Captcha::NAMA_ISIAN => self::TOKEN])
            ->assertRedirect('/admin');

        $this->assertAuthenticated('admin');
    }

    public function test_captcha_dapat_dimatikan_lewat_konfigurasi(): void
    {
        config(['services.turnstile.aktif' => false]);
        Http::fake();
        $pelanggan = User::factory()->create();

        $this->get('/masuk')->assertDontSee('cf-turnstile', false)->assertDontSee(Captcha::ALAMAT_SKRIP, false);
        $this->post('/masuk', ['email' => $pelanggan->email, 'password' => 'password'])->assertRedirect('/akun');

        Http::assertNothingSent();
    }

    public function test_tanpa_kunci_di_lokal_memakai_kunci_uji_dan_di_produksi_menampilkan_pengingat(): void
    {
        config(['services.turnstile.site_key' => null, 'services.turnstile.secret_key' => null]);

        $this->assertTrue(Captcha::aktif());
        $this->assertSame(Captcha::KUNCI_SITUS_UJI, Captcha::kunciSitus());

        $this->app->detectEnvironment(fn () => 'production');

        $this->assertFalse(Captcha::aktif());
        $this->assertTrue(Captcha::kunciProduksiBelumDiisi());

        $admin = Admin::create(['nama' => 'Admin Uji', 'username' => 'admin_uji', 'password_hash' => 'rahasia-uji-123']);
        // Dashboard menampilkan pengingat "Captcha belum aktif".
        $this->actingAs($admin, 'admin')->get('/admin')
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard')->where('captchaBelumAktif', true));
    }
}
