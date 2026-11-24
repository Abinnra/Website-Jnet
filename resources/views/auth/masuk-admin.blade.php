<x-layouts.auth judul="Masuk sebagai admin" area="admin">
    <form method="POST" action="{{ route('admin.login.store') }}" class="form" data-cegah-ganda novalidate>
        @csrf

        <x-form.input name="username" label="Username" autocomplete="username" required autofocus />

        <x-form.input name="password" label="Kata sandi" type="password" autocomplete="current-password" required />

        <x-captcha />

        <button type="submit" class="uk-button uk-button-primary form__kirim" data-teks-proses="Memproses…">Masuk</button>
    </form>

    <p class="auth__alternatif">Bukan admin? <a href="{{ route('beranda') }}">Kembali ke beranda</a></p>
</x-layouts.auth>
