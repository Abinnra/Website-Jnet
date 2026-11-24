<script setup>
import { useForm } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import Isian from '../../../Components/Isian.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import RingkasanGalat from '../../../Components/RingkasanGalat.vue';

const props = defineProps({
    email: { type: String, default: '' },
    tautanSimpan: { type: String, required: true },
});

const form = useForm({ email: props.email });

function simpan() {
    form.post(props.tautanSimpan);
}
</script>

<template>
    <!-- Revisi 5 (UML): Use Case "Mengatur Ulang Kata Sandi Pelanggan" -->
    <JudulHalaman judul="Atur ulang kata sandi pelanggan">Untuk pelanggan yang lupa kata sandi akunnya.</JudulHalaman>

    <div class="app-pesan app-pesan--info" role="note">
        <Ikon nama="info" />
        <p class="app-pesan__isi">
            Sistem membuat <strong>kata sandi sementara</strong> yang hanya ditampilkan satu kali. Kirimkan kepada pelanggan, misalnya
            lewat WhatsApp. Saat masuk, pelanggan wajib membuat kata sandi baru. Kata sandi lama langsung tidak berlaku dan pelanggan
            otomatis keluar dari semua perangkat.
        </p>
    </div>

    <form class="app-kartu app-formulir app-formulir--sempit" novalidate @submit.prevent="simpan">
        <RingkasanGalat :galat="form.errors" />
        <Isian id="isian-email" v-model="form.email" label="Email akun pelanggan" type="email" required autofocus autocomplete="off"
               spellcheck="false" :galat="form.errors.email"
               bantuan="Email yang dipakai pelanggan untuk masuk. Pastikan permintaan benar-benar dari pemilik akun, misalnya dengan mencocokkan nama dan nomor WhatsApp di pengajuannya." />
        <div class="app-formulir__aksi">
            <button type="submit" class="uk-button uk-button-primary" :disabled="form.processing">
                <Ikon nama="kunci" /> {{ form.processing ? 'Memproses…' : 'Buat kata sandi sementara' }}
            </button>
        </div>
    </form>
</template>
