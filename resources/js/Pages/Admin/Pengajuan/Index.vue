<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import Kosong from '../../../Components/Kosong.vue';
import Lencana from '../../../Components/Lencana.vue';
import NomorPengajuan from '../../../Components/NomorPengajuan.vue';
import Paginasi from '../../../Components/Paginasi.vue';

const props = defineProps({
    filter: { type: Object, required: true },
    saringStatus: { type: Array, required: true },
    saringWilayah: { type: Array, required: true },
    tautanCari: { type: String, required: true },
    tautanHapusCari: { type: String, required: true },
    adaFilter: { type: Boolean, required: true },
    pengajuan: { type: Array, required: true },
    paginasi: { type: Object, required: true },
});

const kataKunci = ref(props.filter.q ?? '');
const memuat = ref(false);
let jeda = 0;

/**
 * Pencarian langsung saat mengetik (jeda 400 ms) atau saat tombol Cari ditekan. Filter status dan
 * wilayah yang sedang dipakai ikut dikirim; alamat di browser ikut berubah tanpa menambah riwayat.
 */
function cari(segera = false) {
    clearTimeout(jeda);

    const kirim = () => {
        const data = { status: props.filter.status, wilayah: props.filter.wilayah, q: kataKunci.value.trim() };

        router.get(props.tautanCari, Object.fromEntries(Object.entries(data).filter(([, nilai]) => nilai)), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => { memuat.value = true; },
            onFinish: () => { memuat.value = false; },
        });
    };

    if (segera) {
        kirim();
    } else {
        jeda = setTimeout(kirim, 400);
    }
}

watch(() => props.filter.q, (q) => {
    if ((q ?? '') !== kataKunci.value.trim()) {
        kataKunci.value = q ?? '';
    }
});

onBeforeUnmount(() => clearTimeout(jeda));
</script>

<template>
    <JudulHalaman judul="Pengajuan">Periksa data calon pelanggan dan perbarui status pengajuan.</JudulHalaman>

    <nav class="app-saring" aria-label="Filter status">
        <Link v-for="item in saringStatus" :key="item.label" :href="item.tautan" class="app-saring__item" preserve-scroll
              :aria-current="item.aktif ? 'page' : undefined">
            {{ item.label }} <span class="app-saring__jumlah">{{ item.jumlah }}</span>
        </Link>
    </nav>

    <nav class="app-saring app-saring--sekunder" aria-label="Filter wilayah">
        <Link v-for="item in saringWilayah" :key="item.label" :href="item.tautan" class="app-saring__item" preserve-scroll
              :aria-current="item.aktif ? 'page' : undefined">
            {{ item.label }} <span v-if="item.jumlah !== null" class="app-saring__jumlah">{{ item.jumlah }}</span>
        </Link>
    </nav>

    <form class="app-cari" role="search" @submit.prevent="cari(true)">
        <div class="app-cari__kolom">
            <label class="visually-hidden" for="cari-pengajuan">Cari pengajuan</label>
            <Ikon nama="cari" />
            <input id="cari-pengajuan" v-model="kataKunci" type="search" class="uk-input" autocomplete="off" spellcheck="false"
                   enterkeyhint="search" placeholder="Cari nomor, nama, WhatsApp, email, alamat, atau wilayah" @input="cari()">
        </div>
        <button type="submit" class="uk-button uk-button-primary">Cari</button>
        <Link v-if="filter.q" :href="tautanHapusCari" class="uk-button uk-button-default" preserve-scroll>Hapus pencarian</Link>
    </form>

    <div :class="{ 'app-memuat': memuat }" :aria-busy="memuat ? 'true' : 'false'">
        <Kosong v-if="!pengajuan.length" ikon="pengajuan" :judul="adaFilter ? 'Tidak ada pengajuan yang cocok' : 'Belum ada pengajuan'">
            {{ adaFilter ? 'Coba kata kunci lain atau pilih filter "Semua".' : 'Pengajuan dari calon pelanggan akan muncul di sini.' }}
        </Kosong>

        <template v-else>
            <div class="app-kartu app-kartu--tanpa-isi">
                <div class="app-tabel-wadah" role="region" aria-labelledby="judul-tabel-pengajuan" tabindex="0">
                    <table class="app-tabel">
                        <caption id="judul-tabel-pengajuan" class="visually-hidden">Daftar pengajuan</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nomor</th>
                                <th scope="col">Pemohon</th>
                                <th scope="col">Paket</th>
                                <th scope="col">Lokasi</th>
                                <th scope="col">Diajukan</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="visually-hidden">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in pengajuan" :key="item.nomor">
                                <th scope="row" data-label="Nomor"><NomorPengajuan :nomor="item.nomor" /></th>
                                <td data-label="Pemohon">
                                    <div>
                                        <span class="app-tabel__utama">{{ item.nama }}</span>
                                        <span class="app-tabel__sekunder">{{ item.whatsapp }}</span>
                                        <span v-if="item.catatan" class="app-catatan">
                                            <span class="app-catatan__label">Ada catatan:</span> {{ item.catatan }}
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Paket">{{ item.paket }}</td>
                                <td data-label="Lokasi">
                                    <div>
                                        <span class="app-tabel__utama">{{ item.kecamatan }}</span>
                                        <span class="app-tabel__sekunder">{{ item.kota }}</span>
                                        <span v-if="item.luarWilayah" class="lencana lencana--luar">{{ item.luarWilayah }}</span>
                                    </div>
                                </td>
                                <td data-label="Diajukan">{{ item.diajukan }}</td>
                                <td data-label="Status"><Lencana :status="item.status" /></td>
                                <td class="app-tabel__sel-aksi">
                                    <div class="app-tabel__aksi">
                                        <Link :href="item.tautan" class="uk-button uk-button-default uk-button-small">
                                            Detail<span class="visually-hidden"> {{ item.nomor }}</span>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <Paginasi :paginasi="paginasi" satuan="pengajuan" />
        </template>
    </div>
</template>
