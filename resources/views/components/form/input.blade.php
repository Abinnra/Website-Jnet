@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'bantuan' => null,
])

@php
    $id = $attributes->get('id', 'field-'.$name);
    $adaError = $errors->has($name);
    $idBantuan = $bantuan ? $id.'-bantuan' : null;
    $idError = $adaError ? $id.'-error' : null;
    $terkait = trim(($idBantuan ?? '').' '.($idError ?? ''));
@endphp

<div class="field {{ $adaError ? 'field--error' : '' }}">
    <label class="uk-form-label field__label" for="{{ $id }}">{{ $label }}</label>
    @if ($type === 'password')
        {{-- Tombol lihat sandi dimunculkan oleh app.js; tanpa JavaScript kolom tetap normal. --}}
        <div class="field__sandi" data-sandi>
    @endif
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($terkait !== '') aria-describedby="{{ $terkait }}" @endif
        @if ($adaError) aria-invalid="true" @endif
        {{ $attributes->except('id')->class(['uk-input', 'field__input', 'uk-form-danger' => $adaError]) }}
    >
    @if ($type === 'password')
            <button type="button" class="field__lihat" data-lihat-sandi aria-controls="{{ $id }}" aria-pressed="false"
                    aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi" hidden>
                <x-ikon nama="mata" class="field__ikon-lihat" />
                <x-ikon nama="mata-tutup" class="field__ikon-sembunyi" />
            </button>
        </div>
    @endif
    @error($name)
        <p class="uk-text-danger field__error" id="{{ $idError }}">{{ $message }}</p>
    @enderror
    @if ($bantuan)
        <p class="uk-text-meta field__bantuan" id="{{ $idBantuan }}">{{ $bantuan }}</p>
    @endif
</div>
