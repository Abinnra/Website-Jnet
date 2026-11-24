<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Aplikasi Website PT. JNET
|--------------------------------------------------------------------------
|
| File ini berisi aturan bisnis yang ditetapkan oleh developer setelah
| dikonfirmasi oleh mitra. Informasi yang boleh diubah admin melalui
| dashboard TIDAK disimpan di sini, tetapi di tabel site_settings
| (daftar kuncinya didefinisikan pada bagian 'site_settings' di bawah).
|
| Penanda "BELUM DIKONFIRMASI MITRA" berarti nilai tersebut masih
| sementara dan wajib diperiksa sebelum website dipublikasikan.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas Merek
    |--------------------------------------------------------------------------
    |
    | Path logo relatif terhadap folder public/. Ganti file atau path ini
    | ketika tim desain menyerahkan aset final.
    |
    */

    'brand' => [
        'nama_singkat' => 'JNET',
        // Revisi mitra: logo vektor (SVG) agar tajam di semua ukuran layar dan lebih ringan.
        'logo' => 'images/brand/logo-jnet.svg',
        // Versi PNG untuk Google (data terstruktur) dan pratinjau media sosial yang tidak membaca SVG.
        'logo_raster' => 'images/brand/logo-jnet.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Wilayah Administratif (Dropdown Kecamatan)
    |--------------------------------------------------------------------------
    |
    | Revisi mitra: kota/kabupaten dan kecamatan disimpan di database
    | (tabel kota_kabupaten dan kecamatan) dan dikelola admin di menu
    | "Wilayah layanan". Daftar 'kecamatan_awal' di bawah HANYA dipakai
    | migrasi untuk mengisi data pertama kali; mengubahnya setelah itu
    | tidak memengaruhi website.
    |
    | Daftar ini menunjukkan WILAYAH ADMINISTRATIF yang dapat dipilih,
    | BUKAN klaim bahwa seluruh kecamatan sudah terjangkau layanan.
    |
    | Sumber: diverifikasi dari beberapa sumber sekunder yang merujuk
    | kode wilayah Kemendagri (35.74 Kota, 35.13 Kabupaten).
    | BELUM dicocokkan langsung dengan dokumen Kepmendagri/BPS terbaru.
    |
    | Catatan ejaan antarsumber:
    | - "Pajarakan" juga ditulis "Pejarakan".
    | - "Kotaanyar" juga ditulis "Kota Anyar".
    |
    | Nilai disimpan apa adanya ke kolom registrasi_pelanggan.kecamatan
    | (VARCHAR). Jika ejaan dikoreksi, data lama tetap menyimpan ejaan lama.
    |
    */

    'wilayah' => [
        'kecamatan_awal' => [
            'Kota Probolinggo' => [
                'Kademangan',
                'Kanigaran',
                'Kedopok',
                'Mayangan',
                'Wonoasih',
            ],
            'Kabupaten Probolinggo' => [
                'Bantaran',
                'Banyuanyar',
                'Besuk',
                'Dringu',
                'Gading',
                'Gending',
                'Kotaanyar',
                'Kraksaan',
                'Krejengan',
                'Krucil',
                'Kuripan',
                'Leces',
                'Lumbang',
                'Maron',
                'Paiton',
                'Pajarakan',
                'Pakuniran',
                'Sukapura',
                'Sumber',
                'Sumberasih',
                'Tegalsiwalan',
                'Tiris',
                'Tongas',
                'Wonomerto',
            ],
        ],

        'titik_acuan' => [
            'nama' => 'pusat Kota Probolinggo',
            'latitude' => -7.7543,
            'longitude' => 113.2159,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ketentuan Paket
    |--------------------------------------------------------------------------
    */

    'paket' => [

        // BELUM DIKONFIRMASI MITRA.
        // Contoh setelah dikonfirmasi: '/bulan'. Selama null, halaman publik
        // menampilkan keterangan bahwa ketentuan harga dikonfirmasi saat proses pengajuan.
        'label_periode_harga' => null,

        // BELUM DIKONFIRMASI MITRA.
        // Selama false, kolom biaya_pemasangan tidak ditampilkan ke publik.
        'tampilkan_biaya_pemasangan' => false,

    ],

    /*
    |--------------------------------------------------------------------------
    | Aturan Penjadwalan
    |--------------------------------------------------------------------------
    |
    | Aturan FINAL dan SEMENTARA yang sudah disepakati ditulis langsung di
    | service/validasi. Aturan di bawah ini menunggu keputusan mitra dan
    | TIDAK AKTIF selama nilainya null, array kosong, atau false.
    |
    */

    'jadwal' => [

        // BELUM DIKONFIRMASI MITRA. Format 'HH:MM', contoh '08:00'.
        'jam_operasional_mulai' => null,
        'jam_operasional_selesai' => null,

        // BELUM DIKONFIRMASI MITRA. Nomor hari ISO: 1 = Senin ... 7 = Minggu.
        'hari_libur' => [],

        // BELUM DIKONFIRMASI MITRA. Jika true, dua jadwal aktif tidak boleh
        // memakai tanggal dan jam mulai yang sama.
        'cegah_bentrok_slot' => false,

    ],

    /*
    |--------------------------------------------------------------------------
    | SEO (revisi mitra: mudah ditemukan di mesin pencari)
    |--------------------------------------------------------------------------
    |
    | Kode verifikasi Google Search Console (metode "tag HTML"). Isi
    | GOOGLE_SITE_VERIFICATION di .env hosting dengan nilai content="..."
    | yang diberikan Google. Selama kosong, tag verifikasi tidak dipasang.
    |
    */

    'seo' => [
        'verifikasi_google' => env('GOOGLE_SITE_VERIFICATION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Unggahan Dokumentasi Hasil Layanan
    |--------------------------------------------------------------------------
    |
    | File disimpan di disk 'local' (storage/app/private) sehingga tidak
    | dapat diakses lewat URL publik.
    |
    */

    'unggahan' => [
        'dokumentasi' => [
            'disk' => 'local',
            'folder' => 'dokumentasi',
            'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
            'maks_kb' => 5120,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kunci Informasi Website (site_settings)
    |--------------------------------------------------------------------------
    |
    | Hanya kunci yang terdaftar di sini yang dapat diubah admin.
    | 'default' dipakai oleh seeder sebagai nilai awal. String kosong berarti
    | informasi belum tersedia, dan tampilan publik akan menyesuaikan.
    |
    | Tipe: teks, textarea, telepon, whatsapp, email.
    |
    */

    'site_settings' => [

        'nama_perusahaan' => [
            'label' => 'Nama perusahaan',
            'tipe' => 'teks',
            'aturan' => ['required', 'string', 'max:150'],
            'default' => 'PT. Jaringan Nusa Evo Telematika',
            'bantuan' => 'Nama resmi perusahaan, sesuai logo.',
        ],

        'deskripsi_usaha' => [
            'label' => 'Deskripsi usaha',
            'tipe' => 'textarea',
            'aturan' => ['nullable', 'string', 'max:3000'],
            'default' => '',
            'bantuan' => 'Ditampilkan di halaman Profil. Gunakan informasi resmi dari mitra.',
        ],

        'alamat_kantor' => [
            'label' => 'Alamat kantor',
            'tipe' => 'textarea',
            'aturan' => ['nullable', 'string', 'max:500'],
            'default' => '',
            'bantuan' => 'Ditampilkan di halaman Profil, Kontak, dan footer.',
        ],

        'telepon' => [
            'label' => 'Nomor telepon kantor',
            'tipe' => 'telepon',
            'aturan' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]{6,20}$/'],
            'default' => '',
            'bantuan' => 'Kosongkan jika belum tersedia; baris telepon akan disembunyikan.',
        ],

        'whatsapp' => [
            'label' => 'Nomor WhatsApp resmi',
            'tipe' => 'whatsapp',
            'aturan' => ['nullable', 'string', 'regex:/^628[0-9]{7,11}$/'],
            'default' => '',
            'bantuan' => 'Contoh: 0812xxxxxxxx atau +62812xxxxxxxx. Kosongkan jika belum tersedia; semua tombol WhatsApp akan disembunyikan.',
        ],

        'email' => [
            'label' => 'Email resmi',
            'tipe' => 'email',
            'aturan' => ['nullable', 'email', 'max:150'],
            'default' => '',
            'bantuan' => 'Kosongkan jika belum tersedia.',
        ],

        'jam_layanan_kantor' => [
            'label' => 'Jam layanan kantor',
            'tipe' => 'teks',
            'aturan' => ['nullable', 'string', 'max:150'],
            'default' => '',
            'bantuan' => 'Contoh penulisan: Senin–Jumat, 08.00–16.00. Hanya untuk ditampilkan; tidak memengaruhi validasi jadwal.',
        ],

        'info_pemasangan' => [
            'label' => 'Informasi pemasangan',
            'tipe' => 'textarea',
            'aturan' => ['nullable', 'string', 'max:1000'],
            'default' => '',
            'bantuan' => 'Isi hanya jika ketentuan pemasangan (misalnya pemasangan gratis) sudah dikonfirmasi mitra. Kosongkan untuk menyembunyikan bagian ini.',
        ],

        'info_jangkauan' => [
            'label' => 'Informasi tambahan jangkauan',
            'tipe' => 'textarea',
            'aturan' => ['nullable', 'string', 'max:2000'],
            'default' => '',
            'bantuan' => 'Ditampilkan di halaman Jangkauan. Jangan menulis klaim cakupan yang belum diverifikasi.',
        ],

        'info_pembayaran' => [
            'label' => 'Informasi pembayaran tagihan',
            'tipe' => 'textarea',
            'aturan' => ['nullable', 'string', 'max:2000'],
            'default' => '',
            'bantuan' => 'Cara membayar tagihan, misalnya nomor rekening atas nama perusahaan, loket pembayaran, dan batas tanggal bayar. Isi hanya dengan data resmi dari mitra. Kosongkan untuk menyembunyikan ubin Bayar Tagihan.',
        ],

        // Revisi mitra: kemitraan bisnis (B2B) yang langsung menghubungi direktur.
        'nama_direktur' => [
            'label' => 'Nama direktur',
            'tipe' => 'teks',
            'aturan' => ['nullable', 'string', 'max:100'],
            'default' => '',
            'bantuan' => 'Kosongkan jika nama tidak ingin ditampilkan.',
        ],

        'telepon_direktur' => [
            'label' => 'Nomor telepon direktur',
            'tipe' => 'telepon',
            'aturan' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]{6,20}$/'],
            'default' => '',
            'bantuan' => 'Nomor telepon yang dapat dihubungi calon mitra bisnis. Kosongkan jika belum tersedia; baris telepon akan disembunyikan.',
        ],

        'whatsapp_direktur' => [
            'label' => 'WhatsApp direktur',
            'tipe' => 'whatsapp',
            'aturan' => ['nullable', 'string', 'regex:/^628[0-9]{7,11}$/'],
            'default' => '',
            'bantuan' => 'Nomor yang dihubungi calon mitra bisnis. Contoh: 0812xxxxxxxx. Kosongkan jika belum tersedia; tombol WhatsApp direktur akan disembunyikan.',
        ],

        'email_direktur' => [
            'label' => 'Email direktur',
            'tipe' => 'email',
            'aturan' => ['nullable', 'email', 'max:150'],
            'default' => '',
            'bantuan' => 'Untuk penawaran atau proposal kerja sama. Kosongkan jika belum tersedia.',
        ],

        'info_kemitraan' => [
            'label' => 'Informasi kemitraan',
            'tipe' => 'textarea',
            'aturan' => ['nullable', 'string', 'max:2000'],
            'default' => '',
            'bantuan' => 'Bentuk kerja sama yang ditawarkan, misalnya internet untuk kantor atau usaha. Isi hanya dengan informasi resmi dari mitra. Kosongkan untuk menyembunyikan bagian ini.',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Awal (dipakai AdminSeeder)
    |--------------------------------------------------------------------------
    |
    | Diisi melalui .env, bukan di file ini. Setelah seeder dijalankan,
    | hapus JNET_ADMIN_PASSWORD dari .env.
    |
    */

    'admin_awal' => [
        'nama' => env('JNET_ADMIN_NAMA'),
        'username' => env('JNET_ADMIN_USERNAME'),
        'password' => env('JNET_ADMIN_PASSWORD'),
    ],

];
