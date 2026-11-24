<x-layouts.publik judul="Kontak" deskripsi="Hubungi JNET untuk bertanya tentang paket dan ketersediaan layanan."
                  :kepala-transparan="true">
    @php($tautanWa = $situs->tautanWhatsapp('Halo JNET, saya ingin bertanya tentang layanan internet.'))

    <x-kepala-halaman judul="Kontak"
                      lead="Tanyakan paket, ketersediaan layanan di alamat Anda, atau status pengajuan." />

    <section class="uk-section">
        <div class="uk-container">
            <div class="kontak uk-grid uk-grid-large" uk-grid>
            <div class="uk-width-1-1 uk-width-1-2@m">
                <x-info-kontak judul="Hubungi kami" />
            </div>

            <div class="uk-width-1-1 uk-width-1-2@m kontak__ajakan">
                <h2>Cek ketersediaan di alamat Anda</h2>
                <p>Kirimkan alamat lengkap Anda, dan tim kami akan membantu memastikan ketersediaan layanan.</p>
                @if ($tautanWa)
                    <a class="uk-button uk-button-primary" href="{{ $tautanWa }}" target="_blank" rel="noopener">Chat WhatsApp</a>
                @else
                    <p class="kosong">Nomor WhatsApp resmi akan segera tersedia.</p>
                @endif

                <p class="kontak__mitra">
                    Ingin bekerja sama sebagai mitra bisnis (B2B)? Hubungi direktur kami melalui halaman
                    <a href="{{ route('kemitraan') }}">Kemitraan</a>.
                </p>
            </div>
            </div>
        </div>
    </section>
</x-layouts.publik>
