@props(['judul' => 'Kontak'])

@php
    $situs = app(\App\Services\SiteSettingService::class);
    $tautanWa = $situs->tautanWhatsapp();
    $adaKontak = $situs->ada('alamat_kantor') || $situs->ada('telepon') || $situs->ada('email')
        || $situs->ada('jam_layanan_kantor') || $tautanWa;
@endphp

<div {{ $attributes->class(['info-kontak']) }}>
    <h2 class="info-kontak__judul">{{ $judul }}</h2>

    @if ($adaKontak)
        <dl class="info-kontak__daftar">
            @if ($situs->ada('alamat_kantor'))
                <div><dt>Alamat kantor</dt><dd>{!! nl2br(e($situs->get('alamat_kantor'))) !!}</dd></div>
            @endif
            @if ($situs->ada('telepon'))
                <div><dt>Telepon</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $situs->get('telepon')) }}">{{ $situs->get('telepon') }}</a></dd></div>
            @endif
            @if ($tautanWa)
                <div><dt>WhatsApp</dt><dd><a href="{{ $tautanWa }}" target="_blank" rel="noopener">+{{ \App\Support\Format::nomorWhatsapp($situs->get('whatsapp')) }}</a></dd></div>
            @endif
            @if ($situs->ada('email'))
                <div><dt>Email</dt><dd><a href="mailto:{{ $situs->get('email') }}">{{ $situs->get('email') }}</a></dd></div>
            @endif
            @if ($situs->ada('jam_layanan_kantor'))
                <div><dt>Jam layanan</dt><dd>{{ $situs->get('jam_layanan_kantor') }}</dd></div>
            @endif
        </dl>
    @else
        <p class="kosong">Informasi kontak resmi sedang dilengkapi.</p>
    @endif
</div>
