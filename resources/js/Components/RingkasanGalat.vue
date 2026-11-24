<script setup>
import { computed, nextTick, ref, useId, watch } from 'vue';

/**
 * Ringkasan galat di atas formulir. Setelah formulir gagal dikirim, fokus dipindahkan ke ringkasan ini
 * dan setiap butirnya menautkan ke isian yang perlu diperbaiki (pesan di bawah isian tetap tampil).
 * galat: form.errors dari useForm; awalanId: awalan id isian, misalnya "isian-" untuk "isian-nama_paket".
 */
const props = defineProps({
    galat: { type: Object, required: true },
    awalanId: { type: String, default: 'isian-' },
});

const kotak = ref(null);
const idJudul = useId();
const daftar = computed(() => Object.entries(props.galat));

watch(() => JSON.stringify(props.galat), async () => {
    if (daftar.value.length > 0) {
        await nextTick();
        kotak.value?.focus();
    }
});

function fokusKe(nama) {
    document.getElementById(props.awalanId + nama)?.focus();
}
</script>

<template>
    <div v-if="daftar.length" ref="kotak" class="app-ringkasan-galat" tabindex="-1" :aria-labelledby="idJudul">
        <h2 :id="idJudul">{{ daftar.length > 1 ? `Periksa ${daftar.length} isian berikut` : 'Periksa isian berikut' }}</h2>
        <ul>
            <li v-for="[nama, pesan] in daftar" :key="nama">
                <a :href="`#${awalanId}${nama}`" @click.prevent="fokusKe(nama)">{{ pesan }}</a>
            </li>
        </ul>
    </div>
</template>
