{{-- Navbar transparan di atas foto banner, menjadi putih saat digulir (hanya jika ada foto).
     Di HP navbar ikut transparan bila semua banner punya versi HP tegak (gaya IndiHome).
     Judul dan deskripsi memuat kata kunci pencarian "internet rumah Probolinggo" (SEO). --}}
<x-layouts.publik judul="Internet Rumah Probolinggo" :kepala-transparan="$banner !== []" :kepala-transparan-hp="$bannerTegak"
                  :deskripsi="$paket->isNotEmpty()
                      ? 'Pasang internet rumah JNET di Kota dan Kabupaten Probolinggo. Pilihan '.$paket->min('kecepatan_mbps').'–'.$paket->max('kecepatan_mbps').' Mbps mulai '.\App\Support\Format::rupiah($paket->min('harga')).'. Ajukan pemasangan secara online dan pantau statusnya dari akun Anda.'
                      : null">
    @php
        $tercepat = $paket->max('kecepatan_mbps') ?: 1;
        $tautanWa = $situs->tautanWhatsapp('Halo JNET, saya ingin menanyakan ketersediaan layanan di alamat saya.');
        $versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');

        $detikOtomatis = max(0, (int) config('banner.otomatis_detik', 5));

        // Ubin aksi cepat: semuanya menuju fitur yang benar-benar ada.
        $adaInfoBayar = $situs->ada('info_pembayaran');
        $aksiCepat = array_values(array_filter([
            Route::has('pengajuan.create')
                ? ['ikon' => 'pasang', 'judul' => 'Pasang JNET', 'teks' => 'Ajukan pemasangan baru', 'tautan' => route('pengajuan.create')] : null,
            Route::has('akun')
                ? ['ikon' => 'lacak', 'judul' => 'Lacak Pengajuan', 'teks' => 'Cek status pengajuan Anda', 'tautan' => route('akun')] : null,
            $adaInfoBayar
                ? ['ikon' => 'bayar', 'judul' => 'Bayar Tagihan', 'teks' => 'Cara membayar tagihan', 'tautan' => route('bayar-tagihan')] : null,
            ['ikon' => 'jangkauan', 'judul' => 'Cek Jangkauan', 'teks' => 'Wilayah layanan JNET', 'tautan' => route('jangkauan')],
            ! $adaInfoBayar
                ? ['ikon' => 'hubungi', 'judul' => 'Hubungi Kami', 'teks' => 'Kontak dan WhatsApp', 'tautan' => route('kontak')] : null,
        ]));

        // Revisi mitra: "Kenapa memilih JNET?" sebagai tanya-jawab. Isinya tetap hanya hal yang dapat
        // dibuktikan dari data dan alur sistem; angka kecepatan diambil dari paket aktif di database.
        $tanyaJawab = array_values(array_filter([
            $paket->isNotEmpty() ? [
                'tanya' => 'Ada berapa pilihan kecepatan internet?',
                'jawab' => 'Ada '.$paket->count().' pilihan kecepatan. Mulai '.$paket->min('kecepatan_mbps').' Mbps hingga '.$paket->max('kecepatan_mbps').' Mbps, sesuai kebutuhan rumah Anda.',
            ] : null,
            ['tanya' => 'Apakah bisa mengajukan pemasangan dari rumah?', 'jawab' => 'Bisa. Isi formulir pengajuan secara online tanpa perlu datang ke kantor.'],
            ['tanya' => 'Apakah lokasi saya dicek sebelum dipasang?', 'jawab' => 'Ya. Saat kunjungan pemasangan, teknisi kami mengecek lokasi lebih dulu, lalu langsung memasang jika lokasi memenuhi syarat.'],
            ['tanya' => 'Apakah bisa mengajukan dari luar Probolinggo?', 'jawab' => 'Bisa. Wilayah utama kami Kota dan Kabupaten Probolinggo. Dari luar wilayah itu pun Anda tetap dapat mengajukan; admin JNET mengonfirmasi jangkauannya.'],
            ['tanya' => 'Bagaimana cara memantau status pengajuan?', 'jawab' => 'Masuk ke akun Anda. Status pengajuan, jadwal pemasangan, dan pesan dari admin JNET tampil di halaman akun.'],
        ]));
        $tautanTanya = $situs->tautanWhatsapp('Halo JNET, saya punya pertanyaan tentang layanan internet JNET.');
    @endphp

    <div class="sorotan">
        @if ($banner === [])
            {{-- Hero teks bawaan: tampil selama foto banner belum dipasang --}}
            <section class="hero">
                <div class="uk-container hero__isi">
                    <div class="hero__teks">
                        <h1 class="hero__judul">Ajukan internet rumah, di Probolinggo maupun luar Probolinggo</h1>
                        <p class="hero__lead">
                            @if ($paket->isNotEmpty())
                                Pilih kecepatan {{ $paket->min('kecepatan_mbps') }} sampai {{ $paket->max('kecepatan_mbps') }} Mbps.
                            @endif
                            Ajukan pemasangan secara online, lalu tim kami memeriksa lokasi Anda sebelum memasang.
                        </p>
                        <div class="hero__aksi uk-light">
                            @if (Route::has('pengajuan.create'))
                                <a class="uk-button uk-button-secondary" href="{{ route('pengajuan.create') }}">Langganan</a>
                                <a class="uk-button uk-button-default" href="{{ route('paket') }}">Lihat paket</a>
                            @else
                                <a class="uk-button uk-button-primary" href="{{ route('paket') }}">Lihat paket</a>
                            @endif
                        </div>
                    </div>

                    @if ($paket->isNotEmpty())
                        <div class="tangga" aria-label="Pilihan kecepatan paket">
                            @foreach ($paket as $item)
                                <a class="tangga__baris" href="{{ route('paket') }}#paket-{{ $item->id_paket }}"
                                   style="--rasio: {{ round($item->kecepatan_mbps / $tercepat, 3) }}">
                                    <span class="tangga__batang">
                                        <span class="tangga__label"><span class="angka-kecepatan">{{ $item->kecepatan_mbps }}</span> Mbps</span>
                                    </span>
                                    <span class="tangga__harga">{{ \App\Support\Format::rupiah($item->harga) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @else
            {{-- Banner foto geser. Tulisan sudah ada di dalam foto, jadi foto tidak pernah dipotong.
                 banner--tegak (versi HP 4:5): di HP foto tampil penuh di belakang navbar, tombol Langganan
                 di kiri bawah dan titik geser di kanan bawah foto, seperti banner IndiHome. --}}
            <section @class(['banner', 'banner--tegak' => $bannerTegak]) aria-roledescription="carousel" aria-label="Promo JNET"
                     data-geser data-geser-putar data-geser-seret data-geser-otomatis="{{ $detikOtomatis }}" data-geser-nama="Banner">
                <h1 class="visually-hidden">JNET – Ajukan internet rumah, di Probolinggo maupun luar Probolinggo</h1>

                <div class="banner__bingkai">
                    <div class="banner__trek" data-geser-trek>
                        @foreach ($banner as $nomor => $slide)
                            <div class="banner__slide" role="group" aria-roledescription="slide"
                                 aria-label="{{ $nomor + 1 }} dari {{ count($banner) }}" data-geser-item>
                                {{-- Wadah selebar foto: tombol Langganan selalu menempel pada foto --}}
                                <div class="banner__foto">
                                {{-- Optimasi gambar: versi HP (tegak 4:5, foto di atas dan tulisan di bawah) di layar ≤ 767px,
                                     lalu AVIF/WebP/JPEG beberapa ukuran; browser memilih yang paling ringan dan pas. --}}
                                <picture>
                                    @foreach (\App\Support\Banner::FORMAT as $format => $jenis)
                                        @if ($slide['srcset_hp'][$format])
                                            <source media="(max-width: 767px)" type="{{ $jenis }}" srcset="{{ $slide['srcset_hp'][$format] }}" sizes="100vw"
                                                    @if ($slide['lebar_hp']) width="{{ $slide['lebar_hp'] }}" height="{{ $slide['tinggi_hp'] }}" @endif>
                                        @endif
                                    @endforeach
                                    @foreach (['avif' => 'image/avif', 'webp' => 'image/webp'] as $format => $jenis)
                                        @if ($slide['srcset'][$format])
                                            <source type="{{ $jenis }}" srcset="{{ $slide['srcset'][$format] }}" sizes="{{ \App\Support\Banner::SIZES }}">
                                        @endif
                                    @endforeach
                                    {{-- --rasio/--rasio-hp: UIkit mengunci rasio <img> dari atribut width/height,
                                         jadi rasio versi HP diberikan lewat CSS (lihat .banner__slide img). --}}
                                    <img src="{{ $slide['src'] }}" alt="{{ $slide['alt'] }}"
                                         @if ($slide['srcset']['jpg']) srcset="{{ $slide['srcset']['jpg'] }}" sizes="{{ \App\Support\Banner::SIZES }}" @endif
                                         @if ($slide['lebar']) width="{{ $slide['lebar'] }}" height="{{ $slide['tinggi'] }}" @endif
                                         @if ($slide['lebar'] && $slide['lebar_hp']) style="--rasio: {{ $slide['lebar'] }} / {{ $slide['tinggi'] }}; --rasio-hp: {{ $slide['lebar_hp'] }} / {{ $slide['tinggi_hp'] }}" @endif
                                         @if ($nomor === 0) fetchpriority="high" @else fetchpriority="low" loading="lazy" @endif decoding="async">
                                </picture>
                                <a class="uk-button uk-button-secondary banner__tombol banner__tombol--{{ $slide['posisi'] }}" href="{{ $slide['tautan'] }}">
                                    {{ $slide['label_tombol'] }}
                                    <x-ikon nama="panah-kanan" class="banner__tombol-ikon" />
                                </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if (count($banner) > 1)
                        {{-- Panah di sisi kiri dan kanan banner (disembunyikan di HP; di HP banner digeser dengan jari) --}}
                        <button type="button" class="banner__panah banner__panah--sebelum" data-geser-sebelum aria-label="Banner sebelumnya" hidden><x-ikon nama="kiri" /></button>
                        <button type="button" class="banner__panah banner__panah--berikut" data-geser-berikut aria-label="Banner berikutnya" hidden><x-ikon nama="kanan" /></button>
                    @endif
                </div>

                @if (count($banner) > 1)
                    {{-- Titik geser di pojok kanan bawah foto (di HP tanpa versi tegak: di bawah foto) --}}
                    <div class="banner__kendali" data-geser-kendali>
                        <div class="banner__titik" data-geser-titik role="group" aria-label="Pilih banner"></div>
                    </div>
                @endif
            </section>
        @endif

        {{-- Ubin aksi cepat (revisi mitra: disembunyikan di HP, sehingga banner langsung disusul pilihan paket;
             tujuan setiap ubin tetap tersedia di menu). Revisi Vue: daftar ini menjadi pulau Vue (AksiCepat.vue)
             yang menambahkan umpan balik saat ditekan; HTML di bawah tetap dirender server untuk SEO dan tanpa JavaScript. --}}
        <nav class="aksi-cepat" aria-label="Layanan cepat">
            <div class="uk-container">
                <div class="aksi-cepat__bingkai">
                <ul class="aksi-cepat__daftar uk-grid uk-grid-small uk-grid-match uk-child-width-1-2 uk-child-width-1-4@m" uk-grid
                    data-pulau="aksi-cepat">
                    @foreach ($aksiCepat as $aksi)
                        <li>
                            <a class="aksi-cepat__item" href="{{ $aksi['tautan'] }}">
                                <span class="aksi-cepat__ikon"><x-ikon-layanan :nama="$aksi['ikon']" /></span>
                                <span class="aksi-cepat__teks">
                                    <strong>{{ $aksi['judul'] }}</strong>
                                    <span>{{ $aksi['teks'] }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                </div>
            </div>
        </nav>
        {{ \App\Support\PulauVue::skrip('resources/js/beranda.js') }}
    </div>

    {{-- Etalase paket: kartu dapat digeser; semua angka dari database --}}
    <section class="uk-section etalase" id="pilihan-paket" aria-labelledby="judul-pilihan-paket">
        <div class="uk-container">
            <div class="etalase__panel" data-geser data-geser-nama="Halaman paket">
                <div class="etalase__kepala">
                    <h2 class="etalase__judul" id="judul-pilihan-paket">Pilihan paket</h2>
                </div>

                @if ($paket->isEmpty())
                    <p class="etalase__kosong">Paket internet sedang diperbarui. Silakan hubungi kami untuk informasi terbaru.</p>
                @else
                    <div class="etalase__geser">
                        <button type="button" class="etalase__panah etalase__panah--sebelum" data-geser-sebelum aria-label="Paket sebelumnya" hidden>
                            <x-ikon nama="kiri" />
                        </button>
                        <ul class="etalase__trek" data-geser-trek>
                            @foreach ($paket as $item)
                                <li class="etalase__item" data-geser-item>
                                    <x-kartu-paket :paket="$item" />
                                </li>
                            @endforeach
                        </ul>
                        <button type="button" class="etalase__panah etalase__panah--berikut" data-geser-berikut aria-label="Paket berikutnya" hidden>
                            <x-ikon nama="kanan" />
                        </button>
                    </div>
                    <div class="etalase__titik" data-geser-titik role="group" aria-label="Pilih halaman paket" hidden></div>

                    <div class="etalase__bawah">
                        <a class="uk-button uk-button-default etalase__semua" href="{{ route('paket') }}">
                            Lihat detail semua paket <x-ikon nama="panah-kanan" class="tombol__ikon" />
                        </a>
                    </div>
                @endif
            </div>

            @if ($paket->isNotEmpty())
                <x-catatan-harga />
            @endif

            @if ($situs->ada('info_pemasangan'))
                <div class="info-pemasangan">
                    <h3>Informasi pemasangan</h3>
                    <p>{!! nl2br(e($situs->get('info_pemasangan'))) !!}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Revisi mitra: "Kenapa memilih JNET?" sebagai tanya-jawab (referensi FAQ IndiHome, diberi pembeda).
         Akordeon memakai <details> bawaan browser: bekerja tanpa JavaScript, dan jawabannya tetap terbaca
         mesin pencari serta fitur "cari di halaman". Cuplikan jawaban di dalam pemicu disembunyikan dari
         pembaca layar (aria-hidden) karena jawaban lengkapnya ada tepat di bawahnya.
         Judul, penjelasan, tombol, dan setiap pertanyaan muncul halus saat digulir (CSS saja, lihat .faq di app.css). --}}
    <section class="uk-section uk-section-muted faq" aria-labelledby="judul-faq">
        <div class="uk-container">
            <div class="faq__panel">
                <div class="faq__samping">
                    <h2 class="faq__judul" id="judul-faq">Kenapa memilih JNET?</h2>
                    <p class="faq__lead">Jawaban untuk hal yang paling sering ditanyakan sebelum memasang internet rumah JNET.</p>
                    <a class="uk-button uk-button-default faq__tombol" href="{{ $tautanTanya ?: route('kontak') }}"
                       @if ($tautanTanya) target="_blank" rel="noopener" @endif>
                        Pertanyaan lain? Chat kami <x-ikon nama="panah-kanan" class="tombol__ikon" />
                    </a>
                </div>

                <div class="faq__daftar">
                    @foreach ($tanyaJawab as $item)
                        <details class="faq__item">
                            <summary class="faq__pemicu">
                                <span class="faq__penanda" aria-hidden="true"></span>
                                <span class="faq__teks">
                                    <span class="faq__pertanyaan">{{ $item['tanya'] }}</span>
                                    <span class="faq__cuplikan" aria-hidden="true">{{ $item['jawab'] }}</span>
                                </span>
                            </summary>
                            <p class="faq__jawab">{{ $item['jawab'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Ringkasan alur --}}
    <section class="uk-section">
        <div class="uk-container">
            <div class="bagian__kepala">
                <h2 class="bagian__judul">Lima langkah sampai terpasang</h2>
                <a class="tautan-lanjut" href="{{ route('alur') }}">Penjelasan tiap langkah</a>
            </div>
            <x-langkah-alur ringkas />
        </div>
    </section>

    {{-- Ajakan penutup --}}
    <section class="ajakan uk-light">
        <div class="uk-container ajakan__isi">
            <div>
                <h2 class="ajakan__judul">Belum yakin alamat Anda terjangkau?</h2>
                <p>Ketersediaan layanan dipastikan berdasarkan alamat pemasangan.</p>
            </div>
            @if ($tautanWa)
                <a class="uk-button uk-button-primary" href="{{ $tautanWa }}" target="_blank" rel="noopener">Tanyakan lewat WhatsApp</a>
            @else
                <a class="uk-button uk-button-primary" href="{{ route('jangkauan') }}">Lihat area jangkauan</a>
            @endif
        </div>
    </section>

    <script src="{{ $versiAset('js/geser.js') }}" defer></script>
</x-layouts.publik>
