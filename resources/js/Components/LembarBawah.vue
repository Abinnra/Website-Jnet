<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Lembar bawah (bottom sheet) untuk HP: <dialog> modal yang meluncur dari bawah. Teknik panduan
 * modern-web-guidance "navigation-drawer" diputar ke arah vertikal: wadah gulir dengan dua titik
 * scroll-snap (tertutup di atas, terbuka di bawah), sehingga usapan ke bawah ditangani browser.
 * Dapat ditutup dengan usap ke bawah, ketuk area gelap, tombol Tutup, Esc, atau gerakan kembali;
 * fokus kembali ke tombol pembukanya (bawaan <dialog>). Latar meredup mengikuti posisi usapan.
 */
const props = defineProps({
    id: { type: String, required: true },
    labelledby: { type: String, required: true },
});

const emit = defineEmits(['terbuka', 'tertutup']);

const dialog = ref(null);
const gulir = ref(null);
const lembar = ref(null);
let pengamat = null;
let hapusPendengarRouter = null;

async function buka() {
    dialog.value.showModal();
    // Status terbuka mengikuti dialog, bukan rasio keterlihatan: tinggi lembar bisa berupa pecahan piksel
    // (rasio berhenti di 0,999) atau melebihi layar saat HP mendatar, sehingga rasio 1 tidak pernah tercapai.
    emit('terbuka');

    // Browser tanpa scroll-initial-target: lompat dulu ke posisi tertutup agar lembar tetap meluncur naik.
    if (!CSS.supports('scroll-initial-target', 'nearest')) {
        gulir.value.scrollTo({ top: 0, behavior: 'instant' });
        await new Promise((selesai) => requestAnimationFrame(() => requestAnimationFrame(selesai)));
    }

    gulir.value.scrollTo({ top: gulir.value.scrollHeight, behavior: 'auto' });
}

function tutup() {
    // Dialog baru ditutup setelah lembar benar-benar keluar layar (lihat pengamat di bawah),
    // agar gerakan turunnya tetap terlihat.
    gulir.value.scrollTo({ top: 0, behavior: 'auto' });
}

function ketukDiLuar(event) {
    if (!lembar.value.contains(event.target)) {
        tutup();
    }
}

function tekanEsc(event) {
    if (event.cancelable) {
        event.preventDefault();
        tutup();
    }
}

function ikutiGeser() {
    const jarak = gulir.value.scrollHeight - gulir.value.clientHeight;
    const rasio = jarak > 0 ? gulir.value.scrollTop / jarak : 0;
    dialog.value.style.setProperty('--lembar-redup', Math.min(Math.max(rasio, 0), 1));
}

onMounted(() => {
    const ambangTerlihat = 1 / window.innerHeight;

    // Lembar sudah keluar layar (diusap ke bawah, ketuk latar, Esc, atau tombol Tutup): dialog ditutup.
    pengamat = new IntersectionObserver((daftar) => {
        const terakhir = daftar.at(-1);

        if (terakhir.intersectionRatio < ambangTerlihat && dialog.value.open) {
            dialog.value.close();
        }
    }, { root: dialog.value, threshold: [ambangTerlihat] });

    pengamat.observe(lembar.value);

    // Peramban tanpa animasi gulir CSS: tingkat redup latar dihitung dari posisi gulir.
    if (!CSS.supports('animation-timeline: scroll()')) {
        gulir.value.addEventListener('scroll', ikutiGeser, { passive: true });
    }

    // Berpindah halaman dari dalam lembar: lembar langsung ditutup.
    hapusPendengarRouter = router.on('start', () => {
        if (dialog.value?.open) {
            dialog.value.close();
        }
    });
});

onBeforeUnmount(() => {
    pengamat?.disconnect();
    hapusPendengarRouter?.();
});

defineExpose({ buka, tutup });
</script>

<template>
    <dialog :id="props.id" ref="dialog" class="lembar-bawah" :aria-labelledby="props.labelledby"
            @click="ketukDiLuar" @cancel="tekanEsc" @close="emit('tertutup')">
        <div ref="gulir" class="lembar-bawah__gulir">
            <div ref="lembar" class="lembar-bawah__lembar">
                <div class="lembar-bawah__pegangan" aria-hidden="true" />
                <slot :tutup="tutup" />
            </div>
        </div>
    </dialog>
</template>
