@props(['paket'])

@php
    /*
     * Spidometer kecepatan di banner halaman Paket (revisi 23 November 2026). Referensinya tampilan speed test
     * (Dribbble "Internet speed test – fast.com", LibreSpeed di GitHub): jarum dan busur menunjuk paket yang
     * dipilih, lalu harga dan tombol "Pilih paket" mengikuti pilihan itu. Semua angka dari paket aktif di database.
     *
     * Busur 240° dari kiri bawah ke kanan bawah (derajat SVG, searah jarum jam dari arah kanan: 150° sampai 390°).
     * Skala 20% di atas paket tercepat, dibulatkan ke kelipatan 10, agar jarum paket tercepat tidak menempel di
     * ujung busur. Angka kecepatan diletakkan di luar busur supaya tidak tertutup jarum.
     *
     * HTML ini dirender server (SEO dan pengunjung tanpa JavaScript: pilihan kecepatan berupa tautan ke kartu
     * paketnya). Pulau Vue (resources/js/paket.js, Components/SpidometerPaket.vue) mengambil alih dengan pilihan
     * yang dapat diketuk; geometri dihitung di sini saja dan dikirim ke Vue lewat atribut data-spido.
     */
    $daftar = $paket->values();
    $skala = (int) (ceil(max(1, (int) $daftar->max('kecepatan_mbps')) * 1.2 / 10) * 10);
    $sudut = fn (float $mbps): float => 150 + 240 * min(1, max(0, $mbps / $skala));
    $titik = fn (float $derajat, float $jari): array => [
        round(160 + $jari * cos(deg2rad($derajat)), 2),
        round(150 + $jari * sin(deg2rad($derajat)), 2),
    ];
    $garisDari = fn (float $derajat, float $dalam, float $luar): array => [...$titik($derajat, $dalam), ...$titik($derajat, $luar)];

    // Garis skala kecil; jaraknya dipilih agar jumlahnya paling banyak sekitar 24 garis.
    $kecepatanPaket = $daftar->map(fn ($item) => (int) $item->kecepatan_mbps)->all();
    $langkah = collect([5, 10, 20, 25, 50, 100, 200, 250, 500])->first(fn (int $jarak) => $skala / $jarak <= 24) ?? 1000;
    $garisSkala = [];
    for ($nilai = 0; $nilai <= $skala; $nilai += $langkah) {
        if (! in_array($nilai, $kecepatanPaket, true)) {
            $garisSkala[] = $garisDari($sudut($nilai), 88, 95);
        }
    }

    $adaFormulir = Route::has('pengajuan.create');
    $data = $daftar->map(function ($item) use ($sudut, $titik, $garisDari, $adaFormulir) {
        $derajat = $sudut((float) $item->kecepatan_mbps);

        return [
            'id' => $item->id_paket,
            'nama' => $item->nama_paket,
            'kecepatan' => (int) $item->kecepatan_mbps,
            'harga' => \App\Support\Format::rupiah($item->harga),
            'label' => filled($item->label) ? $item->label : null,
            'tautan' => $adaFormulir ? route('pengajuan.create', ['paket' => $item->id_paket]) : null,
            'sudut' => round($derajat, 2),
            'isi' => round(($derajat - 150) / 240 * 100, 2),
            'tanda' => $titik($derajat, 136),
            'garis' => $garisDari($derajat, 83, 96),
        ];
    })->all();

    // Paket yang ditunjuk lebih dulu: paket berlabel dari admin (misalnya "Terlaris"); bila tidak ada, yang pertama.
    $awal = $daftar->search(fn ($item) => filled($item->label));
    $awal = $awal === false ? 0 : $awal;
    $pilihan = $data[$awal];

    [$mulaiX, $mulaiY] = $titik(150, 112);
    [$akhirX, $akhirY] = $titik(390, 112);
    $model = [
        'busur' => "M {$mulaiX} {$mulaiY} A 112 112 0 1 1 {$akhirX} {$akhirY}",
        'garis' => $garisSkala,
        'nol' => $titik(150, 136),
        'periode' => config('jnet.paket.label_periode_harga'),
        'awal' => $awal,
        'paket' => $data,
    ];

    // Bila build Vue belum ada, skripnya kosong dan spidometer tetap tampil apa adanya dari HTML ini.
    $skrip = \App\Support\PulauVue::skrip('resources/js/paket.js');
    $adaPulau = $skrip->toHtml() !== '';
@endphp

<div @class(['spido', 'spido--pulau' => $adaPulau]) data-pulau="spidometer"
     data-spido="{{ json_encode($model, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}">
    <div class="spido__isi">
        <div class="spido__meter" style="--sudut: {{ $pilihan['sudut'] }}deg; --isi: {{ $pilihan['isi'] }}">
            <svg class="spido__gambar" viewBox="0 0 320 232" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="spido-gradien" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#0077C5" />
                        <stop offset=".55" stop-color="#00ADEF" />
                        <stop offset="1" stop-color="#FF6501" />
                    </linearGradient>
                </defs>
                <path class="spido__jalur" d="{{ $model['busur'] }}" />
                <path class="spido__busur" d="{{ $model['busur'] }}" pathLength="100" />
                <g class="spido__skala">
                    @foreach ($model['garis'] as [$x1, $y1, $x2, $y2])
                        <line x1="{{ $x1 }}" y1="{{ $y1 }}" x2="{{ $x2 }}" y2="{{ $y2 }}" />
                    @endforeach
                </g>
                <text class="spido__nol" x="{{ $model['nol'][0] }}" y="{{ $model['nol'][1] }}">0</text>
                @foreach ($data as $nomor => $item)
                    <g @class(['spido__tanda', 'spido__tanda--aktif' => $nomor === $awal])>
                        <line class="spido__garis-paket" x1="{{ $item['garis'][0] }}" y1="{{ $item['garis'][1] }}" x2="{{ $item['garis'][2] }}" y2="{{ $item['garis'][3] }}" />
                        <text x="{{ $item['tanda'][0] }}" y="{{ $item['tanda'][1] }}">{{ $item['kecepatan'] }}</text>
                    </g>
                @endforeach
                <polygon class="spido__jarum" points="146,146 254,148.6 258,150 254,151.4 146,154" />
                <circle class="spido__poros" cx="160" cy="150" r="10" />
                <circle class="spido__poros-dalam" cx="160" cy="150" r="4" />
            </svg>
            <p class="spido__baca"><span class="spido__angka angka-kecepatan">{{ $pilihan['kecepatan'] }}</span> <span class="spido__satuan">Mbps</span></p>
        </div>

        {{-- Tanpa JavaScript: pilihan kecepatan berupa tautan ke kartu paketnya di bawah banner --}}
        <div class="spido__pilihan">
            <div class="spido__deret">
                @foreach ($data as $nomor => $item)
                    <a @class(['spido__chip', 'spido__chip--aktif' => $nomor === $awal]) href="#paket-{{ $item['id'] }}">{{ $item['kecepatan'] }} Mbps</a>
                @endforeach
            </div>
        </div>

        <div class="spido__bawah">
            <p class="spido__ringkas">
                <span class="spido__nama">{{ $pilihan['nama'] }}@if ($pilihan['label'])<span class="spido__label">{{ $pilihan['label'] }}</span>@endif</span>
                <strong class="spido__harga">{{ $pilihan['harga'] }}@if ($model['periode'])<span class="spido__periode">{{ $model['periode'] }}</span>@endif</strong>
            </p>
            @if ($pilihan['tautan'])
                <a class="uk-button uk-button-secondary spido__aksi" href="{{ $pilihan['tautan'] }}">Pilih {{ $pilihan['nama'] }}</a>
            @endif
        </div>
    </div>
</div>
{{ $skrip }}
