@php
    // Animasi logo banner profil (lihat penjelasan di bawah).
    $adaAnimasi = is_file(public_path('images/profil/latar-profil.mp4'));
@endphp
{{-- Navbar transparan di atas animasi, menjadi putih saat digulir (sama seperti beranda) --}}
<x-layouts.publik judul="Profil Perusahaan" deskripsi="Profil {{ $situs->get('nama_perusahaan') }}, penyedia layanan internet JNET."
                  :kepala-transparan="$adaAnimasi ? 'terang' : true">
    @php
        // Animasi logo di banner profil: public/images/profil/latar-profil.mp4
        // (+ latar-profil.jpg sebagai gambar diam). Jika video belum ada,
        // banner memakai latar navy dengan judul teks seperti halaman lain.
        $animasiVideo = 'images/profil/latar-profil.mp4';
        $animasiDiam = 'images/profil/latar-profil.jpg';
        $versi = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');
    @endphp

    @if ($adaAnimasi)
        {{-- Animasi berisi logo dan nama perusahaan; judul tetap ada untuk pembaca layar dan Google. --}}
        <section class="banner-profil">
            <h1 class="visually-hidden">Profil perusahaan – {{ $situs->get('nama_perusahaan') }}</h1>
            <video class="banner-profil__video" autoplay muted loop playsinline preload="auto" aria-hidden="true" tabindex="-1"
                   data-animasi-hiasan
                   @if (is_file(public_path($animasiDiam))) poster="{{ $versi($animasiDiam) }}" @endif>
                <source src="{{ $versi($animasiVideo) }}" type="video/mp4">
            </video>
        </section>
    @else
        <x-kepala-halaman judul="Profil perusahaan" :lead="$situs->get('nama_perusahaan')" />
    @endif

    <section class="uk-section">
        <div class="uk-container">
            <div class="profil uk-grid uk-grid-large" uk-grid>
            <div class="uk-width-1-1 uk-width-3-5@m profil__deskripsi">
                @unless ($adaAnimasi)
                    <img class="profil__logo" src="{{ asset(config('jnet.brand.logo')) }}" alt="Logo JNET" width="252" height="96">
                @endunless
                @if ($situs->ada('deskripsi_usaha'))
                    <p>{!! nl2br(e($situs->get('deskripsi_usaha'))) !!}</p>
                @else
                    <p class="kosong">Profil perusahaan sedang dilengkapi.</p>
                @endif
            </div>

            <div class="uk-width-1-1 uk-width-2-5@m">
                <x-info-kontak class="profil__kontak" judul="Kantor dan kontak" />
            </div>
            </div>
        </div>
    </section>
</x-layouts.publik>
