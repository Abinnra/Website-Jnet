import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

// Revisi (Vue): Vite membundel aplikasi Vue 3 (Inertia) untuk panel admin dan halaman akun (app.js),
// serta pulau Vue kecil di halaman publik (beranda.js: ubin aksi cepat; paket.js: spidometer di banner Paket).
// Selebihnya halaman publik tetap memakai CSS/JS biasa di folder public (UIkit + font lokal), jadi Tailwind dan
// font dari CDN tidak dipakai.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/js/beranda.js', 'resources/js/paket.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // Alamat gambar di template (misalnya /images/...) dibiarkan apa adanya.
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        // IPv4 agar alamat server dev dapat dituliskan di Content-Security-Policy (CSP tidak menerima [::1]).
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**', '**/.claude/**'],
        },
    },
});
