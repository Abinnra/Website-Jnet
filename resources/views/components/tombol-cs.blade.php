@props(['pesan' => 'Halo admin JNET, saya ingin bertanya tentang layanan internet JNET.'])

{{--
    Revisi mitra: tombol CS bulat melayang di pojok kanan bawah. Menekannya langsung membuka chat
    WhatsApp admin JNET (nomor dari menu Informasi Website, isian "Nomor WhatsApp resmi") dengan
    pesan pembuka. Selama nomor WhatsApp belum diisi admin, tombol tidak ditampilkan.

    - Ikon (revisi 22-11-2026, referensi Pinterest): headset saja, biru JNET di atas lingkaran putih;
      ujung mikrofon diberi aksen biru yang lebih terang. Ikon berupa SVG inline (bukan gambar yang
      diwarnai ulang dengan filter), jadi warnanya diatur CSS.
    - Tanpa kapsul jam layanan (dihapus atas permintaan pengguna); jam layanan tetap ada di halaman Kontak.
--}}
@php($tautanCs = app(\App\Services\SiteSettingService::class)->tautanWhatsapp($pesan))

@if ($tautanCs)
    <a class="uk-icon-button tombol-cs" href="{{ $tautanCs }}" target="_blank" rel="noopener" aria-label="Chat CS JNET lewat WhatsApp">
        {{-- Headset: bando, dua bantalan telinga, gagang mikrofon, dan ujung mikrofon (aksen) --}}
        <svg class="tombol-cs__headset" viewBox="0 0 48 48" width="34" height="34" aria-hidden="true" focusable="false">
            <path d="M10 28v-5a14 14 0 0 1 28 0v5" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>
            <rect x="5" y="21.5" width="9.5" height="14.5" rx="4.75" fill="currentColor"/>
            <rect x="33.5" y="21.5" width="9.5" height="14.5" rx="4.75" fill="currentColor"/>
            <path d="M38.25 35.5v.75c0 3.6-2.9 5.75-6.5 5.75H28" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
            <rect class="tombol-cs__mik" x="19.5" y="38.75" width="10" height="6.5" rx="3.25"/>
        </svg>
    </a>
@endif
