<?php

namespace App\Rules;

use App\Support\Captcha;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Token captcha Turnstile harus dinyatakan sah oleh Cloudflare.
 */
class LolosCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Captcha::verifikasi($value, request()->ip())) {
            $fail('Verifikasi keamanan (captcha) gagal. Muat ulang halaman, lalu coba lagi.');
        }
    }
}
