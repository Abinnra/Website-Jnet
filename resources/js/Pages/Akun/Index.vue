<script setup>
import { Link } from '@inertiajs/vue3';
import Ikon from '../../Components/Ikon.vue';
import JudulHalaman from '../../Components/JudulHalaman.vue';
import Kosong from '../../Components/Kosong.vue';
import Lencana from '../../Components/Lencana.vue';

defineProps({
    email: { type: String, required: true },
    labelSandi: { type: String, required: true },
    tautanSandi: { type: String, required: true },
    tautanAjukan: { type: String, required: true },
    pengajuan: { type: Array, required: true },
});
</script>

<template>
    <JudulHalaman judul="Pengajuan saya">
        Masuk sebagai {{ email }}
        <span aria-hidden="true"> · </span>
        <!-- Pengaturan akun cukup berupa tautan kecil, bukan menu utama -->
        <Link :href="tautanSandi">{{ labelSandi }}</Link>
        <template v-if="pengajuan.length" #aksi>
            <!-- Formulir pengajuan berupa halaman biasa (peta lokasi dan captcha), dibuka dengan muat ulang penuh -->
            <a :href="tautanAjukan" class="uk-button uk-button-primary"><Ikon nama="tambah" /> Ajukan pemasangan</a>
        </template>
    </JudulHalaman>

    <Kosong v-if="!pengajuan.length" judul="Anda belum memiliki pengajuan" ikon="pengajuan">
        Pilih paket yang sesuai, lalu ajukan pemasangan untuk alamat Anda.
        <template #aksi>
            <a :href="tautanAjukan" class="uk-button uk-button-primary"><Ikon nama="tambah" /> Ajukan pemasangan</a>
        </template>
    </Kosong>

    <ul v-else class="app-pengajuan-saya">
        <li v-for="item in pengajuan" :key="item.tautan">
            <Link :href="item.tautan" class="app-pengajuan-saya__item">
                <div>
                    <p class="app-pengajuan-saya__nomor">{{ item.nomor }}</p>
                    <p class="app-pengajuan-saya__paket">{{ item.ringkas }}</p>
                    <p class="app-pengajuan-saya__tanggal">{{ item.diajukan }}</p>
                    <p v-if="item.adaPesan" class="app-pengajuan-saya__pesan"><Ikon nama="chat" class="app-ikon--kecil" /> Ada pesan dari JNET</p>
                </div>
                <Lencana :status="item.status" />
            </Link>
        </li>
    </ul>
</template>
