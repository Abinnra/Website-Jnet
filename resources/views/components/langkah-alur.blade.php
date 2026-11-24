@props(['ringkas' => false, 'level' => 3])

@php
    $level = in_array((int) $level, [2, 3, 4], true) ? (int) $level : 3;
    // Empat tahap sesuai status pengajuan di sistem (revisi mitra: tanpa survei terpisah).
    $langkah = \App\Support\AlurLangganan::langkah();
@endphp

{{-- Grid UIkit: 1 kolom di HP, 2x2 di tablet, 4 kolom sejajar di layar lebar --}}
<ol {{ $attributes->class([
    'langkah uk-grid uk-child-width-1-1 uk-child-width-1-2@s uk-child-width-1-4@l',
    'langkah--ringkas uk-grid-medium' => $ringkas,
]) }} uk-grid>
    @foreach ($langkah as [$judul, $keterangan])
        <li class="langkah__item">
            <span class="langkah__nomor" aria-hidden="true">{{ $loop->iteration }}</span>
            <div>
                <h{{ $level }} class="langkah__judul">{{ $judul }}</h{{ $level }}>
                @unless ($ringkas)
                    <p class="langkah__teks">{{ $keterangan }}</p>
                @endunless
            </div>
        </li>
    @endforeach
</ol>
