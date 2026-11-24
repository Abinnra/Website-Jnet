{{-- Revisi: hasil pencarian dari kolom "Cari apa?" di navbar HP. Tidak diindeks mesin pencari (noindex). --}}
<x-layouts.publik judul="Cari" deskripsi="Cari halaman, paket internet, dan wilayah layanan JNET." :kepala-transparan="true" :indeks="false">
    @php
        $kelompok = [
            'halaman' => 'Halaman',
            'paket' => 'Paket internet',
            'wilayah' => 'Wilayah layanan',
        ];
        $jumlahHasil = collect($hasil)->sum(fn (array $daftar) => count($daftar));
    @endphp

    <x-kepala-halaman judul="Cari di JNET" lead="Cari halaman, paket internet, atau kecamatan di wilayah layanan JNET.">
        <search class="cari__wadah">
            <form class="cari__form" action="{{ route('cari') }}" method="GET">
                <label class="cari__label" for="kolom-cari">Kata kunci</label>
                <div class="cari__baris">
                    <input class="uk-input cari__kolom" id="kolom-cari" name="q" type="search" value="{{ $kataKunci }}" maxlength="100"
                           placeholder="Contoh: paket 25 Mbps, Mayangan, kontak" enterkeyhint="search"
                           autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false"
                           @if ($kataKunci === '') autofocus @endif>
                    {{-- Di atas banner biru: tombol putih (uk-light), hanya untuk tombol agar kolom isian tetap putih --}}
                    <span class="uk-light cari__aksi">
                        <button class="uk-button uk-button-primary cari__tombol" type="submit">
                            <x-ikon nama="cari" /> Cari
                        </button>
                    </span>
                </div>
            </form>
        </search>
    </x-kepala-halaman>

    <section class="uk-section cari">
        <div class="uk-container">
            @if ($kataKunci === '')
                <h2 class="cari__judul">Yang sering dicari</h2>
                <ul class="cari__daftar">
                    @foreach ($saran as $item)
                        <li>
                            <a class="cari__item" href="{{ $item['tautan'] }}">
                                <span class="cari__item-judul">{{ $item['judul'] }}</span>
                                <span class="cari__item-teks">{{ $item['keterangan'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @elseif ($jumlahHasil === 0)
                <h2 class="cari__judul">Tidak ada hasil untuk "{{ $kataKunci }}"</h2>
                <p class="cari__kosong">
                    Coba kata lain, misalnya nama paket, kecamatan, atau "kontak". Atau tanyakan langsung lewat
                    halaman <a href="{{ route('kontak') }}">Kontak</a>.
                </p>
            @else
                <h2 class="cari__judul">{{ $jumlahHasil }} hasil untuk "{{ $kataKunci }}"</h2>
                @foreach ($kelompok as $kunci => $namaKelompok)
                    @if ($hasil[$kunci] !== [])
                        <h3 class="cari__kelompok">{{ $namaKelompok }}</h3>
                        <ul class="cari__daftar">
                            @foreach ($hasil[$kunci] as $item)
                                <li>
                                    <a class="cari__item" href="{{ $item['tautan'] }}">
                                        <span class="cari__item-judul">{{ $item['judul'] }}</span>
                                        <span class="cari__item-teks">{{ $item['keterangan'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endforeach
            @endif
        </div>
    </section>
</x-layouts.publik>
