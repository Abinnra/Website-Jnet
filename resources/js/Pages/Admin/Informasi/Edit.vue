<script setup>
import { useForm } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import Isian from '../../../Components/Isian.vue';
import IsianTeks from '../../../Components/IsianTeks.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import RingkasanGalat from '../../../Components/RingkasanGalat.vue';

const props = defineProps({
    kelompok: { type: Array, required: true },
    nilai: { type: Object, required: true },
    tautanSimpan: { type: String, required: true },
    tautanWebsite: { type: String, required: true },
});

// Hanya isian yang ditampilkan di formulir ini yang dikirim (sama seperti formulir Blade sebelumnya).
const kunciIsian = props.kelompok.flatMap((bagian) => bagian.isian.map((isian) => isian.kunci));
const form = useForm(Object.fromEntries(kunciIsian.map((kunci) => [kunci, props.nilai[kunci] ?? ''])));

function simpan() {
    form.put(props.tautanSimpan, { preserveScroll: true });
}
</script>

<template>
    <JudulHalaman judul="Informasi website">
        Perubahan langsung tampil di halaman publik. Kosongkan isian yang belum tersedia; bagian terkait akan disembunyikan.
        <template #aksi>
            <a :href="tautanWebsite" class="uk-button uk-button-default" target="_blank" rel="noopener">
                <Ikon nama="tautan-luar" /> Lihat website
            </a>
        </template>
    </JudulHalaman>

    <form class="app-tumpuk app-tumpuk--besar" novalidate @submit.prevent="simpan">
        <RingkasanGalat :galat="form.errors" />

        <fieldset v-for="bagian in kelompok" :key="bagian.judul" class="app-kartu app-bagian-formulir">
            <legend>{{ bagian.judul }}</legend>
            <div v-for="isian in bagian.isian" :key="isian.kunci" class="app-tumpuk" style="gap: 6px">
                <IsianTeks v-if="isian.tipe === 'textarea'" :id="`isian-${isian.kunci}`" v-model="form[isian.kunci]" :label="isian.label"
                           :rows="isian.baris" :galat="form.errors[isian.kunci]" :bantuan="isian.bantuan" />
                <Isian v-else :id="`isian-${isian.kunci}`" v-model="form[isian.kunci]" :label="isian.label" :type="isian.tipe"
                       :required="isian.wajib" :galat="form.errors[isian.kunci]" :bantuan="isian.bantuan"
                       :inputmode="isian.tipe === 'tel' ? 'tel' : undefined" :autocomplete="isian.tipe === 'email' ? 'off' : undefined" />
                <p v-if="isian.riwayat" class="isian__riwayat">{{ isian.riwayat }}</p>
            </div>
        </fieldset>

        <div class="app-formulir__aksi">
            <button type="submit" class="uk-button uk-button-primary" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan…' : 'Simpan informasi' }}
            </button>
        </div>
    </form>
</template>
