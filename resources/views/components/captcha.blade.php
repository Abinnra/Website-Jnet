{{--
    Captcha Cloudflare Turnstile (revisi mitra: mencegah brute force dan pendaftaran spam).
    Biasanya cukup centang otomatis tanpa teka-teki gambar. Tidak tampil jika captcha tidak aktif.
--}}
@if (\App\Support\Captcha::aktif())
    @php($namaIsian = \App\Support\Captcha::NAMA_ISIAN)
    <div class="field captcha @error($namaIsian) field--error @enderror">
        <div class="cf-turnstile captcha__widget" data-sitekey="{{ \App\Support\Captcha::kunciSitus() }}"
             data-language="id" data-theme="light" data-size="flexible" data-response-field-name="{{ $namaIsian }}"></div>
        @error($namaIsian)
            <p class="uk-text-danger field__error" id="error-captcha" role="alert">{{ $message }}</p>
        @enderror
        <noscript><p class="uk-text-meta field__bantuan">Aktifkan JavaScript agar verifikasi keamanan dapat dimuat.</p></noscript>
    </div>
    @once
        <script src="{{ \App\Support\Captcha::ALAMAT_SKRIP }}" async defer></script>
    @endonce
@endif
