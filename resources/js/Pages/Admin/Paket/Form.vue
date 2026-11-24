<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Centang from '../../../Components/Centang.vue';
import Ikon from '../../../Components/Ikon.vue';
import Isian from '../../../Components/Isian.vue';
import IsianTeks from '../../../Components/IsianTeks.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import RingkasanGalat from '../../../Components/RingkasanGalat.vue';

const props = defineProps({
    modeUbah: { type: Boolean, required: true },
    judul: { type: String, required: true },
    jumlahPengajuan: { type: Number, required: true },
    paket: { type: Object, required: true },
    tautanSimpan: { type: String, required: true },
    tautanKembali: { type: String, required: true },
});

const form = useForm({ ...props.paket });

function simpan() {
    // Kotak centang dikirim sebagai 1/0, sama seperti formulir biasa.
    form.transform((data) => ({ ...data, status_aktif: data.status_aktif ? 1 : 0 }));

    if (props.modeUbah) {
        form.put(props.tautanSimpan, { preserveScroll: true });
    } else {
        form.post(props.tautanSimpan, { preserveScroll: true });
    }
}
</script>

<template>
    <JudulHalaman :judul="judul" :kembali="{ href: tautanKembali, label: 'Kembali ke daftar paket' }" />

    <div v-if="modeUbah && jumlahPengajuan > 0" class="app-pesan app-pesan--peringatan" role="note">
        <Ikon nama="info" />
        <p class="app-pesan__isi">
            Paket ini sudah dipakai di {{ jumlahPengajuan }} pengajuan. Harga pada pengajuan lama tidak ikut berubah, tetapi perubahan
            nama dan kecepatan akan terlihat di pengajuan tersebut. Untuk perubahan besar, buat paket baru lalu nonaktifkan paket ini.
        </p>
    </div>

    <form class="app-kartu app-formulir app-formulir--sempit" novalidate @submit.prevent="simpan">
        <RingkasanGalat :galat="form.errors" />

        <Isian id="isian-nama_paket" v-model="form.nama_paket" label="Nama paket" required maxlength="100" :galat="form.errors.nama_paket"
               bantuan="Contoh: Paket 25 Mbps. Nama ditampilkan di website dan riwayat pengajuan." />

        <Isian id="isian-kecepatan_mbps" v-model="form.kecepatan_mbps" label="Kecepatan (Mbps)" type="number" min="1" max="10000" step="1"
               inputmode="numeric" required :galat="form.errors.kecepatan_mbps" />

        <Isian id="isian-harga" v-model="form.harga" label="Harga (Rp)" inputmode="decimal" required :galat="form.errors.harga"
               bantuan="Boleh ditulis 125000 atau 125.000. Periode harga belum dikonfirmasi mitra." />

        <Isian id="isian-biaya_pemasangan" v-model="form.biaya_pemasangan" label="Biaya pemasangan (Rp)" inputmode="decimal" required
               :galat="form.errors.biaya_pemasangan" bantuan="Isi 0 jika gratis atau belum ditentukan." />

        <Isian id="isian-label" v-model="form.label" label="Label kartu" opsional maxlength="30" :galat="form.errors.label"
               bantuan="Tampil sebagai pita di pojok kartu paket, misalnya Terlaris atau Rekomendasi. Kosongkan jika tidak perlu." />

        <IsianTeks id="isian-keunggulan" v-model="form.keunggulan" label="Keunggulan (maksimal 3 baris)" opsional rows="3"
                   :galat="form.errors.keunggulan"
                   bantuan="Satu keunggulan per baris. Pakai tanda | untuk keterangan kecil, contoh: 1–8 perangkat | Perangkat optimal. Isi hanya dengan informasi yang sudah dikonfirmasi mitra. Jika kosong, kartu menampilkan dua poin standar: cek lokasi saat pemasangan dan pantau status." />

        <Centang id="isian-status_aktif" v-model="form.status_aktif" label="Aktif (tampil di website dan dapat dipilih)"
                 :galat="form.errors.status_aktif" />

        <div class="app-formulir__aksi">
            <button type="submit" class="uk-button uk-button-primary" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan…' : (modeUbah ? 'Simpan perubahan' : 'Tambah paket') }}
            </button>
            <Link :href="tautanKembali" class="uk-button uk-button-default">Batal</Link>
        </div>
    </form>
</template>
