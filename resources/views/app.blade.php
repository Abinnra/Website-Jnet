{{--
    Revisi (Vue): halaman dasar aplikasi Vue 3 (Inertia) untuk panel admin dan halaman akun pelanggan.
    Halaman publik tetap memakai layout Blade (components/layouts/publik.blade.php).

    - CSS UIkit, tema JNET, dan app.css dipakai bersama situs publik agar tombol, formulir, dan navbar akun
      tetap seragam; gaya khusus aplikasi ada di resources/css/aplikasi.css (dibundel Vite).
    - Skrip Vite membawa nonce CSP yang sama dengan skrip inline (lihat HeaderKeamanan).
    - Data halaman disisipkan Inertia sebagai <script type="application/json">, bukan skrip yang dijalankan.
    - Panel admin memakai viewport-fit=cover agar bilah navigasi bawahnya dapat menjauhi area aman layar
      (garis gestur, poni) lewat env(safe-area-inset-*). Halaman akun tetap memakai viewport biasa.
--}}
@php($versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1'))
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1{{ request()->routeIs('admin.*') ? ', viewport-fit=cover' : '' }}">
    <meta name="robots" content="noindex">
    <script nonce="{{ $cspNonce ?? '' }}">document.documentElement.classList.add('js');</script>
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans.woff2') }}" as="font" type="font/woff2" crossorigin>
    <x-ikon-situs />
    <link rel="stylesheet" href="{{ $versiAset('vendor/uikit/css/uikit.min.css') }}">
    <link rel="stylesheet" href="{{ $versiAset('css/tema.css') }}">
    <link rel="stylesheet" href="{{ $versiAset('css/uikit-jnet.css') }}">
    <link rel="stylesheet" href="{{ $versiAset('css/app.css') }}">
    @vite('resources/js/app.js')
    <x-inertia::head>
        <title>JNET</title>
    </x-inertia::head>
</head>
<body class="aplikasi">
    <x-inertia::app />
</body>
</html>
