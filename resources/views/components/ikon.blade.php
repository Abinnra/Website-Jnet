@props(['nama'])

@php
    // Ikon garis sederhana buatan sendiri (24x24, mengikuti warna teks). Dekoratif: selalu disertai teks.
    $jalur = [
        'pasang' => '<path d="M3.5 11 12 4l8.5 7"/><path d="M5.5 9.5V20h7"/><path d="M18.5 9.5v3.5"/><path d="M17.5 16v6M14.5 19h6"/><path d="M9.5 20v-5h3"/>',
        'lacak' => '<rect x="4.5" y="3" width="11" height="15" rx="2"/><path d="M7.5 7.5h5M7.5 11h3"/><circle cx="16" cy="16" r="3.5"/><path d="m18.6 18.6 2.4 2.4"/>',
        'bayar' => '<path d="M6 3h12v18l-2.5-1.6L13 21l-2.5-1.6L8 21l-2-1.3z"/><path d="M9 8h6M9 11.5h6M9 15h3.5"/>',
        'jangkauan' => '<path d="M12 21s-6.5-5.4-6.5-10.8a6.5 6.5 0 0 1 13 0C18.5 15.6 12 21 12 21z"/><circle cx="12" cy="10.2" r="2.4"/>',
        'hubungi' => '<path d="M4 5.5h16v10.5H9.5L5 20v-4H4z"/><path d="M8 9.5h8M8 12.5h5"/>',
        // Kaca pembesar: kolom "Cari apa?" di navbar HP dan halaman pencarian.
        'cari' => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.4-4.4"/>',
        // Menu HP "speed dial": beranda (rumah), paket (sinyal Wi-Fi), dan alur berlangganan (rute dua titik).
        'beranda' => '<path d="M3.5 11 12 4l8.5 7"/><path d="M5.5 9.5V20h13V9.5"/><path d="M10 20v-5.5h4V20"/>',
        'paket' => '<path d="M3.5 9.5a12 12 0 0 1 17 0"/><path d="M6.5 12.8a7.5 7.5 0 0 1 11 0"/><path d="M9.5 16a3.2 3.2 0 0 1 5 0"/><circle cx="12" cy="19" r="1.1" fill="currentColor"/>',
        'alur' => '<circle cx="6" cy="6" r="2.2"/><circle cx="18" cy="18" r="2.2"/><path d="M8.2 6H15a3 3 0 0 1 0 6H9a3 3 0 0 0 0 6h6.8"/>',
        // Menu Profil (dropdown navbar): perusahaan, kontak, dan kemitraan bisnis.
        'gedung' => '<path d="M5 21V5.5L12 3l7 2.5V21"/><path d="M3 21h18"/><path d="M8.5 8.5h1.5M14 8.5h1.5M8.5 12h1.5M14 12h1.5M8.5 15.5h1.5M14 15.5h1.5"/><path d="M10.5 21v-2.5h3V21"/>',
        'telepon' => '<path d="M6.6 3.5h2.8l1.5 4-2 1.3a10.6 10.6 0 0 0 6.3 6.3l1.3-2 4 1.5v2.8a2 2 0 0 1-2.2 2A16.6 16.6 0 0 1 4.6 5.7a2 2 0 0 1 2-2.2z"/>',
        // Headset layanan pelanggan (tombol CS melayang)
        'headset' => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="2.5" y="13" width="4.5" height="7" rx="2"/><rect x="17" y="13" width="4.5" height="7" rx="2"/><path d="M19.25 20v.25A2.75 2.75 0 0 1 16.5 23H13"/>',
        'koper' =>'<rect x="3.5" y="7" width="17" height="12.5" rx="2"/><path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7"/><path d="M3.5 12.5h17"/><path d="M10.5 12.5V14h3v-1.5"/>',
        'kiri' => '<path d="m14.5 6-6 6 6 6"/>',
        'kanan' => '<path d="m9.5 6 6 6-6 6"/>',
        'jeda' => '<path d="M9 6.5v11M15 6.5v11"/>',
        'putar' => '<path d="M8.5 6v12l9.5-6z"/>',
        'centang' => '<circle cx="12" cy="12" r="9"/><path d="m8 12.4 2.7 2.7 5.3-5.6"/>',
        // Centang tanpa lingkaran (tahap "Terpasang" di banner Alur; digambar dengan animasi)
        'centang-garis' => '<path d="m6.5 12.5 3.5 3.5 7.5-8"/>',
        'panah-kanan' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'mata' => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'mata-tutup' => '<path d="M10.6 5.6A9.6 9.6 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a17 17 0 0 1-2.4 3.2M6.6 6.6C3.9 8.4 2.5 12 2.5 12S6 18.5 12 18.5a9 9 0 0 0 5.4-1.9"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="m3 3 18 18"/>',
    ][$nama] ?? null;
@endphp

@if ($jalur)
    <svg {{ $attributes->merge(['class' => 'ikon']) }} width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $jalur !!}</svg>
@endif
