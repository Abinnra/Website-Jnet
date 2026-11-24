<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Ubin aksi cepat beranda sebagai pulau Vue di halaman Blade (revisi: tampilan memakai Vue).
 * Isinya dibaca dari HTML server oleh resources/js/beranda.js, jadi tautan, judul, dan ikonnya tetap
 * satu sumber dengan publik/beranda.blade.php. Komponen ini menambahkan umpan balik saat ubin ditekan:
 * ubin dan ikon mengecil lalu memantul kembali (CSS, kelas aksi-cepat__item--ditekan), serta riak cahaya
 * yang menyebar dari titik sentuh. Riak dilewati bila pengunjung memilih "kurangi animasi".
 */
const props = defineProps({
    aksi: { type: Array, required: true }, // [{ tautan, judul, teks, ikon }], ikon berupa SVG dari server
});

const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');
const ditekan = ref(null);
const riak = ref([]);
let nomorRiak = 0;

function tekan(event, item) {
    // Hanya klik utama, sentuhan, atau pena; klik kanan tidak memberi efek tekan.
    if (event.button !== 0) {
        return;
    }

    ditekan.value = item.tautan;

    if (kurangiGerak.matches) {
        return;
    }

    const kotak = event.currentTarget.getBoundingClientRect();
    const x = event.clientX - kotak.left;
    const y = event.clientY - kotak.top;
    // Diameter riak cukup untuk menjangkau sudut ubin yang paling jauh dari titik sentuh.
    const ukuran = 2 * Math.hypot(Math.max(x, kotak.width - x), Math.max(y, kotak.height - y));
    const id = ++nomorRiak;

    riak.value.push({ id, tautan: item.tautan, x, y, ukuran });
    // Cadangan bila animationend tidak terpicu (misalnya CSS belum termuat), agar elemen riak tidak menumpuk.
    window.setTimeout(() => hapusRiak(id), 1000);
}

function lepas() {
    ditekan.value = null;
}

function hapusRiak(id) {
    riak.value = riak.value.filter((r) => r.id !== id);
}

// Kembali lewat tombol Back (bfcache) memulihkan halaman apa adanya, jadi keadaan tekan dibersihkan.
function pulihkan(event) {
    if (event.persisted) {
        lepas();
        riak.value = [];
    }
}

onMounted(() => window.addEventListener('pageshow', pulihkan));
onBeforeUnmount(() => window.removeEventListener('pageshow', pulihkan));
</script>

<template>
    <li v-for="item in props.aksi" :key="item.tautan">
        <a class="aksi-cepat__item" :class="{ 'aksi-cepat__item--ditekan': ditekan === item.tautan }" :href="item.tautan"
           @pointerdown="tekan($event, item)" @pointerup="lepas" @pointercancel="lepas" @pointerleave="lepas" @dragstart="lepas">
            <!-- SVG ikon berasal dari HTML server sendiri (components/ikon-layanan), bukan dari masukan pengguna. -->
            <span class="aksi-cepat__ikon" v-html="item.ikon"></span>
            <span class="aksi-cepat__teks">
                <strong>{{ item.judul }}</strong>
                <span>{{ item.teks }}</span>
            </span>
            <span v-for="r in riak.filter((r) => r.tautan === item.tautan)" :key="r.id" class="aksi-cepat__riak" aria-hidden="true"
                  :style="{ left: `${r.x}px`, top: `${r.y}px`, width: `${r.ukuran}px`, height: `${r.ukuran}px` }"
                  @animationend="hapusRiak(r.id)"></span>
        </a>
    </li>
</template>
