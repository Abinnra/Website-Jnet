<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Ikon from './Ikon.vue';
import LembarBawah from './LembarBawah.vue';

/**
 * Navigasi panel admin di HP dan tablet (di bawah 1024px). Menu harian (Dashboard, Pengajuan, Jadwal)
 * ada di bilah bawah yang terjangkau ibu jari, satu ketukan untuk berpindah halaman. Tombol "Lainnya"
 * membuka lembar bawah berisi menu lain, akun yang login, dan Keluar. Layar lebar memakai sidebar.
 */
const page = usePage();
const aplikasi = computed(() => page.props.aplikasi);
const menuUtama = computed(() => aplikasi.value.menu.filter((item) => item.utama));
const menuLain = computed(() => aplikasi.value.menu.filter((item) => !item.utama));
// Halaman yang sedang dibuka ada di lembar "Lainnya": tombol Lainnya ikut ditandai aktif.
const lainAktif = computed(() => menuLain.value.find((item) => item.aktif) ?? null);

const lembar = ref(null);
const lembarTerbuka = ref(false);
</script>

<template>
    <nav class="app-bawah" aria-label="Menu admin" data-navigasi>
        <ul class="app-bawah__daftar">
            <li v-for="item in menuUtama" :key="item.href">
                <Link :href="item.href" class="app-bawah__item" :aria-current="item.aktif ? 'page' : undefined">
                    <span class="app-bawah__ikon">
                        <Ikon :nama="item.ikon" />
                        <span v-if="item.jumlah" class="app-bawah__jumlah" aria-hidden="true">{{ item.jumlah > 99 ? '99+' : item.jumlah }}</span>
                    </span>
                    <span class="app-bawah__label">{{ item.label }}</span>
                    <span v-if="item.jumlah" class="visually-hidden">, {{ item.jumlah }} menunggu verifikasi</span>
                </Link>
            </li>
            <li>
                <button type="button" class="app-bawah__item" :class="{ 'app-bawah__item--aktif': lainAktif }"
                        aria-haspopup="dialog" aria-controls="lembar-lainnya" :aria-expanded="lembarTerbuka ? 'true' : 'false'"
                        @click="lembar.buka()">
                    <span class="app-bawah__ikon"><Ikon nama="lainnya" /></span>
                    <span class="app-bawah__label">Lainnya</span>
                    <span v-if="lainAktif" class="visually-hidden">, sedang membuka {{ lainAktif.label }}</span>
                </button>
            </li>
        </ul>
    </nav>

    <LembarBawah id="lembar-lainnya" ref="lembar" labelledby="judul-lembar-lainnya" data-navigasi
                 @terbuka="lembarTerbuka = true" @tertutup="lembarTerbuka = false">
        <template #default="{ tutup }">
            <div class="lembar-bawah__kepala">
                <h2 id="judul-lembar-lainnya" class="lembar-bawah__judul">Menu lainnya</h2>
                <button type="button" class="lembar-bawah__tutup" aria-label="Tutup menu" autofocus @click="tutup">
                    <Ikon nama="tutup" />
                </button>
            </div>

            <nav aria-label="Menu admin lainnya">
                <ul class="app-lainnya">
                    <li v-for="item in menuLain" :key="item.href">
                        <Link :href="item.href" class="app-lainnya__tautan" :aria-current="item.aktif ? 'page' : undefined">
                            <span class="app-lainnya__ikon"><Ikon :nama="item.ikon" /></span>
                            <span class="app-lainnya__label">{{ item.label }}</span>
                            <span v-if="item.aktif" class="app-lainnya__penanda">Sedang dibuka</span>
                            <Ikon v-else nama="berikutnya" class="app-lainnya__panah" />
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Akun dan Keluar dipisah dari menu agar tidak tertekan tanpa sengaja -->
            <div class="app-lainnya__akun">
                <div class="app-pengguna app-pengguna--terang">
                    <span class="app-pengguna__inisial" aria-hidden="true">{{ aplikasi.akun.inisial }}</span>
                    <span class="app-pengguna__teks">
                        <span class="app-pengguna__nama">{{ aplikasi.akun.nama }}</span>
                        <span class="app-pengguna__info">{{ aplikasi.akun.username }}</span>
                    </span>
                </div>
                <div class="app-lainnya__tombol">
                    <a :href="aplikasi.tautan.website" class="uk-button uk-button-default" target="_blank" rel="noopener">
                        <Ikon nama="tautan-luar" /> Lihat website
                    </a>
                    <Link :href="aplikasi.tautan.keluar" method="post" as="button" class="uk-button tombol--bahaya-garis">
                        <Ikon nama="keluar" /> Keluar
                    </Link>
                </div>
            </div>
        </template>
    </LembarBawah>
</template>
