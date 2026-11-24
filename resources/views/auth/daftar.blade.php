<x-layouts.auth judul="Buat akun">
    <p class="auth__lead">Akun dipakai untuk mengajukan pemasangan dan memantau statusnya.</p>

    <x-tombol-google teks="Daftar dengan Google" pemisah="atau daftar dengan email" />

    <form method="POST" action="{{ route('daftar.store') }}" class="form" data-cegah-ganda novalidate>
        @csrf

        <x-form.input name="email" label="Email" type="email" autocomplete="username" spellcheck="false" required autofocus />

        {{-- Kata sandi cukup diisi sekali: tombol "lihat kata sandi" dipakai untuk memeriksa ketikan,
             dan browser dapat menyarankan kata sandi kuat (autocomplete="new-password"). --}}
        <x-form.input name="password" label="Kata sandi" type="password" autocomplete="new-password" required
                      bantuan="Minimal 8 karakter, berisi huruf dan angka. Tekan ikon mata untuk memeriksa ketikan Anda." />

        <x-captcha />

        <button type="submit" class="uk-button uk-button-primary form__kirim" data-teks-proses="Membuat akun…">Buat akun</button>
    </form>

    <p class="auth__alternatif">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
</x-layouts.auth>
