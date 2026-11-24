<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import Isian from '../../../Components/Isian.vue';
import IsianTeks from '../../../Components/IsianTeks.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import RingkasanGalat from '../../../Components/RingkasanGalat.vue';

const props = defineProps({
    modeUbah: { type: Boolean, required: true },
    judul: { type: String, required: true },
    jenis: { type: Object, required: true },
    pengajuan: { type: Object, required: true },
    jadwal: { type: Object, required: true },
    tanggalMinimal: { type: String, required: true },
    bantuanJam: { type: String, required: true },
    tautanSimpan: { type: String, required: true },
});

const page = usePage();
const galatJadwal = computed(() => page.props.errors?.jadwal);

const form = useForm({
    jenis_jadwal: props.jenis.nilai,
    tanggal: props.jadwal.tanggal ?? '',
    waktu_mulai: props.jadwal.waktu_mulai ?? '',
    catatan: props.jadwal.catatan ?? '',
});

function simpan() {
    const opsi = { preserveScroll: true };

    if (props.modeUbah) {
        form.transform(({ jenis_jadwal, ...data }) => data).put(props.tautanSimpan, opsi);
    } else {
        form.post(props.tautanSimpan, opsi);
    }
}
</script>

<template>
    <JudulHalaman :judul="judul" :kembali="{ href: pengajuan.tautan, label: `Kembali ke ${pengajuan.nomor}` }">
        {{ pengajuan.ringkas }}
    </JudulHalaman>

    <div v-if="galatJadwal" class="app-pesan app-pesan--gagal" role="alert">
        <Ikon nama="peringatan" />
        <p class="app-pesan__isi">{{ galatJadwal }}</p>
    </div>

    <div class="app-grid app-grid--sisi">
        <form class="app-kartu app-formulir" novalidate @submit.prevent="simpan">
            <RingkasanGalat :galat="form.errors" />

            <div class="isian">
                <p class="isian__label">Jenis jadwal</p>
                <p><strong>{{ jenis.label }}</strong></p>
                <p v-if="jenis.keterangan" class="isian__bantuan">{{ jenis.keterangan }}</p>
            </div>

            <Isian id="isian-tanggal" v-model="form.tanggal" label="Tanggal" type="date" required :min="tanggalMinimal"
                   :galat="form.errors.tanggal" bantuan="Tidak boleh sebelum hari ini." />

            <Isian id="isian-waktu_mulai" v-model="form.waktu_mulai" label="Jam mulai (WIB)" type="time" required step="300"
                   :galat="form.errors.waktu_mulai" :bantuan="bantuanJam" />

            <IsianTeks id="isian-catatan" v-model="form.catatan" label="Catatan internal" opsional rows="3" maxlength="1000"
                       :galat="form.errors.catatan" bantuan="Hanya terlihat oleh admin, tidak ditampilkan kepada pelanggan." />

            <div class="app-formulir__aksi">
                <button type="submit" class="uk-button uk-button-primary" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan…' : (modeUbah ? 'Simpan perubahan' : 'Simpan jadwal') }}
                </button>
                <Link :href="pengajuan.tautan" class="uk-button uk-button-default">Batal</Link>
            </div>
        </form>

        <aside class="app-kartu">
            <h2 class="app-kartu__judul app-kartu__kepala">Lokasi kunjungan</h2>
            <dl class="app-data">
                <div><dt>Alamat</dt><dd class="app-pre">{{ pengajuan.alamat }}</dd></div>
                <div><dt>Lokasi</dt><dd>{{ pengajuan.lokasi }}</dd></div>
                <div><dt>WhatsApp</dt><dd>{{ pengajuan.whatsapp }}</dd></div>
            </dl>
            <p v-if="jenis.statusSetelahDisimpan" class="isian__bantuan" style="margin-top: 16px">
                Setelah disimpan, status pengajuan berubah menjadi "{{ jenis.statusSetelahDisimpan }}" dan pelanggan dapat melihat tanggal serta jam kunjungan.
            </p>
        </aside>
    </div>
</template>
