{{-- Pesan sukses atau gagal setelah sebuah aksi (flash session). --}}
@if (session('sukses'))
    <div class="uk-alert uk-alert-success" role="status">{{ session('sukses') }}</div>
@endif
@if (session('gagal'))
    <div class="uk-alert uk-alert-danger" role="alert">{{ session('gagal') }}</div>
@endif
