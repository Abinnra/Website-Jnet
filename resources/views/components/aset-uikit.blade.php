{{--
    UIkit 3.25.31 (disimpan di public/vendor/uikit agar tidak bergantung CDN) dan tema JNET.
    Dipasang paling awal di <head>; app.css dan panel.css dimuat sesudahnya oleh layout.
--}}
@php($versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1'))
<link rel="stylesheet" href="{{ $versiAset('vendor/uikit/css/uikit.min.css') }}">
<link rel="stylesheet" href="{{ $versiAset('css/tema.css') }}">
<link rel="stylesheet" href="{{ $versiAset('css/uikit-jnet.css') }}">
<script src="{{ $versiAset('vendor/uikit/js/uikit.min.js') }}" defer></script>
<script src="{{ $versiAset('vendor/uikit/js/uikit-icons.min.js') }}" defer></script>
