/*
 * Revisi (Vue): pulau Vue di halaman Paket. Spidometer kecepatan di banner tetap dirender server
 * (components/spidometer-paket.blade.php, untuk SEO dan pengunjung tanpa JavaScript); Vue mengambil alih
 * dengan pilihan kecepatan yang dapat diketuk (Components/SpidometerPaket.vue). Data dan geometrinya dibaca
 * dari atribut data-spido, jadi tetap satu sumber dengan Blade. Dimuat lewat App\Support\PulauVue.
 */
import { createApp } from 'vue';
import SpidometerPaket from './Components/SpidometerPaket.vue';

const wadah = document.querySelector('[data-pulau="spidometer"]');

if (wadah?.dataset.spido) {
    createApp(SpidometerPaket, { model: JSON.parse(wadah.dataset.spido) }).mount(wadah);
}
