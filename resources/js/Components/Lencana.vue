<script setup>
/**
 * Lencana status. Teks status selalu tampil, jadi makna tidak hanya bergantung pada warna.
 * status: { label, warna } dari server (App\Support\Tampilan::lencana).
 */
defineProps({
    status: { type: Object, required: true },
});
</script>

<template>
    <span class="lencana" :class="`lencana--${status.warna}`">{{ status.label }}</span>
</template>
