<script setup>
import { computed } from 'vue';

/**
 * Nomor pengajuan di panel admin, misalnya JNET-261119-01. Awalan dan tanggal pesan tampil tebal sebagai
 * bagian utama, urutan hari itu (-01) lebih kecil dan pudar seperti angka sen pada harga. Teksnya tetap utuh
 * "JNET-261119-01", jadi bisa disalin langsung ke kolom pencarian.
 */
const props = defineProps({
    nomor: { type: String, required: true },
});

const bagian = computed(() => {
    const cocok = /^(.*\d{6})(-\d+)$/.exec(props.nomor);

    return cocok ? { utama: cocok[1], urutan: cocok[2] } : null;
});
</script>

<template>
    <span class="nomor-pengajuan"><template v-if="bagian">{{ bagian.utama }}<span class="nomor-pengajuan__urutan">{{ bagian.urutan }}</span></template><template v-else>{{ nomor }}</template></span>
</template>
