<script setup>
import { Link } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import Kosong from '../../../Components/Kosong.vue';

defineProps({
    paket: { type: Array, required: true },
    catatanBiaya: { type: String, default: null },
    tautanTambah: { type: String, required: true },
});
</script>

<template>
    <JudulHalaman judul="Paket internet">
        Paket aktif tampil di website dan dapat dipilih saat pengajuan.
        <template #aksi>
            <Link :href="tautanTambah" class="uk-button uk-button-primary"><Ikon nama="tambah" /> Tambah paket</Link>
        </template>
    </JudulHalaman>

    <Kosong v-if="!paket.length" judul="Belum ada paket" ikon="paket">
        Tambahkan paket pertama agar calon pelanggan dapat memilihnya.
        <template #aksi>
            <Link :href="tautanTambah" class="uk-button uk-button-primary"><Ikon nama="tambah" /> Tambah paket</Link>
        </template>
    </Kosong>

    <template v-else>
        <div class="app-kartu app-kartu--tanpa-isi">
            <div class="app-tabel-wadah" role="region" aria-labelledby="judul-tabel-paket" tabindex="0">
                <table class="app-tabel">
                    <caption id="judul-tabel-paket" class="visually-hidden">Daftar paket internet</caption>
                    <thead>
                        <tr>
                            <th scope="col">Paket</th>
                            <th scope="col" class="app-tabel__angka">Kecepatan</th>
                            <th scope="col" class="app-tabel__angka">Harga</th>
                            <th scope="col" class="app-tabel__angka">Biaya pasang</th>
                            <th scope="col" class="app-tabel__angka">Pengajuan</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in paket" :key="item.id" :class="{ 'app-tabel__redup': !item.aktif }">
                            <th scope="row" data-label="Paket">
                                <div class="app-baris">
                                    {{ item.nama }}
                                    <span v-if="item.label" class="lencana lencana--info">{{ item.label }}</span>
                                </div>
                            </th>
                            <td class="app-tabel__angka" data-label="Kecepatan">{{ item.kecepatan }}</td>
                            <td class="app-tabel__angka" data-label="Harga">{{ item.harga }}</td>
                            <td class="app-tabel__angka" data-label="Biaya pasang">{{ item.biayaPasang }}</td>
                            <td class="app-tabel__angka" data-label="Pengajuan">{{ item.jumlahPengajuan }}</td>
                            <td data-label="Status">
                                <span class="lencana" :class="item.aktif ? 'lencana--sukses' : 'lencana--redup'">{{ item.aktif ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="app-tabel__sel-aksi">
                                <div class="app-tabel__aksi">
                                    <Link :href="item.tautanUbah" class="uk-button uk-button-default uk-button-small">
                                        <Ikon nama="ubah" /> Ubah<span class="visually-hidden"> {{ item.nama }}</span>
                                    </Link>
                                    <Link :href="item.tautanStatus" method="patch" as="button" preserve-scroll
                                          class="uk-button uk-button-default uk-button-small">
                                        {{ item.aktif ? 'Nonaktifkan' : 'Aktifkan' }}<span class="visually-hidden"> {{ item.nama }}</span>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="app-kecil app-teks-redup" style="margin-top: 14px">
            Paket tidak dapat dihapus karena pengajuan lama tetap merujuk ke paketnya. Nonaktifkan paket yang tidak dijual lagi.
            <template v-if="catatanBiaya"> {{ catatanBiaya }}</template>
        </p>
    </template>
</template>
