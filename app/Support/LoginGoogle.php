<?php

namespace App\Support;

/**
 * Masuk/daftar dengan akun Google (revisi mitra), memakai Laravel Socialite.
 *
 * Fitur ini aktif hanya jika GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET sudah diisi di .env.
 * Selama belum diisi, tombol Google tidak ditampilkan dan route-nya menjawab 404, sehingga
 * website tetap berjalan normal dengan login email dan kata sandi.
 */
class LoginGoogle
{
    public static function aktif(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }
}
