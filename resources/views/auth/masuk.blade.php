<x-layouts.auth judul="Masuk ke akun Anda">
    <x-tombol-google teks="Masuk dengan Google" pemisah="atau masuk dengan email" />

    <form method="POST" action="{{ route('login.store') }}" class="form" data-cegah-ganda novalidate>
        @csrf

        {{-- autocomplete="username": dikenali pengelola kata sandi browser sebagai pasangan kata sandi --}}
        <x-form.input name="email" label="Email" type="email" autocomplete="username" spellcheck="false" required autofocus />

        <x-form.input name="password" label="Kata sandi" type="password" autocomplete="current-password" required />

        <x-captcha />

        <button type="submit" class="uk-button uk-button-primary form__kirim" data-teks-proses="Memproses…">Masuk</button>
    </form>

    <p class="auth__alternatif">Belum punya akun? <a href="{{ route('daftar') }}">Buat akun</a></p>

    {{-- Revisi 5: kata sandi diatur ulang oleh admin (tanpa email/layanan berbayar) --}}
    @php($tautanLupa = $situs->tautanWhatsapp('Halo JNET, saya lupa kata sandi akun website JNET. Email akun saya: '))
    <p class="auth__lupa">
        Lupa kata sandi?
        @if ($tautanLupa)
            <a href="{{ $tautanLupa }}" target="_blank" rel="noopener">Hubungi admin JNET lewat WhatsApp</a>
        @else
            <a href="{{ route('kontak') }}">Hubungi admin JNET</a>
        @endif
        untuk mengatur ulang kata sandi Anda.
    </p>
</x-layouts.auth>
