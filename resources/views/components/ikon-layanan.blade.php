@props(['nama'])

@php
    // Ikon "kaca berlapis" untuk ubin aksi cepat beranda (48x48), mengikuti referensi ikon kaca buram (frosted
    // glass icons) di Dribbble: bentuk biru JNET di belakang, bentuk kaca putih buram di depan, dan bagian
    // belakang yang tertutup kaca tampil kabur. Di atas kaca ada gambar kecil biru, kilau putih, serta titik
    // aksen oranye (seperti lengkung logo JNET) dan biru muda. Dekoratif: ubin selalu memuat judul dan keterangan.
    // belakang: bentuk SVG bebas; depan: satu path (dipakai juga sebagai mask dan clip); gambar: isi kaca.
    $ikon = [
        'pasang' => [
            'belakang' => '<circle cx="33" cy="15" r="10"/>',
            'depan' => 'M19.4 7.8a2.5 2.5 0 0 1 3.2 0l13.9 11.4c.8.7.3 1.9-.7 1.9H33V37a4 4 0 0 1-4 4H13a4 4 0 0 1-4-4V21.1H6.2c-1 0-1.5-1.2-.7-1.9z',
            'gambar' => '<path d="M15.7 28.2a7.5 7.5 0 0 1 10.6 0M18.2 30.7a4 4 0 0 1 5.6 0"/><circle class="ikon-layanan__titik" cx="21" cy="34.4" r="1.8"/>',
            'kilau' => 'M10.4 17.4l4.4-3.6',
            'aksen' => [[41.5, 30, 2.2], [44, 23.5, 1.1]],
        ],
        'lacak' => [
            'belakang' => '<circle cx="31" cy="27" r="9.5"/><rect x="36.2" y="31.2" width="5" height="10" rx="2.5" transform="rotate(-45 38.7 36.2)"/>',
            'depan' => 'M11 6h13a5 5 0 0 1 5 5v24a5 5 0 0 1-5 5H11a5 5 0 0 1-5-5V11a5 5 0 0 1 5-5z',
            'gambar' => '<path d="M11.5 14.5h11M11.5 20.5h8.5M11.5 26.5h5.5"/>',
            'kilau' => 'M9.6 13.2v-1.6a2 2 0 0 1 2-2h1.6',
            'aksen' => [[40.5, 13, 2.2], [36, 7.5, 1.1]],
        ],
        'bayar' => [
            'belakang' => '<rect x="12" y="4.5" width="25" height="16" rx="3.5" transform="rotate(-12 24.5 12.5)"/>',
            'depan' => 'M11 16h22a6 6 0 0 1 6 6v13a6 6 0 0 1-6 6H11a6 6 0 0 1-6-6V22a6 6 0 0 1 6-6z',
            'gambar' => '<path d="M39 24.5h-8.5a4 4 0 0 0 0 8H39"/><circle class="ikon-layanan__titik" cx="30.8" cy="28.5" r="1.7"/>',
            'kilau' => 'M9.4 22.4a3.6 3.6 0 0 1 3.4-2.8h2',
            'aksen' => [[42.5, 11, 2.2], [44.5, 18, 1.1]],
        ],
        'jangkauan' => [
            'belakang' => '<circle cx="30" cy="27" r="11"/>',
            'depan' => 'M19 42C19 42 7 31.6 7 19a12 12 0 0 1 24 0c0 12.6-12 23-12 23z',
            'gambar' => '<circle class="ikon-layanan__titik" cx="19" cy="19" r="4.6"/>',
            'kilau' => 'M11.3 15a8.4 8.4 0 0 1 3.8-4.4',
            'aksen' => [[40, 12, 2.2], [43.5, 41, 1.1]],
        ],
        'hubungi' => [
            'belakang' => '<path d="M25 6h9a8 8 0 0 1 8 8v3c0 3.4-2.1 6.3-5 7.4V28l-4.2-3H25a8 8 0 0 1-8-8v-3a8 8 0 0 1 8-8z"/>',
            'depan' => 'M14 16h9a9 9 0 0 1 9 9v3a9 9 0 0 1-9 9h-8.5L9 41.5V35.6A9 9 0 0 1 5 28v-3a9 9 0 0 1 9-9z',
            'gambar' => '<circle class="ikon-layanan__titik" cx="12.5" cy="26.5" r="1.9"/><circle class="ikon-layanan__titik" cx="18.5" cy="26.5" r="1.9"/><circle class="ikon-layanan__titik" cx="24.5" cy="26.5" r="1.9"/>',
            'kilau' => 'M9.2 22.6a5.4 5.4 0 0 1 3.6-3.4',
            'aksen' => [[42, 34, 2.2], [38.5, 40, 1.1]],
        ],
    ][$nama] ?? null;

    // Id unik per ikon (gradasi, kabur, mask, dan clip); setiap ikon tampil sekali di beranda.
    $id = 'il-'.$nama;
@endphp

@if ($ikon)
    <svg {{ $attributes->merge(['class' => 'ikon-layanan ikon-layanan--'.$nama]) }} width="48" height="48" viewBox="0 0 48 48"
         aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="{{ $id }}-warna" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#3FCDF8"/>
                <stop offset="1" stop-color="#0A6ACB"/>
            </linearGradient>
            <linearGradient id="{{ $id }}-kaca" x1="0" y1="0" x2=".85" y2="1">
                <stop offset="0" stop-color="#fff" stop-opacity=".97"/>
                <stop offset="1" stop-color="#E4F2FF" stop-opacity=".72"/>
            </linearGradient>
            <filter id="{{ $id }}-kabur" x="-30%" y="-30%" width="160%" height="160%">
                <feGaussianBlur stdDeviation="2.4"/>
            </filter>
            {{-- Bagian belakang yang tidak tertutup kaca tampil tajam; yang tertutup kaca digambar ulang dalam keadaan kabur --}}
            <mask id="{{ $id }}-luar" maskUnits="userSpaceOnUse" x="0" y="0" width="48" height="48">
                <rect width="48" height="48" fill="#fff"/>
                <path d="{{ $ikon['depan'] }}" fill="#000"/>
            </mask>
            <clipPath id="{{ $id }}-dalam">
                <path d="{{ $ikon['depan'] }}"/>
            </clipPath>
        </defs>
        <g mask="url(#{{ $id }}-luar)">
            <g class="ikon-layanan__belakang" fill="url(#{{ $id }}-warna)">{!! $ikon['belakang'] !!}</g>
        </g>
        <g clip-path="url(#{{ $id }}-dalam)">
            <g class="ikon-layanan__belakang" fill="url(#{{ $id }}-warna)" filter="url(#{{ $id }}-kabur)">{!! $ikon['belakang'] !!}</g>
        </g>
        <path class="ikon-layanan__kaca" d="{{ $ikon['depan'] }}" fill="url(#{{ $id }}-kaca)" stroke="#fff" stroke-opacity=".85" stroke-width=".9"/>
        <g class="ikon-layanan__gambar" fill="none" stroke="#0A5FC0" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">{!! $ikon['gambar'] !!}</g>
        <path class="ikon-layanan__kilau" d="{{ $ikon['kilau'] }}" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/>
        {{-- Titik aksen: oranye (lengkung logo JNET) dan biru muda --}}
        <g class="ikon-layanan__aksen">
            @foreach ($ikon['aksen'] as [$x, $y, $r])
                <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $r }}" fill="{{ $loop->first ? '#FF7A21' : '#8FE0FF' }}"/>
            @endforeach
        </g>
    </svg>
@endif
