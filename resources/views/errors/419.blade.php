@extends('errors.layout')
@section('kode', '419')
@section('judul', 'Sesi formulir telah berakhir')
@section('pesan', 'Halaman terlalu lama dibiarkan terbuka sehingga formulir tidak dapat dikirim demi keamanan. Muat ulang halaman, lalu isi dan kirim kembali formulirnya.')
@section('aksi')
    @php
        // Hanya alamat dari website ini sendiri (disimpan di sesi), bukan header Referer.
        $sebelumnya = session()->previousUrl();
        $tautanKembali = $sebelumnya && str_starts_with($sebelumnya, url('/').'/') ? $sebelumnya : url('/');
    @endphp
    <a class="uk-button uk-button-primary" href="{{ $tautanKembali }}">Kembali ke halaman sebelumnya</a>
@endsection
