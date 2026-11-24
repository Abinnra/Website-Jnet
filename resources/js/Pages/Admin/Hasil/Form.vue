<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import IsianTeks from '../../../Components/IsianTeks.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import RingkasanGalat from '../../../Components/RingkasanGalat.vue';

const props = defineProps({
    judul: { type: String, required: true },
    keterangan: { type: String, required: true },
    pengajuan: { type: Object, required: true },
    pilihanKeputusan: { type: Array, required: true },
    galatPilihan: { type: Array, required: true },
    unggahan: { type: Object, required: true },
    tautanSimpan: { type: String, required: true },
});

const form = useForm({
    hasil: '',
    catatan: '',
    pesan_untuk_pelanggan: '',
    dokumentasi: null,
});

// Galat "hasil" tentang pilihan tampil di bawah pilihan; galat lain (misalnya jadwal sudah dicatat) di atas.
const galatDiPilihan = computed(() => (props.galatPilihan.includes(form.errors.hasil) ? form.errors.hasil : null));
const galatUmum = computed(() => (form.errors.hasil && !galatDiPilihan.value ? form.errors.hasil : null));

function pilihBerkas(event) {
    form.dokumentasi = event.target.files[0] ?? null;
}

function simpan() {
    form.post(props.tautanSimpan, { preserveScroll: true });
}
</script>

<template>
    <JudulHalaman :judul="judul" :kembali="{ href: pengajuan.tautan, label: `Kembali ke ${pengajuan.nomor}` }">
        {{ keterangan }}
    </JudulHalaman>

    <div v-if="galatUmum" class="app-pesan app-pesan--gagal" role="alert">
        <Ikon nama="peringatan" />
        <p class="app-pesan__isi">{{ galatUmum }}</p>
    </div>

    <div class="app-grid app-grid--sisi">
        <form class="app-kartu app-formulir" novalidate @submit.prevent="simpan">
            <RingkasanGalat :galat="form.errors" />

            <fieldset class="pilihan-kartu" :aria-describedby="galatDiPilihan ? 'galat-hasil' : undefined">
                <legend>Hasil pemasangan</legend>
                <label v-for="(keputusan, indeks) in pilihanKeputusan" :key="keputusan.nilai" class="pilihan-kartu__item">
                    <input :id="indeks === 0 ? 'isian-hasil' : undefined" v-model="form.hasil" type="radio" class="uk-radio" name="hasil"
                           :value="keputusan.nilai" required>
                    <span>
                        <span class="pilihan-kartu__judul">{{ keputusan.label }}</span>
                        <span class="pilihan-kartu__teks">{{ keputusan.keterangan }}</span>
                    </span>
                </label>
                <p v-if="galatDiPilihan" id="galat-hasil" class="isian__galat"><Ikon nama="peringatan" /> {{ galatDiPilihan }}</p>
            </fieldset>

            <IsianTeks id="isian-catatan" v-model="form.catatan" label="Uraian hasil" rows="4" maxlength="2000" :galat="form.errors.catatan"
                       bantuan="Contoh: perangkat yang dipasang, atau alasan lokasi tidak layak (wajib untuk tidak layak). Hanya terlihat oleh admin." />

            <IsianTeks id="isian-pesan_untuk_pelanggan" v-model="form.pesan_untuk_pelanggan" label="Pesan untuk pelanggan" opsional rows="3"
                       maxlength="1000" :galat="form.errors.pesan_untuk_pelanggan"
                       bantuan="Tampil di halaman pengajuan pelanggan, misalnya alasan lokasi tidak layak dipasang. Kosongkan jika tidak perlu." />

            <div class="isian">
                <label class="isian__label" for="isian-dokumentasi">Dokumentasi <span class="isian__opsional">(opsional)</span></label>
                <input id="isian-dokumentasi" type="file" class="uk-input isian__berkas" :accept="unggahan.accept"
                       :class="{ 'uk-form-danger': form.errors.dokumentasi }" :aria-invalid="form.errors.dokumentasi ? 'true' : undefined"
                       :aria-describedby="form.errors.dokumentasi ? 'isian-dokumentasi-galat isian-dokumentasi-bantuan' : 'isian-dokumentasi-bantuan'"
                       @change="pilihBerkas">
                <p v-if="form.errors.dokumentasi" id="isian-dokumentasi-galat" class="isian__galat"><Ikon nama="peringatan" /> {{ form.errors.dokumentasi }}</p>
                <p id="isian-dokumentasi-bantuan" class="isian__bantuan">
                    Foto atau PDF, maksimal {{ unggahan.maksMb }} MB. File disimpan di folder privat dan hanya dapat dibuka admin.
                </p>
                <progress v-if="form.progress" :value="form.progress.percentage" max="100">{{ form.progress.percentage }}%</progress>
            </div>

            <div class="app-formulir__aksi">
                <button type="submit" class="uk-button uk-button-primary" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan…' : 'Simpan hasil' }}
                </button>
                <Link :href="pengajuan.tautan" class="uk-button uk-button-default">Batal</Link>
            </div>
        </form>

        <aside class="app-kartu">
            <h2 class="app-kartu__judul app-kartu__kepala">Setelah disimpan</h2>
            <ul class="app-tumpuk" style="margin: 0; padding-left: 20px">
                <li>Jadwal ini berstatus <strong>Selesai</strong> dan tidak dapat dicatat ulang.</li>
                <li><strong>Pemasangan berhasil</strong>: pengajuan menjadi Terpasang. <strong>Tidak layak dipasang</strong>: pengajuan berhenti.</li>
                <li>Jika kunjungan batal atau ditunda, jangan catat hasil: ubah tanggal jadwal dari detail pengajuan.</li>
            </ul>
        </aside>
    </div>
</template>
