@props(['paket', 'level' => 3])

@php
    // Level heading mengikuti halaman: 3 di bawah judul bagian (beranda), 2 langsung di bawah h1.
    $level = in_array((int) $level, [2, 3, 4], true) ? (int) $level : 3;
    $labelPeriode = config('jnet.paket.label_periode_harga');
    $tampilkanBiayaPasang = config('jnet.paket.tampilkan_biaya_pemasangan');
    // Label dan keunggulan diisi admin (Revisi 4). Jika keunggulan kosong, tampil dua poin
    // standar yang berlaku untuk semua paket sesuai alur sistem (bukan klaim promo).
    $keunggulan = $paket->daftar_keunggulan ?: [
        ['nilai' => 'Cek lokasi', 'keterangan' => 'Saat pemasangan'],
        ['nilai' => 'Pantau status', 'keterangan' => 'Lewat akun Anda'],
    ];
@endphp

<article @class(['kartu-paket', 'kartu-paket--berlabel' => filled($paket->label)])>
    @if (filled($paket->label))
        <p class="kartu-paket__label">{{ $paket->label }}</p>
    @endif

    <h{{ $level }} class="kartu-paket__nama">{{ $paket->nama_paket }}</h{{ $level }}>

    <p class="kartu-paket__kecepatan">
        <span class="angka-kecepatan">{{ $paket->kecepatan_mbps }}</span>
        <span class="kartu-paket__satuan">Mbps</span>
    </p>

    <p class="kartu-paket__harga">
        {{ \App\Support\Format::rupiah($paket->harga) }}@if ($labelPeriode)<span class="kartu-paket__periode">{{ $labelPeriode }}</span>@endif
    </p>

    @if ($tampilkanBiayaPasang)
        <p class="kartu-paket__catatan">
            Biaya pemasangan:
            {{ (float) $paket->biaya_pemasangan > 0 ? \App\Support\Format::rupiah($paket->biaya_pemasangan) : 'Gratis' }}
        </p>
    @endif

    @if (Route::has('pengajuan.create'))
        <a class="uk-button uk-button-secondary kartu-paket__aksi" href="{{ route('pengajuan.create', ['paket' => $paket->id_paket]) }}">
            Pilih {{ $paket->nama_paket }}
        </a>
    @elseif ($tautanWa = $situs->tautanWhatsapp('Halo JNET, saya ingin bertanya tentang '.$paket->nama_paket.'.'))
        <a class="uk-button uk-button-default kartu-paket__aksi" href="{{ $tautanWa }}" target="_blank" rel="noopener">
            Tanyakan paket ini
        </a>
    @endif

    @if ($keunggulan !== [])
        <ul class="kartu-paket__keunggulan">
            @foreach ($keunggulan as $poin)
                <li>
                    <x-ikon nama="centang" class="kartu-paket__centang" />
                    <span>
                        <strong>{{ $poin['nilai'] }}</strong>
                        @if ($poin['keterangan'])
                            <span class="kartu-paket__keterangan">{{ $poin['keterangan'] }}</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</article>
