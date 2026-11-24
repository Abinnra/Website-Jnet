/*
 * Revisi (Vue): aplikasi Vue 3 + Inertia untuk panel admin (Pages/Admin/*) dan halaman akun
 * pelanggan (Pages/Akun/*). Halaman publik tetap Blade dengan JavaScript biasa (public/js), kecuali
 * pulau Vue ubin aksi cepat di beranda (beranda.js).
 */
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import AdminLayout from './Layouts/AdminLayout.vue';
import AkunLayout from './Layouts/AkunLayout.vue';
import '../css/aplikasi.css';

// Layar sentuh: Safari di iPhone/iPad baru menyalakan :active (warna menu atau tombol saat ditekan) bila
// halaman punya pendengar touchstart. Pendengar kosong ini pasif, jadi tidak memperlambat gulir.
document.addEventListener('touchstart', () => {}, { passive: true });

createInertiaApp({
    title: (judul, page) => {
        const merek = page?.component?.startsWith('Admin/') ? 'Admin JNET' : 'JNET';

        return judul ? `${judul} | ${merek}` : merek;
    },
    // Setiap halaman dimuat terpisah saat dibuka (code splitting), jadi halaman pertama tetap ringan.
    resolve: (nama) => {
        const halaman = import.meta.glob('./Pages/**/*.vue');

        return halaman[`./Pages/${nama}.vue`]();
    },
    // Layout tetap hidup antarhalaman (sidebar dan posisi gulirnya tidak dimuat ulang).
    layout: (nama) => (nama.startsWith('Admin/') ? AdminLayout : AkunLayout),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) }).use(plugin).mount(el);
    },
    // Bilah progres biru JNET di atas layar saat berpindah halaman.
    progress: { color: '#0077C5', delay: 200, showSpinner: false },
});
