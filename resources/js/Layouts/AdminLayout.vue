<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import IsiSisiAdmin from '../Components/IsiSisiAdmin.vue';
import NavigasiBawahAdmin from '../Components/NavigasiBawahAdmin.vue';
import PesanFlash from '../Components/PesanFlash.vue';

/**
 * Kerangka panel admin. Layar lebar: sidebar navy di kiri. HP dan tablet: bar atas berisi merek dan
 * bilah navigasi bawah yang terjangkau ibu jari (NavigasiBawahAdmin). Layout tetap hidup antarhalaman
 * (persistent layout).
 */
const page = usePage();
const aplikasi = computed(() => page.props.aplikasi);
const konten = ref(null);
let hapusPendengarNavigasi = null;

onMounted(() => {
    let komponen = page.component;

    // Setelah berpindah halaman lewat menu, fokus dipindah ke konten utama agar pembaca layar langsung
    // membacakan halaman baru. Filter dan pencarian di halaman yang sama tidak memindahkan fokus.
    hapusPendengarNavigasi = router.on('navigate', (event) => {
        const komponenBaru = event.detail.page.component;

        if (komponenBaru === komponen) {
            return;
        }

        komponen = komponenBaru;
        nextTick(() => {
            const aktif = document.activeElement;

            if (!aktif || aktif === document.body || aktif.closest('[data-navigasi]')) {
                konten.value?.focus({ preventScroll: true });
            }
        });
    });
});

onBeforeUnmount(() => hapusPendengarNavigasi?.());
</script>

<template>
    <div class="app-admin">
        <a class="lewati" href="#konten">Langsung ke konten</a>

        <header class="app-atas">
            <Link :href="aplikasi.tautan.beranda" class="app-atas__merek">
                <img :src="aplikasi.tautan.logo" alt="JNET – dashboard admin" width="90" height="34">
                <span class="app-atas__peran">Admin</span>
            </Link>
        </header>

        <aside class="app-sisi" data-navigasi>
            <div class="app-sisi__isi">
                <IsiSisiAdmin />
            </div>
        </aside>

        <main id="konten" ref="konten" class="app-isi" tabindex="-1">
            <div class="app-isi__dalam">
                <PesanFlash />
                <slot />
            </div>
        </main>

        <NavigasiBawahAdmin />
    </div>
</template>
