<?php

/*
|--------------------------------------------------------------------------
| Banner Beranda (Revisi UI)
|--------------------------------------------------------------------------
|
| Maksimal 4 foto banner geser. Letakkan file di public/images/banner/
| dengan nama sesuai 'gambar'. Banner yang filenya belum ada dilewati; jika
| belum ada satu pun, beranda menampilkan hero teks bawaan.
|
| Foto sudah berisi tulisan, jadi:
| - 'alt' diisi dengan isi tulisan di foto (dibacakan pembaca layar, dibaca
|   mesin pencari, dan tampil jika foto gagal dimuat).
| - 'posisi_tombol' (kiri/tengah/kanan) dipilih di area foto yang kosong.
| - Versi HP: varian banner-N-hp-{480,768,1080}.{webp,jpg} di folder yang
|   sama, atau 'gambar_hp' (opsional), misalnya 'images/banner/banner-1-hp.jpg'.
|   Bila semua versi HP berbentuk tegak (4:5: foto di atas, tulisan di bawah,
|   bagian paling bawah dikosongkan), di HP banner tampil penuh di belakang
|   navbar dengan tombol di kiri bawah foto, seperti IndiHome. Selain itu
|   tombol diletakkan di bawah foto.
| - 'tautan' kosong = formulir pengajuan; atau alamat di website ini,
|   misalnya '/paket'. Alamat situs lain tidak diterima.
|
| Setelah mengubah file ini, jalankan: php artisan config:clear
|
*/

return [
    // Detik per foto untuk pergantian otomatis; 0 = tidak berganti otomatis.
    // 6 detik agar tulisan promo di foto sempat dibaca (sebelumnya 3 detik).
    'otomatis_detik' => 6,

    'slide' => [
        ['gambar' => 'images/banner/banner-1.jpg', 'gambar_hp' => null, 'alt' => 'Internet Cepat, Seisi Rumah Terhubung. Koneksi stabil untuk kerja, belajar, dan hiburan keluarga. Internet kecepatan hingga 15 Mbps (kecepatan maksimal), harga mulai Rp125 ribu per bulan dari Rp140 ribu, hemat 10,7%.', 'tautan' => null, 'label_tombol' => 'Langganan', 'posisi_tombol' => 'kiri'],
        ['gambar' => 'images/banner/banner-2.jpg', 'gambar_hp' => null, 'alt' => 'Nonton Bareng Lancar, Tanpa Buffering. Film dan serial favorit diputar mulus di semua layar rumah. Internet kecepatan hingga 25 Mbps (kecepatan maksimal), harga mulai Rp150 ribu per bulan dari Rp175 ribu, hemat 14%.', 'tautan' => null, 'label_tombol' => 'Langganan', 'posisi_tombol' => 'kiri'],
        ['gambar' => 'images/banner/banner-3.jpg', 'gambar_hp' => null, 'alt' => 'Musik Favorit Mengalir, Mood Seharian Terjaga. Streaming lagu dan podcast tanpa jeda, kapan saja di rumah. Internet kecepatan hingga 35 Mbps (kecepatan maksimal), harga mulai Rp165 ribu per bulan dari Rp190 ribu, hemat 13%.', 'tautan' => null, 'label_tombol' => 'Langganan', 'posisi_tombol' => 'kiri'],
        ['gambar' => 'images/banner/banner-4.jpg', 'gambar_hp' => null, 'alt' => 'Belajar dan Berkreasi, Seru Bersama Keluarga. Belajar online dan aplikasi kreatif lancar di semua perangkat. Internet kecepatan hingga 50 Mbps (kecepatan maksimal), harga mulai Rp210 ribu per bulan.', 'tautan' => null, 'label_tombol' => 'Langganan', 'posisi_tombol' => 'kiri'],
        ['gambar' => 'images/banner/banner-5.jpg', 'gambar_hp' => null, 'alt' => 'Pasang Internet JNET, Tanpa Biaya Pasang. Biaya pemasangan gratis untuk semua jenis paket.', 'tautan' => null, 'label_tombol' => 'Langganan', 'posisi_tombol' => 'kiri'],
    ],
];
