<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import PesanFlash from '../Components/PesanFlash.vue';
import TombolCs from '../Components/TombolCs.vue';

/**
 * Kerangka halaman akun pelanggan. Navbar sama dengan situs publik (kelas dari app.css), sehingga
 * pelanggan tidak merasa berpindah situs. Di HP menu dibuka dengan tombol Menu dan ditutup dengan Esc.
 */
const page = usePage();
const aplikasi = computed(() => page.props.aplikasi);
const menuTerbuka = ref(false);
let hapusPendengar = null;

function tekanTombol(event) {
    if (event.key === 'Escape' && menuTerbuka.value) {
        menuTerbuka.value = false;
        document.getElementById('tombol-menu-akun')?.focus();
    }
}

onMounted(() => {
    document.addEventListener('keydown', tekanTombol);
    hapusPendengar = router.on('start', () => { menuTerbuka.value = false; });
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', tekanTombol);
    hapusPendengar?.();
});
</script>

<template>
    <div class="app-akun">
        <a class="lewati" href="#konten">Langsung ke konten</a>

        <header class="kepala kepala--akun" :data-terbuka="menuTerbuka ? 'true' : 'false'">
            <div class="uk-container kepala__isi">
                <a class="kepala__logo" :href="aplikasi.tautan.beranda">
                    <img :src="aplikasi.tautan.logo" alt="JNET – beranda" width="126" height="48">
                </a>

                <button id="tombol-menu-akun" class="kepala__tombol-menu" type="button" aria-controls="menu-akun"
                        :aria-expanded="menuTerbuka ? 'true' : 'false'" @click="menuTerbuka = !menuTerbuka">
                    <span class="kepala__ikon-menu" aria-hidden="true"></span>
                    <span>Menu</span>
                </button>

                <nav id="menu-akun" class="kepala__menu" aria-label="Menu akun">
                    <ul class="menu">
                        <li v-for="item in aplikasi.menu" :key="item.href">
                            <Link v-if="item.inertia" class="menu__tautan" :href="item.href" :aria-current="item.aktif ? 'page' : undefined">
                                {{ item.label }}
                            </Link>
                            <a v-else class="menu__tautan" :href="item.href">{{ item.label }}</a>
                        </li>
                    </ul>
                    <div class="kepala__akun">
                        <span class="kepala__email">{{ aplikasi.akun.email }}</span>
                        <Link :href="aplikasi.tautan.keluar" method="post" as="button" class="uk-button uk-button-default uk-button-small">
                            Keluar
                        </Link>
                    </div>
                </nav>
            </div>
        </header>

        <main id="konten" class="app-akun__isi" tabindex="-1">
            <div class="uk-container">
                <PesanFlash />
                <slot />
            </div>
        </main>

        <TombolCs v-if="aplikasi.cs?.tautan" :cs="aplikasi.cs" />
    </div>
</template>
