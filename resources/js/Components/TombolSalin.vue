<script setup>
import { onBeforeUnmount, ref } from 'vue';
import Ikon from './Ikon.vue';

/**
 * Tombol salin (panduan "copy-button"): menyalin teks lewat Async Clipboard API, umpan balik singkat
 * di wadah role="status" (terlihat sekaligus dibacakan), ikon berganti centang saat berhasil.
 * Tombol tidak dinonaktifkan selama umpan balik, sehingga dapat dicoba lagi bila gagal.
 */
const props = defineProps({
    teks: { type: String, required: true },
    label: { type: String, default: 'Salin' },
});

const status = ref('');
const berhasil = ref(false);
const gagal = ref(false);
let pewaktu = 0;

async function salin() {
    clearTimeout(pewaktu);
    status.value = '';

    try {
        await navigator.clipboard.writeText(props.teks);
        berhasil.value = true;
        gagal.value = false;
        requestAnimationFrame(() => { status.value = 'Tersalin'; });
    } catch {
        berhasil.value = false;
        gagal.value = true;
        requestAnimationFrame(() => { status.value = 'Gagal menyalin. Salin manual dari kotak di atas.'; });
    }

    pewaktu = setTimeout(() => {
        berhasil.value = false;
        gagal.value = false;
        status.value = '';
    }, 2500);
}

onBeforeUnmount(() => clearTimeout(pewaktu));
</script>

<template>
    <span class="salin">
        <button type="button" class="uk-button uk-button-default uk-button-small" @click="salin">
            <Ikon :nama="berhasil ? 'centang-garis' : 'salin'" /> {{ label }}
        </button>
        <span class="salin__status" role="status" :data-gagal="gagal ? '' : undefined">{{ status }}</span>
    </span>
</template>
