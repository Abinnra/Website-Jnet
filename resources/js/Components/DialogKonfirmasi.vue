<script setup>
import { ref, useId } from 'vue';

/**
 * Dialog konfirmasi untuk tindakan yang tidak dapat dibatalkan (menolak, membatalkan, menghapus).
 * Memakai <dialog> modal bawaan browser: halaman di belakang tidak dapat disentuh, Esc menutup,
 * dan fokus kembali ke tombol pembuka. Fokus awal ada pada pilihan aman (Batal).
 */
const props = defineProps({
    judul: { type: String, required: true },
    labelKonfirmasi: { type: String, required: true },
    bahaya: { type: Boolean, default: false },
    memproses: { type: Boolean, default: false },
});

const emit = defineEmits(['konfirmasi']);
const dialog = ref(null);
const idJudul = useId();

defineExpose({
    buka: () => dialog.value?.showModal(),
    tutup: () => dialog.value?.close(),
});
</script>

<template>
    <dialog ref="dialog" class="app-dialog" :aria-labelledby="idJudul">
        <form class="app-dialog__isi" @submit.prevent="emit('konfirmasi')">
            <h2 :id="idJudul" class="app-dialog__judul">{{ props.judul }}</h2>
            <slot />
            <div class="app-dialog__aksi">
                <button type="button" class="uk-button uk-button-default" autofocus @click="dialog.close()">Batal</button>
                <button type="submit" class="uk-button" :class="bahaya ? 'uk-button-danger' : 'uk-button-primary'" :disabled="memproses">
                    {{ memproses ? 'Menyimpan…' : labelKonfirmasi }}
                </button>
            </div>
        </form>
    </dialog>
</template>
