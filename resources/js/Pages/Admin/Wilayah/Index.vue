<script setup>
import { useForm } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import KartuWilayah from '../../../Components/KartuWilayah.vue';
import Kosong from '../../../Components/Kosong.vue';

const props = defineProps({
    kota: { type: Array, required: true },
    tautanTambahKota: { type: String, required: true },
    tautanJangkauan: { type: String, required: true },
});

const formKota = useForm({ nama_kota: '' });

function tambahKota() {
    formKota.post(props.tautanTambahKota, {
        preserveScroll: true,
        onSuccess: () => formKota.reset(),
    });
}
</script>

<template>
    <JudulHalaman judul="Wilayah layanan">
        Kota/kabupaten dan kecamatan di sini tampil di halaman Jangkauan dan menjadi pilihan kecamatan saat pelanggan mengajukan
        pemasangan. Pengajuan dari kota/kabupaten lain ditandai "luar wilayah".
        <template #aksi>
            <a :href="tautanJangkauan" class="uk-button uk-button-default" target="_blank" rel="noopener">
                <Ikon nama="tautan-luar" /> Lihat halaman Jangkauan
            </a>
        </template>
    </JudulHalaman>

    <div class="app-tumpuk app-tumpuk--besar">
        <Kosong v-if="!kota.length" judul="Belum ada wilayah layanan" ikon="wilayah">
            Tambahkan kota/kabupaten pertama, lalu tambahkan kecamatannya.
        </Kosong>

        <KartuWilayah v-for="item in kota" :key="item.id" :kota="item" />

        <section id="tambah-kota" class="app-kartu" aria-labelledby="judul-tambah-kota">
            <h2 id="judul-tambah-kota" class="app-wilayah__judul app-kartu__kepala">Tambah kota/kabupaten</h2>
            <form novalidate @submit.prevent="tambahKota">
                <div class="isian">
                    <label class="isian__label" for="kota-baru">Nama kota/kabupaten</label>
                    <div class="app-form-baris">
                        <input id="kota-baru" v-model="formKota.nama_kota" type="text" class="uk-input" maxlength="100" required
                               placeholder="Contoh: Kabupaten Lumajang" :class="{ 'uk-form-danger': formKota.errors.nama_kota }"
                               :aria-invalid="formKota.errors.nama_kota ? 'true' : undefined"
                               :aria-describedby="formKota.errors.nama_kota ? 'kota-baru-galat kota-baru-bantuan' : 'kota-baru-bantuan'">
                        <button type="submit" class="uk-button uk-button-primary" :disabled="formKota.processing">
                            <Ikon nama="tambah" /> {{ formKota.processing ? 'Menambahkan…' : 'Tambah kota/kabupaten' }}
                        </button>
                    </div>
                    <p v-if="formKota.errors.nama_kota" id="kota-baru-galat" class="isian__galat"><Ikon nama="peringatan" /> {{ formKota.errors.nama_kota }}</p>
                    <p id="kota-baru-bantuan" class="isian__bantuan">Pengajuan dari kota/kabupaten ini tidak lagi ditandai "luar wilayah".</p>
                </div>
            </form>
        </section>
    </div>
</template>
