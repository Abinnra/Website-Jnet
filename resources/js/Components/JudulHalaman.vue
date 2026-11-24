<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Ikon from './Ikon.vue';

/**
 * Judul halaman (h1), deskripsi singkat (slot bawaan), tombol aksi (slot "aksi"),
 * tautan kembali opsional, dan judul tab browser. Slot "judul" mengganti isi h1 bila judulnya
 * perlu markup (misalnya nomor pengajuan); prop judul tetap dipakai untuk tab browser.
 */
const props = defineProps({
    judul: { type: String, required: true },
    judulTab: { type: String, default: null },
    kembali: { type: Object, default: null }, // { href, label }
});
</script>

<template>
    <Head :title="props.judulTab ?? props.judul" />
    <Link v-if="kembali" :href="kembali.href" class="app-kembali">
        <Ikon nama="kembali" class="app-ikon--kecil" /> {{ kembali.label }}
    </Link>
    <div class="app-judul">
        <div class="app-judul__teks">
            <h1><slot name="judul">{{ judul }}</slot></h1>
            <p v-if="$slots.default" class="app-judul__deskripsi"><slot /></p>
        </div>
        <div v-if="$slots.aksi" class="app-judul__aksi">
            <slot name="aksi" />
        </div>
    </div>
</template>
