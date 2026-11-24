@props(['judul', 'area' => 'pelanggan'])

@php
    $versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');

    // Poin di sisi kiri: hanya fitur yang benar-benar ada di sistem.
    $manfaat = $area === 'admin'
        ? ['Periksa dan proses pengajuan pelanggan', 'Atur jadwal dan catat hasil pemasangan', 'Kelola paket dan informasi website']
        : ['Ajukan pemasangan secara online', 'Pantau status pengajuan dan jadwal pemasangan', 'Baca pesan dari admin JNET di akun Anda'];
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
</head>
<body class="auth auth--{{ $area }}">
    {{-- Latar: gradasi biru JNET, pola titik, dan garis serat optik (hiasan) --}}
    <x-hiasan-serat class="auth__serat" />

    <div class="auth__bingkai">
        <header class="auth__sisi">
            <a class="auth__logo" href="{{ route('beranda') }}">
                <img src="{{ asset(config('jnet.brand.logo')) }}" alt="JNET – kembali ke beranda" width="147" height="56">
            </a>

            @if ($area === 'admin')
                <p class="auth__slogan">Panel admin JNET</p>
            @else
                <p class="auth__slogan">Ajukan internet rumah, di Probolinggo maupun luar Probolinggo.</p>
            @endif

            <ul class="auth__manfaat">
                @foreach ($manfaat as $poin)
                    <li><x-ikon nama="centang" class="auth__ikon" /> {{ $poin }}</li>
                @endforeach
            </ul>
        </header>

        <main class="auth__utama">
            <div class="auth__kotak">
                <h1 class="auth__judul">{{ $judul }}</h1>

                <x-pesan-sesi />

                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Tombol CS hanya untuk halaman masuk/daftar pelanggan, tidak di halaman masuk admin --}}
    @if ($area === 'pelanggan')
        <x-tombol-cs pesan="Halo admin JNET, saya butuh bantuan terkait akun website JNET." />
    @endif
</body>
</html>
