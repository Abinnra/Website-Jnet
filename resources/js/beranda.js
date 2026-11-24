/*
 * Revisi (Vue): pulau Vue di beranda Blade. Ubin aksi cepat tetap dirender server (SEO dan pengunjung tanpa
 * JavaScript); Vue mengambil alih daftar yang sama untuk menambahkan umpan balik saat ditekan
 * (Components/AksiCepat.vue). Dimuat lewat App\Support\PulauVue, yang melewatinya bila build belum ada.
 */
import { createApp } from 'vue';
import AksiCepat from './Components/AksiCepat.vue';

const daftar = document.querySelector('[data-pulau="aksi-cepat"]');

if (daftar) {
    // Data dibaca dari HTML server, sehingga tautan, judul, dan ikon tetap satu sumber dengan Blade.
    const aksi = [...daftar.querySelectorAll('.aksi-cepat__item')].map((tautan) => ({
        tautan: tautan.getAttribute('href'),
        judul: tautan.querySelector('.aksi-cepat__teks strong')?.textContent.trim() ?? '',
        teks: tautan.querySelector('.aksi-cepat__teks span')?.textContent.trim() ?? '',
        ikon: tautan.querySelector('.aksi-cepat__ikon')?.innerHTML ?? '',
    }));

    createApp(AksiCepat, { aksi }).mount(daftar);
}
