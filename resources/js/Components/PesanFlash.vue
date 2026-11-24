<script setup>
import { reactive, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Ikon from './Ikon.vue';

/**
 * Pesan sukses atau gagal setelah sebuah aksi (flash data Inertia, diteruskan dari ->with('sukses')).
 * Wadahnya selalu ada (aria-live), sehingga pesan baru dibacakan pembaca layar setelah berpindah halaman.
 */
const page = usePage();
const ditutup = reactive({ sukses: false, gagal: false });

watch(() => page.flash, () => {
    ditutup.sukses = false;
    ditutup.gagal = false;
});
</script>

<template>
    <div aria-live="polite">
        <div v-if="page.flash?.sukses && !ditutup.sukses" class="app-pesan app-pesan--sukses">
            <Ikon nama="centang" />
            <p class="app-pesan__isi">{{ page.flash.sukses }}</p>
            <button type="button" class="app-pesan__tutup" aria-label="Tutup pesan" @click="ditutup.sukses = true">
                <Ikon nama="tutup" class="app-ikon--kecil" />
            </button>
        </div>
    </div>
    <div v-if="page.flash?.gagal && !ditutup.gagal" class="app-pesan app-pesan--gagal" role="alert">
        <Ikon nama="peringatan" />
        <p class="app-pesan__isi">{{ page.flash.gagal }}</p>
        <button type="button" class="app-pesan__tutup" aria-label="Tutup pesan" @click="ditutup.gagal = true">
            <Ikon nama="tutup" class="app-ikon--kecil" />
        </button>
    </div>
</template>
