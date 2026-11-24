<?php

namespace Tests\Feature;

use Database\Seeders\PaketInternetSeeder;
use Database\Seeders\SiteSettingSeeder;
use Tests\RefreshDatabaseAman;
use Tests\TestCase;

/**
 * Revisi mitra: header Content-Security-Policy untuk melindungi dari XSS.
 */
class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabaseAman;

    protected function setUp(): void
    {
        parent::setUp();

        (new PaketInternetSeeder)->run();
        (new SiteSettingSeeder)->run();
    }

    private function nonceDari(string $csp): string
    {
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([A-Za-z0-9+\\/=]+)'/", $csp);
        preg_match("/'nonce-([A-Za-z0-9+\\/=]+)'/", $csp, $cocok);

        return $cocok[1];
    }

    public function test_csp_mengunci_skrip_dengan_nonce_yang_sama_dengan_skrip_inline(): void
    {
        $respons = $this->get('/')->assertOk();
        $csp = (string) $respons->headers->get('Content-Security-Policy');
        $nonce = $this->nonceDari($csp);

        foreach (["default-src 'self'", "object-src 'none'", "base-uri 'self'", "form-action 'self'", "frame-ancestors 'none'"] as $aturan) {
            $this->assertStringContainsString($aturan, $csp);
        }
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);

        $respons->assertSee('<script nonce="'.$nonce.'">document.documentElement.classList.add', false);
    }

    public function test_nonce_berbeda_di_setiap_permintaan(): void
    {
        $pertama = $this->nonceDari((string) $this->get('/paket')->headers->get('Content-Security-Policy'));
        $kedua = $this->nonceDari((string) $this->get('/paket')->headers->get('Content-Security-Policy'));

        $this->assertNotSame($pertama, $kedua);
    }

    public function test_sumber_luar_yang_dipakai_website_diizinkan(): void
    {
        $csp = (string) $this->get('/masuk')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://challenges.cloudflare.com', $csp);
        $this->assertStringContainsString('frame-src https://challenges.cloudflare.com', $csp);
        $this->assertStringContainsString('https://unpkg.com', $csp);
        $this->assertStringContainsString('https://tile.openstreetmap.org', $csp);
    }

    public function test_https_memaksa_sumber_http_dimuat_lewat_https(): void
    {
        $this->assertStringNotContainsString('upgrade-insecure-requests', (string) $this->get('/')->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('upgrade-insecure-requests', (string) $this->get('https://localhost/')->headers->get('Content-Security-Policy'));
    }

    public function test_halaman_error_juga_dilindungi(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertHeader('Content-Security-Policy');
    }
}
