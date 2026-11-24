@extends('errors.layout')
@section('kode', $exception->getStatusCode())
@section('judul', 'Permintaan tidak dapat diproses')
@section('pesan', 'Periksa kembali alamat atau data yang dikirim, lalu coba lagi.')
