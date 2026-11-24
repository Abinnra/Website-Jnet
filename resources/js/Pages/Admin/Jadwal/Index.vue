<script setup>
import { Link } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import Kosong from '../../../Components/Kosong.vue';
import Lencana from '../../../Components/Lencana.vue';
import NomorPengajuan from '../../../Components/NomorPengajuan.vue';
import Paginasi from '../../../Components/Paginasi.vue';

defineProps({
    saringTampilan: { type: Array, required: true },
    teksKosong: { type: String, required: true },
    jadwal: { type: Array, required: true },
    paginasi: { type: Object, required: true },
});
</script>

<template>
    <JudulHalaman judul="Jadwal">Jadwal kunjungan pemasangan. Jadwal baru dibuat dari halaman detail pengajuan.</JudulHalaman>

    <nav class="app-saring" aria-label="Waktu jadwal">
        <Link v-for="item in saringTampilan" :key="item.label" :href="item.tautan" class="app-saring__item" preserve-scroll
              :aria-current="item.aktif ? 'page' : undefined">{{ item.label }}</Link>
    </nav>

    <Kosong v-if="!jadwal.length" judul="Tidak ada jadwal" ikon="jadwal">{{ teksKosong }}</Kosong>

    <template v-else>
        <div class="app-kartu app-kartu--tanpa-isi">
            <div class="app-tabel-wadah" role="region" aria-labelledby="judul-tabel-jadwal" tabindex="0">
                <table class="app-tabel">
                    <caption id="judul-tabel-jadwal" class="visually-hidden">Daftar jadwal</caption>
                    <thead>
                        <tr>
                            <th scope="col">Tanggal dan jam</th>
                            <th scope="col">Pengajuan</th>
                            <th scope="col">Lokasi</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in jadwal" :key="item.id">
                            <th scope="row" data-label="Tanggal">
                                <div>
                                    <span class="app-tabel__utama">{{ item.tanggal }}</span>
                                    <span class="app-tabel__sekunder">{{ item.jam }}</span>
                                    <span v-if="item.jenisLama" class="app-tabel__sekunder">{{ item.jenisLama }}</span>
                                </div>
                            </th>
                            <td data-label="Pengajuan">
                                <div>
                                    <span class="app-tabel__utama"><NomorPengajuan :nomor="item.nomor" /></span>
                                    <span class="app-tabel__sekunder">{{ item.nama }}</span>
                                </div>
                            </td>
                            <td data-label="Lokasi">{{ item.lokasi }}</td>
                            <td data-label="Status"><Lencana :status="item.status" /></td>
                            <td class="app-tabel__sel-aksi">
                                <div class="app-tabel__aksi">
                                    <Link v-if="item.tautanCatatHasil" :href="item.tautanCatatHasil" class="uk-button uk-button-primary uk-button-small">
                                        <Ikon nama="catat" /> Catat hasil<span class="visually-hidden"> {{ item.nomor }}</span>
                                    </Link>
                                    <Link v-if="item.tautanUbah" :href="item.tautanUbah" class="uk-button uk-button-default uk-button-small">
                                        Ubah<span class="visually-hidden"> jadwal {{ item.nomor }}</span>
                                    </Link>
                                    <Link :href="item.tautanPengajuan" class="uk-button uk-button-default uk-button-small">
                                        Pengajuan<span class="visually-hidden"> {{ item.nomor }}</span>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Paginasi :paginasi="paginasi" satuan="jadwal" />
    </template>
</template>
