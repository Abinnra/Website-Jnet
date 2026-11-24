<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi Bahasa Indonesia
|--------------------------------------------------------------------------
| Dipakai karena APP_LOCALE=id. Aturan yang tidak tercantum di sini
| memakai pesan bawaan bahasa Inggris (APP_FALLBACK_LOCALE=en).
*/

return [

    'accepted' => ':Attribute harus disetujui.',
    'after' => ':Attribute harus tanggal setelah :date.',
    'after_or_equal' => ':Attribute harus tanggal :date atau sesudahnya.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'array' => ':Attribute harus berupa daftar.',
    'before' => ':Attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus tanggal :date atau sebelumnya.',
    'between' => [
        'array' => ':Attribute harus berisi :min sampai :max item.',
        'file' => 'Ukuran :attribute harus :min sampai :max kilobyte.',
        'numeric' => ':Attribute harus bernilai :min sampai :max.',
        'string' => ':Attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => ':Attribute harus sesuai format :format.',
    'decimal' => ':Attribute harus memiliki :decimal angka desimal.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus terdiri dari :digits digit.',
    'digits_between' => ':Attribute harus terdiri dari :min sampai :max digit.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'enum' => ':Attribute yang dipilih tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'extensions' => ':Attribute harus berupa file dengan ekstensi: :values.',
    'file' => ':Attribute harus berupa file.',
    'filled' => ':Attribute wajib diisi.',
    'gt' => [
        'numeric' => ':Attribute harus lebih besar dari :value.',
        'string' => ':Attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'numeric' => ':Attribute harus lebih besar dari atau sama dengan :value.',
        'string' => ':Attribute minimal :value karakter.',
    ],
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'lowercase' => ':Attribute harus berupa huruf kecil.',
    'lt' => [
        'numeric' => ':Attribute harus lebih kecil dari :value.',
        'string' => ':Attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'numeric' => ':Attribute harus lebih kecil dari atau sama dengan :value.',
        'string' => ':Attribute maksimal :value karakter.',
    ],
    'max' => [
        'array' => ':Attribute maksimal berisi :max item.',
        'file' => 'Ukuran :attribute maksimal :max kilobyte.',
        'numeric' => ':Attribute maksimal bernilai :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'mimes' => ':Attribute harus berupa file bertipe: :values.',
    'mimetypes' => ':Attribute harus berupa file bertipe: :values.',
    'min' => [
        'array' => ':Attribute minimal berisi :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'numeric' => ':Attribute minimal bernilai :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'not_in' => ':Attribute yang dipilih tidak valid.',
    'numeric' => ':Attribute harus berupa angka.',
    'password' => [
        'letters' => ':Attribute harus mengandung minimal satu huruf.',
        'mixed' => ':Attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':Attribute harus mengandung minimal satu angka.',
        'symbols' => ':Attribute harus mengandung minimal satu simbol.',
        'uncompromised' => ':Attribute ini pernah bocor di internet. Gunakan :attribute lain.',
    ],
    'present' => ':Attribute harus ada.',
    'prohibited' => ':Attribute tidak boleh diisi.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'required_if' => ':Attribute wajib diisi jika :other bernilai :value.',
    'required_with' => ':Attribute wajib diisi jika :values diisi.',
    'required_without' => ':Attribute wajib diisi jika :values tidak diisi.',
    'same' => ':Attribute dan :other harus sama.',
    'size' => [
        'array' => ':Attribute harus berisi :size item.',
        'file' => 'Ukuran :attribute harus :size kilobyte.',
        'numeric' => ':Attribute harus bernilai :size.',
        'string' => ':Attribute harus terdiri dari :size karakter.',
    ],
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah terdaftar.',
    'uploaded' => ':Attribute gagal diunggah. Periksa ukuran file lalu coba lagi.',
    'url' => 'Format :attribute tidak valid.',

    'custom' => [
        // Captcha Turnstile (revisi mitra)
        'cf-turnstile-response' => [
            'required' => 'Selesaikan verifikasi keamanan (captcha) terlebih dahulu.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Field
    |--------------------------------------------------------------------------
    | Mengganti nama field teknis menjadi kata yang mudah dipahami.
    */

    'attributes' => [
        'cf-turnstile-response' => 'verifikasi keamanan',
        'email' => 'email',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'username' => 'username',
        'nama' => 'nama',
        'nama_pelanggan' => 'nama pemohon',
        'no_whatsapp' => 'nomor WhatsApp',
        'alamat_pemasangan' => 'alamat pemasangan',
        'kecamatan' => 'kecamatan',
        'latitude' => 'garis lintang',
        'longitude' => 'garis bujur',
        'catatan' => 'catatan',
        'id_paket' => 'paket',
        'nama_paket' => 'nama paket',
        'kecepatan_mbps' => 'kecepatan',
        'harga' => 'harga',
        'biaya_pemasangan' => 'biaya pemasangan',
        'status_aktif' => 'status aktif',
        'status' => 'status',
        'jenis_jadwal' => 'jenis jadwal',
        'tanggal' => 'tanggal',
        'waktu_mulai' => 'jam mulai',
        'hasil' => 'keputusan hasil',
        'dokumentasi' => 'dokumentasi',
    ],

];
