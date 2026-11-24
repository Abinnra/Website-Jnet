{{--
    Tata letak halaman error. Sengaja tidak memakai database, sesi, atau
    informasi website, agar tetap tampil walaupun database sedang bermasalah.
    Pesan teknis tidak pernah ditampilkan kepada pengunjung.
--}}
@php
    $versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');
    $areaAdmin = request()->is('admin', 'admin/*');
    $tautanUtama = $areaAdmin ? url('/admin') : url('/');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('judul') | JNET</title>
    <x-ikon-situs />
    <x-aset-uikit />
    <link rel="stylesheet" href="{{ $versiAset('css/app.css') }}">
</head>
<body class="halaman-error">
    <main class="halaman-error__isi">
        <a class="halaman-error__logo" href="{{ url('/') }}">
            <img src="{{ asset(config('jnet.brand.logo')) }}" alt="JNET – ke beranda" width="147" height="56">
        </a>
        <p class="halaman-error__kode">@yield('kode')</p>
        <h1 class="halaman-error__judul">@yield('judul')</h1>
        <p class="halaman-error__pesan">@yield('pesan')</p>
        <div class="halaman-error__aksi">
            @section('aksi')
                <a class="uk-button uk-button-primary" href="{{ $tautanUtama }}">{{ $areaAdmin ? 'Ke dashboard admin' : 'Ke beranda' }}</a>
            @show
        </div>
    </main>
</body>
</html>
