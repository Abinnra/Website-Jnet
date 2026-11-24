@props(['judul' => null, 'deskripsi' => null, 'kepalaTransparan' => false, 'kepalaTransparanHp' => null, 'indeks' => true])

@php
    $namaPerusahaan = $situs->get('nama_perusahaan');
    // Nilai berupa array = menu bertingkat (dropdown). Revisi mitra: Profil perusahaan,
    // Kontak, dan Kemitraan (B2B) digabung di bawah satu menu "Profil".
    $menu = [
        'beranda' => 'Beranda',
        'paket' => 'Paket',
        'jangkauan' => 'Jangkauan',
        'alur' => 'Alur Berlangganan',
        'Profil' => [
            // label_hp: label di menu HP (speed dial), tempat ketiganya berada di bawah item "Profil".
            'profil' => ['label' => 'Profil Perusahaan', 'label_hp' => 'Profil JNET', 'keterangan' => 'Mengenal JNET dan layanan kami', 'ikon' => 'gedung'],
            'kontak' => ['label' => 'Kontak', 'keterangan' => 'Telepon, WhatsApp, dan alamat', 'ikon' => 'telepon'],
            'kemitraan' => ['label' => 'Kemitraan (B2B)', 'keterangan' => 'Kerja sama bisnis dengan direktur', 'ikon' => 'koper'],
        ],
    ];
    // Footer tetap menautkan setiap halaman secara langsung.
    $menuDatar = collect($menu)
        ->flatMap(fn ($isi, $kunci) => is_array($isi) ? array_map(fn (array $item) => $item['label'], $isi) : [$kunci => $isi])
        ->all();
    $versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');

    // SEO (revisi mitra): judul, deskripsi, alamat kanonik, pratinjau tautan, dan data terstruktur.
    $judulHalaman = ($judul ? $judul.' | ' : '').'JNET – '.$namaPerusahaan;
    $deskripsiHalaman = $deskripsi ?? \App\Support\Seo::DESKRIPSI_BAWAAN;
    $urlKanonik = url()->current();
    $kodeVerifikasiGoogle = config('jnet.seo.verifikasi_google');
    $gambarBagikan = \App\Support\Seo::gambarBagikan();
    $altGambarBagikan = 'JNET – internet rumah untuk Kota dan Kabupaten Probolinggo';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judulHalaman }}</title>
    <meta name="description" content="{{ $deskripsiHalaman }}">
    <link rel="canonical" href="{{ $urlKanonik }}">
    {{-- indeks=false (misalnya halaman hasil pencarian): tidak dimasukkan ke hasil mesin pencari --}}
    <meta name="robots" content="{{ $indeks ? 'index, follow' : 'noindex, follow' }}">
    <meta name="theme-color" content="#021D3A">
    @if ($kodeVerifikasiGoogle)
        <meta name="google-site-verification" content="{{ $kodeVerifikasiGoogle }}">
    @endif

    {{-- Open Graph: pratinjau saat tautan dibagikan di WhatsApp, Facebook, LinkedIn, dan lain-lain --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="JNET – {{ $namaPerusahaan }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="{{ $judulHalaman }}">
    <meta property="og:description" content="{{ $deskripsiHalaman }}">
    <meta property="og:url" content="{{ $urlKanonik }}">
    <meta property="og:image" content="{{ $gambarBagikan['url'] }}">
    @if ($gambarBagikan['jenis'])
        <meta property="og:image:type" content="{{ $gambarBagikan['jenis'] }}">
    @endif
    @if ($gambarBagikan['lebar'])
        <meta property="og:image:width" content="{{ $gambarBagikan['lebar'] }}">
        <meta property="og:image:height" content="{{ $gambarBagikan['tinggi'] }}">
    @endif
    <meta property="og:image:alt" content="{{ $altGambarBagikan }}">

    {{-- Twitter/X Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $judulHalaman }}">
    <meta name="twitter:description" content="{{ $deskripsiHalaman }}">
    <meta name="twitter:image" content="{{ $gambarBagikan['url'] }}">
    <meta name="twitter:image:alt" content="{{ $altGambarBagikan }}">

    <x-ikon-situs />
    <script type="application/ld+json">{!! \App\Support\Seo::json(\App\Support\Seo::dataTerstruktur($situs)) !!}</script>

    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans.woff2') }}" as="font" type="font/woff2" crossorigin>
    <script nonce="{{ $cspNonce ?? '' }}">document.documentElement.classList.add('js');</script>
    <x-aset-uikit />
    <link rel="stylesheet" href="{{ $versiAset('css/app.css') }}">
    <script src="{{ $versiAset('js/app.js') }}" defer></script>
</head>
<body>
    <a class="lewati" href="#konten">Langsung ke konten</a>

    {{-- kepalaTransparan: false, true/'gelap' (di atas foto gelap), atau 'terang' (di atas latar terang).
         kepalaTransparanHp: navbar juga transparan di HP. Bawaannya mengikuti kepalaTransparan, karena banner
         judul halaman selalu memberi ruang untuk navbar; beranda menentukannya sendiri (hanya bila semua
         banner punya versi HP tegak, sebab tulisan promo ada di dalam foto). --}}
    <header @class([
        'kepala',
        'kepala--transparan' => (bool) $kepalaTransparan,
        'kepala--di-terang' => $kepalaTransparan === 'terang',
        'kepala--transparan-hp' => (bool) ($kepalaTransparanHp ?? $kepalaTransparan),
    ]) data-kepala>
        <div class="uk-container kepala__isi">
            <a class="kepala__logo" href="{{ route('beranda') }}">
                <img src="{{ asset(config('jnet.brand.logo')) }}" alt="JNET – {{ $namaPerusahaan }}" width="126" height="48">
            </a>

            {{-- Revisi (HP saja): pojok kanan atas berisi kolom "Cari apa?" dan ikon CS headset polos, seperti IndiHome.
                 Tombol CS bulat yang melayang tidak ditampilkan di HP selama ikon ini ada (app.css). Di layar lebar
                 keduanya disembunyikan; tombol CS tetap melayang di kanan bawah. --}}
            @php
                $tautanCsKepala = $situs->tautanWhatsapp('Halo admin JNET, saya ingin bertanya tentang layanan internet JNET.');
            @endphp
            <div class="kepala__alat">
                <a class="kepala__cari" href="{{ route('cari') }}"><x-ikon nama="cari" /><span>Cari apa?</span></a>
                @if ($tautanCsKepala)
                    <a class="kepala__cs" href="{{ $tautanCsKepala }}" target="_blank" rel="noopener" aria-label="Chat CS JNET lewat WhatsApp">
                        <svg viewBox="0 0 48 48" width="30" height="30" aria-hidden="true" focusable="false">
                            <path d="M10 28v-5a14 14 0 0 1 28 0v5" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>
                            <rect x="5" y="21.5" width="9.5" height="14.5" rx="4.75" fill="currentColor"/>
                            <rect x="33.5" y="21.5" width="9.5" height="14.5" rx="4.75" fill="currentColor"/>
                            <path d="M38.25 35.5v.75c0 3.6-2.9 5.75-6.5 5.75H28" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                            <rect x="19.5" y="38.75" width="10" height="6.5" rx="3.25" fill="currentColor"/>
                        </svg>
                    </a>
                @endif
            </div>

            {{-- Revisi mitra: tombol menu HP berbentuk lambang JNET di pojok kanan bawah (lihat komponennya). --}}
            <x-tombol-menu-lambang />

            {{-- Revisi (23 November 2026): di HP menu tampil sebagai "speed dial" dari tombol Menu di pojok kanan atas,
                 yaitu ikon bulat dengan label yang muncul bergantian ke bawah (kepala__menu--dial, app.css). Ikon dan label
                 hanya dipakai di HP; --dial mengatur urutan munculnya. Di layar lebar menu tetap seperti biasa. --}}
            @php
                $ikonMenu = ['beranda' => 'beranda', 'paket' => 'paket', 'jangkauan' => 'jangkauan', 'alur' => 'alur'];
                $urutanDial = 0;
                // Urutan terakhir (baris Masuk/Daftar): menu muncul dari bawah, dari yang paling dekat tombol lambang.
                $urutanDialAkhir = count($menu) - (Route::has('login') ? 0 : 1);
            @endphp
            <nav class="kepala__menu kepala__menu--dial" id="menu-utama" aria-label="Menu utama" style="--dial-akhir: {{ $urutanDialAkhir }}">
                <ul class="menu">
                    @foreach ($menu as $kunci => $isi)
                        @if (is_array($isi))
                            @php
                                $idSubmenu = 'submenu-'.\Illuminate\Support\Str::slug($kunci);
                                $grupAktif = request()->routeIs(...array_keys($isi));
                            @endphp
                            {{-- Dropdown: dibuka dengan klik/ketuk, atau arahkan mouse di layar lebar (app.js).
                                 Tanpa JavaScript submenu terbuka saat disorot atau difokus. --}}
                            <li class="menu__grup" data-submenu style="--dial: {{ $urutanDial++ }}">
                                <button type="button" @class(['menu__tautan', 'menu__pemicu', 'menu__tautan--aktif' => $grupAktif])
                                        aria-expanded="false" aria-controls="{{ $idSubmenu }}">
                                    <span class="menu__ikon"><x-ikon nama="gedung" /></span><span class="menu__label">{{ $kunci }}</span><span class="menu__panah" aria-hidden="true"></span>
                                </button>
                                <ul class="menu__sub" id="{{ $idSubmenu }}">
                                    @foreach ($isi as $namaRoute => $item)
                                        {{-- --urutan: pilihan muncul bergantian dari atas ke bawah saat dropdown dibuka --}}
                                        <li style="--urutan: {{ $loop->index }}">
                                            <a href="{{ route($namaRoute) }}" class="menu__subtautan"
                                               @if (request()->routeIs($namaRoute)) aria-current="page" @endif>
                                                <span class="menu__subikon"><x-ikon :nama="$item['ikon']" /></span>
                                                <span class="menu__subteks">
                                                    @isset($item['label_hp'])
                                                        <span class="menu__subjudul-hp">{{ $item['label_hp'] }}</span>
                                                    @endisset
                                                    <span class="menu__subjudul">{{ $item['label'] }}</span>
                                                    <span class="menu__subketerangan">{{ $item['keterangan'] }}</span>
                                                </span>
                                                <x-ikon nama="panah-kanan" class="menu__subpanah" />
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li style="--dial: {{ $urutanDial++ }}">
                                <a href="{{ route($kunci) }}"
                                   class="menu__tautan"
                                   @if (request()->routeIs($kunci)) aria-current="page" @endif><span class="menu__ikon"><x-ikon :nama="$ikonMenu[$kunci] ?? 'panah-kanan'" /></span><span class="menu__label">{{ $isi }}</span></a>
                            </li>
                        @endif
                    @endforeach
                </ul>

                {{-- Tombol akun muncul otomatis setelah fitur login tersedia (Batch 3.4b). --}}
                @if (Route::has('login'))
                    <div class="kepala__akun" style="--dial: {{ $urutanDial }}">
                        @auth('web')
                            <a class="uk-button uk-button-primary uk-button-small" href="{{ route('akun') }}">Akun saya</a>
                        @else
                            <a class="menu__tautan" href="{{ route('login') }}">Masuk</a>
                            <a class="uk-button uk-button-primary uk-button-small" href="{{ route('daftar') }}">Daftar</a>
                        @endauth
                    </div>
                @endif
            </nav>
        </div>
    </header>

    <main id="konten">
        {{ $slot }}
    </main>

    <footer class="kaki">
        <div class="uk-container kaki__isi">
            <div class="uk-grid uk-grid-large" uk-grid>
            <div class="uk-width-1-1 uk-width-1-2@m kaki__merek">
                <p class="kaki__nama"><span class="kaki__logo-teks">JNET</span> {{ $namaPerusahaan }}</p>
                <p class="kaki__teks">Layanan internet rumah dengan wilayah utama Kota dan Kabupaten Probolinggo. Lokasi di luar Probolinggo juga dapat diajukan.</p>
                @if ($situs->ada('alamat_kantor'))
                    <address class="kaki__teks">{!! nl2br(e($situs->get('alamat_kantor'))) !!}</address>
                @endif
            </div>

            <nav class="uk-width-1-2 uk-width-1-4@m kaki__menu" aria-label="Menu bawah">
                <ul>
                    @foreach ($menuDatar as $namaRoute => $label)
                        <li><a href="{{ route($namaRoute) }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="uk-width-1-2 uk-width-1-4@m kaki__kontak">
                @if ($situs->ada('telepon'))
                    <p>Telepon: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $situs->get('telepon')) }}">{{ $situs->get('telepon') }}</a></p>
                @endif
                @if ($situs->ada('email'))
                    <p>Email: <a href="mailto:{{ $situs->get('email') }}">{{ $situs->get('email') }}</a></p>
                @endif
                @if ($tautanWa = $situs->tautanWhatsapp())
                    <p><a href="{{ $tautanWa }}" target="_blank" rel="noopener">Chat WhatsApp</a></p>
                @endif
            </div>
            </div>
        </div>
        {{-- Revisi mitra: tautan "Masuk admin" tidak ditampilkan di footer; admin masuk lewat alamat /admin/masuk. --}}
        <div class="uk-container kaki__bawah">
            <p>&copy; {{ now()->year }} {{ $namaPerusahaan }}</p>
        </div>
    </footer>

    <x-tombol-cs />
</body>
</html>
