@php
    $labelPeriode = config('jnet.paket.label_periode_harga');
    $kecamatanLama = old('kecamatan');
    $wilayahLama = old('wilayah', 'probolinggo') === 'luar' ? 'luar' : 'probolinggo';
    $versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');

    // Revisi mitra: formulir memakai model wizard 4 langkah dari website alternatif 3.
    // Jika validasi server gagal, wizard dibuka di langkah yang memuat isian bermasalah.
    $langkahError = match (true) {
        $errors->has('id_paket') => 1,
        $errors->hasAny(['nama_pelanggan', 'no_whatsapp']) => 2,
        $errors->hasAny(['alamat_pemasangan', 'wilayah', 'kecamatan', 'kota_kabupaten', 'kecamatan_luar', 'latitude', 'longitude']) => 3,
        $errors->has('catatan') => 4,
        default => null,
    };

    $namaLangkah = [1 => 'Paket', 2 => 'Data diri', 3 => 'Lokasi', 4 => 'Periksa & kirim'];

    // Leaflet dipakai dari public/vendor/leaflet jika file resminya sudah diletakkan di sana,
    // jika belum dari CDN unpkg. Integrity sama untuk keduanya: hanya file resmi 1.9.4 yang diterima.
    $leafletLokal = is_file(public_path('vendor/leaflet/leaflet.js')) && is_file(public_path('vendor/leaflet/leaflet.css'));
    $sumberLeaflet = $leafletLokal ? asset('vendor/leaflet') : 'https://unpkg.com/leaflet@1.9.4/dist';
@endphp

<x-layouts.akun judul="Ajukan pemasangan">
    <x-slot:head>
        {{-- Leaflet 1.9.4 (peta OpenStreetMap, gratis tanpa API key). Integrity memastikan file tidak diubah pihak lain. --}}
        <link rel="stylesheet" href="{{ $sumberLeaflet }}/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
        <script src="{{ $sumberLeaflet }}/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
        <script src="{{ $versiAset('js/pengajuan.js') }}" defer></script>
    </x-slot:head>

    <div class="wizard-kepala">
        <p class="tautan-kembali"><a href="{{ route('akun') }}">Kembali ke pengajuan saya</a></p>
        <h1 class="wizard-kepala__judul">Ajukan pemasangan</h1>
        <p class="wizard-kepala__teks">Empat langkah singkat. Setelah dikirim, tim JNET memeriksa data dan menjadwalkan pemasangan.</p>
    </div>

    @if ($daftarPaket->isEmpty())
        <div class="kosong kosong--besar">
            <h2>Belum ada paket yang dapat dipilih</h2>
            <p>Paket internet sedang diperbarui. Silakan coba lagi nanti atau hubungi kami.</p>
            <a class="uk-button uk-button-default" href="{{ route('kontak') }}">Lihat kontak</a>
        </div>
    @else
        <form method="POST" action="{{ route('pengajuan.store') }}" class="wizard" data-wizard
              @if ($langkahError) data-langkah-awal="{{ $langkahError }}" @endif data-cegah-ganda novalidate>
            @csrf

            {{-- Penunjuk langkah (diaktifkan JavaScript; tanpa JavaScript semua langkah tampil berurutan) --}}
            <ol class="wizard__progres" aria-label="Langkah pengajuan">
                @foreach ($namaLangkah as $nomor => $nama)
                    <li class="wizard__progres-item" data-progres="{{ $nomor }}">
                        <span class="wizard__progres-nomor" aria-hidden="true">{{ $nomor }}</span>
                        <span class="wizard__progres-nama">{{ $nama }}</span>
                    </li>
                @endforeach
            </ol>

            @if ($errors->any())
                <div class="uk-alert uk-alert-danger" role="alert">
                    Pengajuan belum terkirim. Periksa {{ $errors->count() }} isian yang ditandai merah.
                </div>
            @endif

            {{-- Paket terpilih (tampil di langkah 2–4) --}}
            <div class="wizard__pilihan" data-wizard-pilihan hidden>
                <span class="wizard__pilihan-label">Paket dipilih</span>
                <strong data-ringkasan-paket>Belum memilih paket</strong>
                <span data-ringkasan-harga>–</span>
                <button type="button" class="wizard__ganti" data-ke-langkah="1" aria-label="Ganti paket">Ganti</button>
            </div>

            {{-- Langkah 1: Paket --}}
            <section class="wizard__langkah" data-langkah="1" aria-labelledby="judul-langkah-1">
                <h2 class="wizard__judul" id="judul-langkah-1"><span class="wizard__nomor">Langkah 1 dari 4</span> Pilih kecepatan internet</h2>
                <fieldset class="pilihan-paket @error('id_paket') pilihan-paket--error @enderror" @error('id_paket') aria-describedby="error-id_paket" @enderror>
                    <legend class="visually-hidden">Paket internet</legend>
                    @foreach ($daftarPaket as $paket)
                        <label class="pilihan-paket__item">
                            <input type="radio" name="id_paket" value="{{ $paket->id_paket }}" required
                                   data-nama="{{ $paket->nama_paket }}"
                                   data-harga="{{ \App\Support\Format::rupiah($paket->harga) }}{{ $labelPeriode }}"
                                   @checked((string) $idPaketTerpilih === (string) $paket->id_paket)>
                            <span class="pilihan-paket__isi">
                                <span class="pilihan-paket__mbps"><span class="angka-kecepatan">{{ $paket->kecepatan_mbps }}</span> Mbps</span>
                                <span class="pilihan-paket__teks">
                                    <span class="pilihan-paket__nama">{{ $paket->nama_paket }}</span>
                                    @if (filled($paket->label))
                                        <span class="pilihan-paket__label">{{ $paket->label }}</span>
                                    @endif
                                </span>
                                <span class="pilihan-paket__harga">{{ \App\Support\Format::rupiah($paket->harga) }}{{ $labelPeriode }}</span>
                                <span class="pilihan-paket__centang" aria-hidden="true"></span>
                            </span>
                        </label>
                    @endforeach
                </fieldset>
                @error('id_paket')
                    <p class="uk-text-danger field__error" id="error-id_paket">{{ $message }}</p>
                @enderror
                <x-catatan-harga />
            </section>

            {{-- Langkah 2: Data diri --}}
            <section class="wizard__langkah" data-langkah="2" aria-labelledby="judul-langkah-2">
                <h2 class="wizard__judul" id="judul-langkah-2"><span class="wizard__nomor">Langkah 2 dari 4</span> Data pemohon</h2>
                <div class="form">
                    <x-form.input name="nama_pelanggan" label="Nama pemohon" autocomplete="name" required maxlength="100"
                                  bantuan="Boleh berbeda dari pemilik akun, misalnya nama anggota keluarga yang tinggal di alamat pemasangan." />
                    <x-form.input name="no_whatsapp" label="Nomor WhatsApp" type="tel" inputmode="tel" autocomplete="tel" required
                                  bantuan="Contoh: 0812 3456 7890. Tim kami menghubungi nomor ini untuk konfirmasi dan jadwal pemasangan." />
                </div>
            </section>

            {{-- Langkah 3: Lokasi --}}
            <section class="wizard__langkah" data-langkah="3" aria-labelledby="judul-langkah-3">
                <h2 class="wizard__judul" id="judul-langkah-3"><span class="wizard__nomor">Langkah 3 dari 4</span> Lokasi pemasangan</h2>
                <div class="form">
                    <x-form.textarea name="alamat_pemasangan" label="Alamat pemasangan" rows="3" required maxlength="1000"
                                     bantuan="Tulis nama jalan, nomor rumah, RT/RW, desa atau kelurahan, dan patokan jika ada." />

                    {{-- Revisi 2: pengajuan dari luar Kota/Kabupaten Probolinggo diterima, lalu dikonfirmasi admin. --}}
                    <fieldset class="field pilihan-wilayah" data-wilayah>
                        <legend class="uk-form-label field__label">Wilayah</legend>
                        <label class="pilihan-wilayah__item">
                            <input type="radio" class="uk-radio" name="wilayah" value="probolinggo" @checked($wilayahLama === 'probolinggo')>
                            <span>Kota atau Kabupaten Probolinggo</span>
                        </label>
                        <label class="pilihan-wilayah__item">
                            <input type="radio" class="uk-radio" name="wilayah" value="luar" @checked($wilayahLama === 'luar')>
                            <span>Di luar Probolinggo</span>
                        </label>
                        @error('wilayah')
                            <p class="uk-text-danger field__error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div class="field @error('kecamatan') field--error @enderror" data-wilayah-grup="probolinggo">
                        <label class="uk-form-label field__label" for="field-kecamatan">Kecamatan</label>
                        <select id="field-kecamatan" name="kecamatan" class="uk-select field__input"
                                @error('kecamatan') aria-invalid="true" aria-describedby="field-kecamatan-error" @enderror>
                            <option value="" @selected(! $kecamatanLama)>Pilih kecamatan</option>
                            @foreach ($wilayah as $namaWilayah => $daftarKecamatan)
                                <optgroup label="{{ $namaWilayah }}">
                                    @foreach ($daftarKecamatan as $kecamatan)
                                        <option value="{{ $kecamatan }}" @selected($kecamatanLama === $kecamatan)>{{ $kecamatan }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('kecamatan')
                            <p class="uk-text-danger field__error" id="field-kecamatan-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grup-luar" data-wilayah-grup="luar">
                        <p class="uk-alert uk-alert-primary grup-luar__catatan">
                            Lokasi di luar Kota dan Kabupaten Probolinggo akan dikonfirmasi admin JNET terlebih dahulu.
                            Pemasangan dijadwalkan setelah lokasi dipastikan dapat dijangkau.
                        </p>
                        <x-form.input name="kota_kabupaten" label="Kabupaten atau kota" maxlength="100" autocomplete="address-level2"
                                      bantuan="Contoh: Kabupaten Lumajang atau Kota Pasuruan." />
                        <x-form.input name="kecamatan_luar" label="Kecamatan" maxlength="100" autocomplete="address-level3" />
                    </div>

                    <div class="field @if ($errors->has('latitude') || $errors->has('longitude')) field--error @endif" data-field-peta>
                        <p class="uk-form-label field__label" id="label-peta">
                            Titik lokasi di peta
                            <span class="field__opsional" data-label-peta-probolinggo hidden>(opsional)</span>
                            <span class="field__opsional field__wajib" data-label-peta-luar>(wajib untuk lokasi di luar Probolinggo)</span>
                        </p>
                        <p class="uk-text-meta field__bantuan">Membantu tim menilai jangkauan dan menemukan rumah Anda. Klik peta atau gunakan lokasi Anda, lalu geser penanda jika belum tepat.</p>

                        <div class="peta" data-peta>
                            <div class="peta__kanvas" data-peta-kanvas role="application" aria-labelledby="label-peta"></div>
                            <div class="peta__aksi">
                                <button type="button" class="uk-button uk-button-default uk-button-small" data-peta-lokasi-saya>Gunakan lokasi saya</button>
                                <button type="button" class="uk-button uk-button-default uk-button-small" data-peta-hapus hidden>Hapus titik</button>
                            </div>
                            <p class="peta__status" data-peta-status aria-live="polite"></p>
                        </div>
                        <noscript><p class="uk-text-meta field__bantuan">Peta membutuhkan JavaScript. Untuk lokasi di Probolinggo, pengajuan tetap dapat dikirim tanpa titik lokasi.</p></noscript>

                        <input type="hidden" name="latitude" value="{{ old('latitude') }}" data-peta-lat>
                        <input type="hidden" name="longitude" value="{{ old('longitude') }}" data-peta-lng>
                        @if ($errors->has('latitude') || $errors->has('longitude'))
                            <p class="uk-text-danger field__error" id="error-latitude">{{ $errors->first('latitude') ?: $errors->first('longitude') }}</p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Langkah 4: Periksa dan kirim --}}
            <section class="wizard__langkah" data-langkah="4" aria-labelledby="judul-langkah-4">
                <h2 class="wizard__judul" id="judul-langkah-4"><span class="wizard__nomor">Langkah 4 dari 4</span> Periksa dan kirim</h2>

                {{-- Diisi JavaScript dari isian sebelumnya; tanpa JavaScript, isian di atas tetap terlihat. --}}
                <dl class="periksa" data-periksa hidden>
                    <div>
                        <dt>Paket</dt>
                        <dd><span data-periksa-isi="paket"></span><button type="button" class="wizard__ganti" data-ke-langkah="1" aria-label="Ubah paket">Ubah</button></dd>
                    </div>
                    <div>
                        <dt>Pemohon</dt>
                        <dd><span data-periksa-isi="pemohon"></span><button type="button" class="wizard__ganti" data-ke-langkah="2" aria-label="Ubah data pemohon">Ubah</button></dd>
                    </div>
                    <div>
                        <dt>Lokasi</dt>
                        <dd><span data-periksa-isi="lokasi"></span><button type="button" class="wizard__ganti" data-ke-langkah="3" aria-label="Ubah lokasi">Ubah</button></dd>
                    </div>
                </dl>

                <x-form.textarea name="catatan" label="Catatan untuk tim JNET (opsional)" rows="3" maxlength="1000"
                                 bantuan="Misalnya waktu yang nyaman untuk dihubungi." />

                <ul class="janji">
                    <li><x-ikon nama="centang" /> Data diperiksa admin JNET</li>
                    <li><x-ikon nama="centang" /> Teknisi mengecek lokasi, lalu langsung memasang jika layak</li>
                    <li><x-ikon nama="centang" /> Status dapat dipantau di akun Anda</li>
                </ul>
            </section>

            {{-- Navigasi wizard: tanpa JavaScript hanya tombol kirim yang tampil. --}}
            <div class="wizard__navigasi">
                <button type="button" class="uk-button uk-button-default" data-wizard-kembali hidden>Kembali</button>
                <button type="button" class="uk-button uk-button-primary" data-wizard-lanjut hidden>Lanjut</button>
                <button type="submit" class="uk-button tombol--hijau" data-wizard-kirim data-teks-proses="Mengirim pengajuan…">Kirim pengajuan</button>
            </div>
        </form>
    @endif
</x-layouts.akun>
