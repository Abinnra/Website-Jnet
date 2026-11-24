@props(['judul'])

@php
    $versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');
    $user = auth('web')->user();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $judul }} | JNET</title>
    <script nonce="{{ $cspNonce ?? '' }}">document.documentElement.classList.add('js');</script>
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans.woff2') }}" as="font" type="font/woff2" crossorigin>
    <x-ikon-situs />
    <x-aset-uikit />
    <link rel="stylesheet" href="{{ $versiAset('css/app.css') }}">
    <link rel="stylesheet" href="{{ $versiAset('css/panel.css') }}">
    <script src="{{ $versiAset('js/app.js') }}" defer></script>
    {{ $head ?? '' }}
</head>
<body class="area-akun">
    <a class="lewati" href="#konten">Langsung ke konten</a>

    <header class="kepala kepala--akun" data-kepala>
        <div class="uk-container kepala__isi">
            <a class="kepala__logo" href="{{ route('beranda') }}">
                <img src="{{ asset(config('jnet.brand.logo')) }}" alt="JNET – beranda" width="126" height="48">
            </a>

            <button class="kepala__tombol-menu" type="button" aria-expanded="false" aria-controls="menu-akun" data-tombol-menu>
                <span class="kepala__ikon-menu" aria-hidden="true"></span>
                <span>Menu</span>
            </button>

            <nav class="kepala__menu" id="menu-akun" aria-label="Menu akun">
                <ul class="menu">
                    <li><a class="menu__tautan" href="{{ route('akun') }}" @if (request()->routeIs('akun')) aria-current="page" @endif>Pengajuan saya</a></li>
                    <li><a class="menu__tautan" href="{{ route('paket') }}">Paket</a></li>
                </ul>
                <div class="kepala__akun">
                    <span class="kepala__email">{{ $user?->email }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="uk-button uk-button-default uk-button-small">Keluar</button>
                    </form>
                </div>
            </nav>
        </div>
    </header>

    <main id="konten" class="area-akun__isi">
        <div class="uk-container">
            <x-pesan-sesi />
            {{ $slot }}
        </div>
    </main>

    {{-- Pesan pembuka menyertakan email akun agar admin langsung tahu akun mana yang bertanya --}}
    <x-tombol-cs :pesan="'Halo admin JNET, saya pelanggan dengan email akun '.$user?->email.'. Saya ingin bertanya: '" />
</body>
</html>
