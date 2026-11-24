<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Revisi mitra: captcha Cloudflare Turnstile di form Daftar, Masuk pelanggan, dan Masuk admin.
    // Kunci gratis dibuat di dashboard Cloudflare > Turnstile untuk domain hosting.
    'turnstile' => [
        'aktif' => env('CAPTCHA_AKTIF', true),
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    // Revisi mitra: pelanggan dapat masuk/daftar dengan akun Google (Laravel Socialite).
    // Client ID dan Client Secret gratis dari Google Cloud Console > Google Auth Platform > Clients
    // (jenis "Web application"). Selama kosong, tombol "Masuk dengan Google" tidak ditampilkan.
    // Alamat callback relatif otomatis menjadi APP_URL + /masuk/google/callback (misalnya
    // https://domain-jnet/masuk/google/callback); alamat lengkap itu yang didaftarkan sebagai
    // "Authorized redirect URI" di Google. Jadi APP_URL di hosting wajib berisi domain https yang benar.
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/masuk/google/callback'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
