{{-- Navbar transparan di atas banner terang (seperti Profil), menjadi putih saat digulir --}}
<x-layouts.publik judul="Kemitraan Bisnis" deskripsi="Kerja sama bisnis (B2B) dengan JNET, dibahas langsung bersama direktur."
                  kepala-transparan="terang">
    @php
        // Kontak direktur diatur admin di menu Informasi Website (kelompok Kemitraan bisnis).
        $namaPerusahaan = $situs->get('nama_perusahaan');
        $namaDirektur = $situs->get('nama_direktur');
        $emailDirektur = $situs->get('email_direktur');
        $teleponDirektur = $situs->get('telepon_direktur');
        $tautanWaDirektur = $situs->tautanWhatsappDirektur('Halo, saya ingin membahas peluang kerja sama bisnis (B2B) dengan JNET.');
        $adaKontakDirektur = $tautanWaDirektur || $emailDirektur !== '' || $teleponDirektur !== '';
        $versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');
    @endphp

    {{-- Revisi mitra: banner penuh bergaya motion graphic (senada dengan banner Profil).
         Kanvas diisi public/js/salaman-partikel.js: titik cahaya membentuk dua tangan yang bersalaman. --}}
    <section class="banner-kemitraan" data-salaman-banner aria-labelledby="judul-kemitraan">
        <canvas class="banner-kemitraan__kanvas" data-salaman-partikel aria-hidden="true"></canvas>
        <div class="uk-container banner-kemitraan__isi">
            <div class="banner-kemitraan__teks" data-salaman-teks>
                <h1 id="judul-kemitraan">Kemitraan bisnis</h1>
                <p class="banner-kemitraan__lead">Untuk kerja sama business to business (B2B), Anda dapat menghubungi direktur JNET secara langsung.</p>
                <div class="banner-kemitraan__aksi">
                    <a class="uk-button uk-button-primary" href="#kontak-direktur">Hubungi direktur</a>
                </div>
            </div>
        </div>
    </section>

    <section class="uk-section">
        <div class="uk-container">
            <div class="kontak kemitraan uk-grid uk-grid-large" uk-grid>
            <div class="uk-width-1-1 uk-width-3-5@m kemitraan__isi">
                <h2>Bermitra dengan JNET</h2>
                <p>
                    Halaman ini untuk perusahaan, instansi, atau pelaku usaha yang ingin menjalin kerja sama dengan
                    {{ $namaPerusahaan }}. Usulan kerja sama dibahas langsung bersama direktur, tidak melalui layanan pelanggan.
                </p>

                @if ($situs->ada('info_kemitraan'))
                    <div class="kemitraan__info">
                        <h3>Informasi kemitraan</h3>
                        <p>{!! nl2br(e($situs->get('info_kemitraan'))) !!}</p>
                    </div>
                @endif

                <h3>Cara memulai</h3>
                <ol class="kemitraan__langkah">
                    <li>Hubungi direktur melalui telepon, WhatsApp, atau email.</li>
                    <li>Perkenalkan perusahaan Anda dan jelaskan bentuk kerja sama yang diusulkan.</li>
                    <li>Kelanjutan kerja sama dibahas bersama direktur JNET.</li>
                </ol>

                <p class="kemitraan__catatan">
                    Ingin memasang internet rumah?
                    @if (Route::has('pengajuan.create'))
                        <a href="{{ route('pengajuan.create') }}">Ajukan pemasangan</a> atau
                    @endif
                    hubungi <a href="{{ route('kontak') }}">layanan pelanggan</a>.
                </p>
            </div>

            <div class="uk-width-1-1 uk-width-2-5@m">
            <aside class="info-kontak kemitraan__kontak" id="kontak-direktur" aria-labelledby="judul-kontak-direktur">
                <h2 class="info-kontak__judul" id="judul-kontak-direktur">Hubungi direktur</h2>

                @if ($adaKontakDirektur)
                    @if ($namaDirektur !== '')
                        <p class="kemitraan__nama">{{ $namaDirektur }}<span>Direktur {{ $namaPerusahaan }}</span></p>
                    @endif

                    @if ($tautanWaDirektur || $emailDirektur !== '')
                        <div class="kemitraan__aksi">
                            @if ($tautanWaDirektur)
                                <a class="uk-button uk-button-primary" href="{{ $tautanWaDirektur }}" target="_blank" rel="noopener">Chat WhatsApp direktur</a>
                            @endif
                            @if ($emailDirektur !== '')
                                <a class="uk-button uk-button-default" href="mailto:{{ $emailDirektur }}?subject={{ rawurlencode('Kerja sama bisnis dengan JNET') }}">Kirim email</a>
                            @endif
                        </div>
                    @endif

                    {{-- Sama seperti halaman Kontak: nomor dan email diatur admin di menu Informasi Website. --}}
                    <dl class="info-kontak__daftar">
                        @if ($teleponDirektur !== '')
                            <div><dt>Telepon</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $teleponDirektur) }}">{{ $teleponDirektur }}</a></dd></div>
                        @endif
                        @if ($tautanWaDirektur)
                            <div><dt>WhatsApp</dt><dd><a href="{{ $tautanWaDirektur }}" target="_blank" rel="noopener">+{{ \App\Support\Format::nomorWhatsapp($situs->get('whatsapp_direktur')) }}</a></dd></div>
                        @endif
                        @if ($emailDirektur !== '')
                            <div><dt>Email</dt><dd><a href="mailto:{{ $emailDirektur }}">{{ $emailDirektur }}</a></dd></div>
                        @endif
                    </dl>
                @else
                    <p class="kosong">
                        Kontak direktur sedang dilengkapi. Sementara itu, hubungi kami melalui halaman
                        <a href="{{ route('kontak') }}">Kontak</a>.
                    </p>
                @endif
            </aside>
            </div>
            </div>
        </div>
    </section>
    <script src="{{ $versiAset('js/salaman-partikel.js') }}" defer></script>
</x-layouts.publik>
