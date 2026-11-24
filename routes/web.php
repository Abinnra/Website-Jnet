<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HasilLayananController;
use App\Http\Controllers\Admin\InformasiWebsiteController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\PaketController;
use App\Http\Controllers\Admin\PengajuanController as AdminPengajuanController;
use App\Http\Controllers\Admin\SandiPelangganController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Pelanggan\AkunController;
use App\Http\Controllers\Pelanggan\AuthController as PelangganAuthController;
use App\Http\Controllers\Pelanggan\KataSandiController;
use App\Http\Controllers\Pelanggan\LoginGoogleController;
use App\Http\Controllers\Pelanggan\PengajuanController;
use App\Http\Controllers\Pelanggan\RegistrasiAkunController;
use App\Http\Controllers\Publik\CariController;
use App\Http\Controllers\Publik\HalamanController;
use App\Http\Controllers\Publik\SeoController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\WajibGantiSandi;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik (Pengunjung)
|--------------------------------------------------------------------------
*/

Route::controller(HalamanController::class)->group(function () {
    Route::get('/', 'beranda')->name('beranda');
    Route::get('/paket', 'paket')->name('paket');
    Route::get('/jangkauan', 'jangkauan')->name('jangkauan');
    Route::get('/alur-berlangganan', 'alur')->name('alur');
    Route::get('/profil', 'profil')->name('profil');
    Route::get('/kontak', 'kontak')->name('kontak');
    // Revisi mitra: kemitraan bisnis (B2B) yang langsung menghubungi direktur.
    Route::get('/kemitraan', 'kemitraan')->name('kemitraan');
    Route::get('/bayar-tagihan', 'bayarTagihan')->name('bayar-tagihan');
});

// Revisi: pencarian situs dari kolom "Cari apa?" di navbar HP.
Route::get('/cari', CariController::class)->name('cari');

// Revisi mitra (SEO): peta situs dan aturan perayap untuk mesin pencari.
// public/robots.txt sengaja tidak ada agar baris Sitemap memakai alamat domain yang sebenarnya.
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Autentikasi Pelanggan (guard: web)
|--------------------------------------------------------------------------
*/

Route::middleware('guest:web')->group(function () {
    Route::get('/daftar', [RegistrasiAkunController::class, 'create'])->name('daftar');
    Route::post('/daftar', [RegistrasiAkunController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('daftar.store');

    Route::get('/masuk', [PelangganAuthController::class, 'create'])->name('login');
    Route::post('/masuk', [PelangganAuthController::class, 'store'])->name('login.store');

    // Revisi mitra: masuk/daftar dengan akun Google (aktif setelah GOOGLE_CLIENT_ID diisi).
    Route::get('/masuk/google', [LoginGoogleController::class, 'arahkan'])->name('login.google');
    Route::get('/masuk/google/callback', [LoginGoogleController::class, 'kembali'])
        ->middleware('throttle:20,1')
        ->name('login.google.callback');
});

Route::middleware(['auth:web', WajibGantiSandi::class])->group(function () {
    Route::post('/keluar', [PelangganAuthController::class, 'destroy'])->name('logout');

    // Use Case: Mengajukan Berlangganan. Formulir bertahap ini tetap Blade (peta lokasi dan captcha).
    Route::get('/pengajuan/buat', [PengajuanController::class, 'create'])->name('pengajuan.create');
    Route::post('/pengajuan', [PengajuanController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('pengajuan.store');

    // Revisi (Vue): halaman akun pelanggan dirender Vue 3 lewat Inertia.
    Route::middleware(HandleInertiaRequests::class)->group(function () {
        Route::get('/akun', AkunController::class)->name('akun');

        // Revisi 5 (UML): Use Case Mengubah Kata Sandi.
        Route::get('/akun/kata-sandi', [KataSandiController::class, 'edit'])->name('akun.sandi.edit');
        Route::put('/akun/kata-sandi', [KataSandiController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('akun.sandi.update');

        // Use Case: Melihat Konfirmasi Pengajuan, Melihat Riwayat dan Status.
        Route::get('/pengajuan/{registrasi}', [PengajuanController::class, 'show'])
            ->whereNumber('registrasi')
            ->name('pengajuan.show');
    });
});

/*
|--------------------------------------------------------------------------
| Admin (guard: admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/masuk', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/masuk', [AdminAuthController::class, 'store'])->name('login.store');
    });

    // Revisi (Vue): panel admin dirender Vue 3 lewat Inertia.
    Route::middleware(['auth:admin', HandleInertiaRequests::class])->group(function () {
        Route::post('/keluar', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        // Use Case: Mengelola Paket Internet (tanpa hapus; paket dinonaktifkan).
        Route::resource('paket', PaketController::class)
            ->except(['show', 'destroy'])
            ->parameters(['paket' => 'paket'])
            ->names('paket');
        Route::patch('/paket/{paket}/status', [PaketController::class, 'ubahStatus'])->name('paket.status');

        // Use Case: Mengelola Pengajuan.
        Route::get('/pengajuan', [AdminPengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/{registrasi}', [AdminPengajuanController::class, 'show'])
            ->whereNumber('registrasi')
            ->name('pengajuan.show');
        Route::patch('/pengajuan/{registrasi}/status', [AdminPengajuanController::class, 'ubahStatus'])
            ->whereNumber('registrasi')
            ->name('pengajuan.status');
        Route::put('/pengajuan/{registrasi}/pesan', [AdminPengajuanController::class, 'simpanPesan'])
            ->whereNumber('registrasi')
            ->name('pengajuan.pesan');

        // Use Case: Mengelola Jadwal Pemasangan (revisi mitra: tanpa survei terpisah).
        Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/pengajuan/{registrasi}/jadwal/buat', [JadwalController::class, 'create'])
            ->whereNumber('registrasi')
            ->name('jadwal.create');
        Route::post('/pengajuan/{registrasi}/jadwal', [JadwalController::class, 'store'])
            ->whereNumber('registrasi')
            ->name('jadwal.store');
        Route::get('/jadwal/{jadwal}/ubah', [JadwalController::class, 'edit'])->whereNumber('jadwal')->name('jadwal.edit');
        Route::put('/jadwal/{jadwal}', [JadwalController::class, 'update'])->whereNumber('jadwal')->name('jadwal.update');

        // Use Case: Mencatat Hasil Layanan.
        Route::get('/jadwal/{jadwal}/hasil/buat', [HasilLayananController::class, 'create'])
            ->whereNumber('jadwal')
            ->name('hasil.create');
        Route::post('/jadwal/{jadwal}/hasil', [HasilLayananController::class, 'store'])
            ->whereNumber('jadwal')
            ->name('hasil.store');
        Route::get('/hasil/{hasil}/dokumentasi', [HasilLayananController::class, 'dokumentasi'])
            ->whereNumber('hasil')
            ->name('hasil.dokumentasi');

        // Revisi 5 (UML): Use Case Mengatur Ulang Kata Sandi Pelanggan.
        Route::get('/sandi-pelanggan', [SandiPelangganController::class, 'create'])->name('sandi-pelanggan.create');
        Route::post('/sandi-pelanggan', [SandiPelangganController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('sandi-pelanggan.store');

        // Revisi mitra: Mengelola Wilayah Layanan (kota/kabupaten dan kecamatan).
        Route::controller(WilayahController::class)->prefix('wilayah')->name('wilayah.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/kota', 'simpanKota')->name('kota.store');
            Route::put('/kota/{kota}', 'ubahKota')->whereNumber('kota')->name('kota.update');
            Route::delete('/kota/{kota}', 'hapusKota')->whereNumber('kota')->name('kota.destroy');
            Route::post('/kota/{kota}/kecamatan', 'simpanKecamatan')->whereNumber('kota')->name('kecamatan.store');
            Route::put('/kecamatan/{kecamatan}', 'ubahKecamatan')->whereNumber('kecamatan')->name('kecamatan.update');
            Route::delete('/kecamatan/{kecamatan}', 'hapusKecamatan')->whereNumber('kecamatan')->name('kecamatan.destroy');
        });

        // Use Case: Mengelola Informasi Website.
        Route::get('/informasi-website', [InformasiWebsiteController::class, 'edit'])->name('informasi.edit');
        Route::put('/informasi-website', [InformasiWebsiteController::class, 'update'])->name('informasi.update');
    });
});
