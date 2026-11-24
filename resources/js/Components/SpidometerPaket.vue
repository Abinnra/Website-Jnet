<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

/**
 * Spidometer kecepatan di banner halaman Paket sebagai pulau Vue (revisi 23 November 2026).
 * Data dan geometrinya dihitung server di components/spidometer-paket.blade.php dan dibaca oleh
 * resources/js/paket.js, jadi tampilan awalnya sama persis dengan HTML server.
 *
 * Pilihan kecepatan memakai radio asli (fieldset + legend): bisa diketuk, dipilih dengan tombol panah,
 * dan dibacakan pembaca layar beserta harganya. Saat pilihan berganti, jarum berayun (CSS), busur terisi,
 * dan angka kecepatan menghitung naik/turun. Angka kecepatan di busur juga dapat diketuk.
 * Semua gerak dilewati bila pengunjung memilih "kurangi animasi".
 */
const props = defineProps({
    // { busur, garis, nol, periode, awal, paket: [{ id, nama, kecepatan, harga, label, tautan, sudut, isi, tanda, garis }] }
    model: { type: Object, required: true },
});

const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');
const terpilih = ref(props.model.awal);
const pilihan = computed(() => props.model.paket[terpilih.value]);
const angka = ref(pilihan.value.kecepatan);
// Sebelum siap, jarum dan busur berada di nol (CSS); setelah siap keduanya naik ke paket terpilih.
const siap = ref(false);

let bingkai = 0;

function hitungAngka(dari, ke) {
    cancelAnimationFrame(bingkai);

    if (kurangiGerak.matches || dari === ke) {
        angka.value = ke;

        return;
    }

    const mulai = performance.now();
    const durasi = 600;
    const langkah = (sekarang) => {
        const t = Math.min(1, (sekarang - mulai) / durasi);
        angka.value = Math.round(dari + (ke - dari) * (1 - (1 - t) ** 3));

        if (t < 1) {
            bingkai = requestAnimationFrame(langkah);
        }
    };

    bingkai = requestAnimationFrame(langkah);
}

watch(terpilih, (baru, lama) => hitungAngka(props.model.paket[lama].kecepatan, props.model.paket[baru].kecepatan));

onMounted(() => {
    // Dua frame: posisi nol tergambar dulu, baru jarum berayun naik ke paket terpilih.
    requestAnimationFrame(() => requestAnimationFrame(() => {
        siap.value = true;
    }));
});

onBeforeUnmount(() => cancelAnimationFrame(bingkai));
</script>

<template>
    <div class="spido__isi" :class="{ 'spido__isi--siap': siap }">
        <div class="spido__meter" :style="{ '--sudut': `${pilihan.sudut}deg`, '--isi': pilihan.isi }">
            <svg class="spido__gambar" viewBox="0 0 320 232" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="spido-gradien" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#0077C5" />
                        <stop offset=".55" stop-color="#00ADEF" />
                        <stop offset="1" stop-color="#FF6501" />
                    </linearGradient>
                </defs>
                <path class="spido__jalur" :d="props.model.busur" />
                <path class="spido__busur" :d="props.model.busur" pathLength="100" />
                <g class="spido__skala">
                    <line v-for="(garis, nomor) in props.model.garis" :key="nomor" :x1="garis[0]" :y1="garis[1]" :x2="garis[2]" :y2="garis[3]" />
                </g>
                <text class="spido__nol" :x="props.model.nol[0]" :y="props.model.nol[1]">0</text>
                <!-- Pintasan untuk tetikus/sentuhan; keyboard dan pembaca layar memakai radio di bawah. -->
                <g v-for="(item, nomor) in props.model.paket" :key="item.id" class="spido__tanda"
                   :class="{ 'spido__tanda--aktif': nomor === terpilih }" @click="terpilih = nomor">
                    <line class="spido__garis-paket" :x1="item.garis[0]" :y1="item.garis[1]" :x2="item.garis[2]" :y2="item.garis[3]" />
                    <text :x="item.tanda[0]" :y="item.tanda[1]">{{ item.kecepatan }}</text>
                </g>
                <polygon class="spido__jarum" points="146,146 254,148.6 258,150 254,151.4 146,154" />
                <circle class="spido__poros" cx="160" cy="150" r="10" />
                <circle class="spido__poros-dalam" cx="160" cy="150" r="4" />
            </svg>
            <p class="spido__baca"><span class="spido__angka angka-kecepatan">{{ angka }}</span> <span class="spido__satuan">Mbps</span></p>
        </div>

        <fieldset class="spido__pilihan">
            <legend class="visually-hidden">Pilih kecepatan paket</legend>
            <div class="spido__deret">
                <template v-for="(item, nomor) in props.model.paket" :key="item.id">
                    <input :id="`spido-${item.id}`" v-model="terpilih" class="spido__radio" type="radio" name="spido-paket" :value="nomor">
                    <label class="spido__chip" :for="`spido-${item.id}`">{{ item.kecepatan }} Mbps<span class="visually-hidden">, {{ item.harga }}</span></label>
                </template>
            </div>
        </fieldset>

        <div class="spido__bawah">
            <p class="spido__ringkas">
                <span class="spido__nama">{{ pilihan.nama }}<span v-if="pilihan.label" class="spido__label">{{ pilihan.label }}</span></span>
                <strong class="spido__harga">{{ pilihan.harga }}<span v-if="props.model.periode" class="spido__periode">{{ props.model.periode }}</span></strong>
            </p>
            <a v-if="pilihan.tautan" class="uk-button uk-button-secondary spido__aksi" :href="pilihan.tautan">Pilih {{ pilihan.nama }}</a>
        </div>
    </div>
</template>
