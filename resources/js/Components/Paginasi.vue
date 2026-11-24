<script setup>
import { Link } from '@inertiajs/vue3';
import Ikon from './Ikon.vue';

/**
 * Navigasi halaman daftar. paginasi dari server (App\Support\Tampilan::paginasi).
 */
defineProps({
    paginasi: { type: Object, required: true },
    satuan: { type: String, default: 'data' },
});
</script>

<template>
    <div class="app-tabel-kaki">
        <p class="app-kecil app-teks-redup">
            Menampilkan {{ paginasi.dari }}–{{ paginasi.sampai }} dari {{ paginasi.total }} {{ satuan }}.
        </p>
        <nav v-if="paginasi.tautan.length" aria-label="Halaman daftar">
            <ul class="app-paginasi">
                <li>
                    <Link v-if="paginasi.sebelumnya" :href="paginasi.sebelumnya" rel="prev" aria-label="Halaman sebelumnya">
                        <Ikon nama="sebelumnya" class="app-ikon--kecil" />
                    </Link>
                    <span v-else class="app-paginasi__mati" aria-hidden="true"><Ikon nama="sebelumnya" class="app-ikon--kecil" /></span>
                </li>
                <li v-for="(tautan, indeks) in paginasi.tautan" :key="indeks">
                    <span v-if="!tautan.url" aria-hidden="true">…</span>
                    <span v-else-if="tautan.aktif" aria-current="page">{{ tautan.label }}</span>
                    <Link v-else :href="tautan.url">{{ tautan.label }}</Link>
                </li>
                <li>
                    <Link v-if="paginasi.berikutnya" :href="paginasi.berikutnya" rel="next" aria-label="Halaman berikutnya">
                        <Ikon nama="berikutnya" class="app-ikon--kecil" />
                    </Link>
                    <span v-else class="app-paginasi__mati" aria-hidden="true"><Ikon nama="berikutnya" class="app-ikon--kecil" /></span>
                </li>
            </ul>
        </nav>
    </div>
</template>
