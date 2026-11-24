<x-layouts.publik judul="Bayar Tagihan" deskripsi="Cara membayar tagihan layanan internet JNET."
                  :kepala-transparan="true">
    @php($tautanWa = $situs->tautanWhatsapp('Halo JNET, saya ingin mengonfirmasi pembayaran tagihan. Nama pelanggan: '))

    <x-kepala-halaman judul="Bayar tagihan"
                      lead="Informasi cara membayar tagihan langganan internet JNET." />

    <section class="uk-section">
        <div class="uk-container bayar">
            @if ($situs->ada('info_pembayaran'))
                <div class="info-pemasangan bayar__info">
                    <h2>Cara pembayaran</h2>
                    <p>{!! nl2br(e($situs->get('info_pembayaran'))) !!}</p>
                </div>
            @else
                <p class="kosong">Informasi pembayaran sedang dilengkapi. Silakan hubungi kami untuk menanyakan cara membayar tagihan.</p>
            @endif

            <p class="catatan-harga">
                Website ini tidak menerima pembayaran online. Pastikan Anda hanya membayar melalui cara yang tercantum di halaman ini.
            </p>

            <div class="bagian__aksi">
                @if ($tautanWa)
                    <a class="uk-button uk-button-primary" href="{{ $tautanWa }}" target="_blank" rel="noopener">Konfirmasi pembayaran lewat WhatsApp</a>
                @endif
                <a class="uk-button uk-button-default" href="{{ route('kontak') }}">Kontak JNET</a>
            </div>
        </div>
    </section>
</x-layouts.publik>
