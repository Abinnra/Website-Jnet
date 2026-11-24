<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Ikon from './Ikon.vue';

/**
 * Isi sidebar admin (layar lebar): merek, menu, admin yang login, dan keluar.
 * Di HP dan tablet, menu yang sama tampil di bilah navigasi bawah (NavigasiBawahAdmin).
 */
const page = usePage();
const aplikasi = computed(() => page.props.aplikasi);
</script>

<template>
    <Link :href="aplikasi.tautan.beranda" class="app-merek">
        <img :src="aplikasi.tautan.logo" alt="JNET" width="96" height="36">
        <span class="app-merek__peran">Panel admin</span>
    </Link>

    <nav aria-label="Menu admin">
        <ul class="app-menu">
            <li v-for="item in aplikasi.menu" :key="item.href">
                <Link :href="item.href" class="app-menu__tautan" :aria-current="item.aktif ? 'page' : undefined">
                    <Ikon :nama="item.ikon" />
                    <span class="app-menu__label">{{ item.label }}</span>
                    <span v-if="item.jumlah" class="app-menu__jumlah">
                        {{ item.jumlah }}<span class="visually-hidden"> menunggu verifikasi</span>
                    </span>
                </Link>
            </li>
        </ul>
    </nav>

    <div class="app-sisi__akun">
        <div class="app-pengguna">
            <span class="app-pengguna__inisial" aria-hidden="true">{{ aplikasi.akun.inisial }}</span>
            <span class="app-pengguna__teks">
                <span class="app-pengguna__nama">{{ aplikasi.akun.nama }}</span>
                <span class="app-pengguna__info">{{ aplikasi.akun.username }}</span>
            </span>
        </div>
        <div class="app-sisi__tautan">
            <a :href="aplikasi.tautan.website" class="app-sisi__tombol" target="_blank" rel="noopener">
                <Ikon nama="tautan-luar" /> Lihat website
            </a>
            <Link :href="aplikasi.tautan.keluar" method="post" as="button" class="app-sisi__tombol">
                <Ikon nama="keluar" /> Keluar
            </Link>
        </div>
    </div>
</template>
