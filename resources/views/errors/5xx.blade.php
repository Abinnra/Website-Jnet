@extends('errors.layout')
@section('kode', $exception->getStatusCode())
@section('judul', 'Terjadi kesalahan pada server')
@section('pesan', 'Permintaan Anda belum dapat diproses. Silakan coba beberapa saat lagi.')
