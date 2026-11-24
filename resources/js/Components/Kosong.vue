<script setup>
import Ikon from './Ikon.vue';

/**
 * Keadaan kosong: menjelaskan mengapa tidak ada data dan apa yang dapat dilakukan (slot "aksi").
 * ringkas: versi kecil tanpa bingkai, untuk di dalam kartu.
 */
defineProps({
    judul: { type: String, default: null },
    ikon: { type: String, default: 'info' },
    ringkas: { type: Boolean, default: false },
});
</script>

<template>
    <div class="app-kosong" :class="{ 'app-kosong--ringkas': ringkas }">
        <span v-if="!ringkas" class="app-kosong__ikon"><Ikon :nama="ikon" class="app-ikon--besar" /></span>
        <h2 v-if="judul">{{ judul }}</h2>
        <p v-if="$slots.default"><slot /></p>
        <slot name="aksi" />
    </div>
</template>
