@props(['judul', 'lead' => null])

{{--
    Banner judul halaman publik (tanpa foto): latar gradasi biru JNET, pola titik,
    dan garis "serat optik" (komponen hiasan-serat). Slot "samping" opsional untuk panel di kanan.
--}}
<section {{ $attributes->class(['kepala-halaman', 'kepala-halaman--bersamping' => isset($samping)]) }}>
    <x-hiasan-serat class="kepala-halaman__serat" />

    <div class="uk-container kepala-halaman__isi">
        <div class="kepala-halaman__teks">
            <h1>{{ $judul }}</h1>
            @if (filled($lead))
                <p>{{ $lead }}</p>
            @endif
            {{ $slot }}
        </div>

        @isset($samping)
            <div class="kepala-halaman__samping">{{ $samping }}</div>
        @endisset
    </div>
</section>
