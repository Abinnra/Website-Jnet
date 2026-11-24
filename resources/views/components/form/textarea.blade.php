@props(['name', 'label', 'value' => null, 'bantuan' => null, 'rows' => 4])

@php
    $id = 'field-'.$name;
    $adaError = $errors->has($name);
    $terkait = trim(($adaError ? $id.'-error ' : '').($bantuan ? $id.'-bantuan' : ''));
@endphp

<div class="field {{ $adaError ? 'field--error' : '' }}">
    <label class="uk-form-label field__label" for="{{ $id }}">{{ $label }}</label>
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($terkait !== '') aria-describedby="{{ $terkait }}" @endif
        @if ($adaError) aria-invalid="true" @endif
        {{ $attributes->class(['uk-textarea', 'field__input', 'uk-form-danger' => $adaError]) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="uk-text-danger field__error" id="{{ $id }}-error">{{ $message }}</p>
    @enderror
    @if ($bantuan)
        <p class="uk-text-meta field__bantuan" id="{{ $id }}-bantuan">{{ $bantuan }}</p>
    @endif
</div>
