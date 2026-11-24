<x-layouts.publik judul="Paket Internet" deskripsi="Pilihan paket internet rumah JNET beserta kecepatan dan harganya."
                  :kepala-transparan="true">
    @php
        $labelPeriode = config('jnet.paket.label_periode_harga');
    @endphp

    {{-- Semua angka di banner diambil dari data paket aktif di database --}}
    <x-kepala-halaman judul="Paket internet" lead="Bandingkan kecepatan dan harga, lalu pilih yang paling sesuai untuk rumah Anda.">
        @if ($paket->isNotEmpty())
            <ul class="sorot-angka" aria-label="Ringkasan paket">
                <li><strong>{{ $paket->count() }}</strong> pilihan paket</li>
                <li>
                    <strong>{{ $paket->min('kecepatan_mbps') }}@if ($paket->count() > 1)–{{ $paket->max('kecepatan_mbps') }}@endif</strong> Mbps
                </li>
                <li>
                    Mulai <strong>{{ \App\Support\Format::rupiah($paket->min('harga')) }}</strong>@if ($labelPeriode) {{ $labelPeriode }}@endif
                </li>
            </ul>
        @endif

        {{-- Revisi (23 November 2026): batang kecepatan diganti spidometer yang dapat dipilih (components/spidometer-paket) --}}
        @if ($paket->isNotEmpty())
            <x-slot:samping>
                <div class="panel-kaca">
                    <p class="panel-kaca__judul">Kecepatan dan harga</p>
                    <x-spidometer-paket :paket="$paket" />
                </div>
            </x-slot:samping>
        @endif
    </x-kepala-halaman>

    <section class="uk-section">
        <div class="uk-container">
            @if ($paket->isEmpty())
                <p class="kosong">Paket internet sedang diperbarui. Silakan hubungi kami untuk informasi terbaru.</p>
            @else
                <div class="daftar-paket uk-grid uk-grid-medium uk-grid-match uk-child-width-1-1 uk-child-width-1-2@s uk-child-width-1-4@l" uk-grid>
                    @foreach ($paket as $item)
                        <div id="paket-{{ $item->id_paket }}" class="daftar-paket__jangkar">
                            <x-kartu-paket :paket="$item" :level="2" />
                        </div>
                    @endforeach
                </div>
                <x-catatan-harga />
            @endif

            @if ($situs->ada('info_pemasangan'))
                <div class="info-pemasangan">
                    <h2>Informasi pemasangan</h2>
                    <p>{!! nl2br(e($situs->get('info_pemasangan'))) !!}</p>
                </div>
            @endif

            <div class="panduan-singkat">
                <h2>Sebelum memilih</h2>
                <ul>
                    <li>Ketersediaan layanan dipastikan berdasarkan alamat pemasangan.</li>
                    <li>Teknisi mengecek lokasi Anda saat kunjungan pemasangan, lalu langsung memasang jika lokasi memenuhi syarat.</li>
                    <li>Lihat <a href="{{ route('alur') }}">alur berlangganan</a> untuk mengetahui setiap tahapnya.</li>
                </ul>
            </div>
        </div>
    </section>
</x-layouts.publik>
