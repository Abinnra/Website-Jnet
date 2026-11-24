<script setup>
import { computed, nextTick, ref, useId } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Ikon from './Ikon.vue';
import Isian from './Isian.vue';
import Kosong from './Kosong.vue';

/**
 * Satu kota/kabupaten di halaman Wilayah layanan: daftar kecamatan, tambah kecamatan, dan dialog
 * untuk mengubah atau menghapus kecamatan maupun kota/kabupaten. Setiap formulir punya pesan galatnya sendiri.
 */
const props = defineProps({
    kota: { type: Object, required: true },
});

const page = usePage();
const idDialog = useId();
const pesan = computed(() => {
    const flash = page.flash?.pesanWilayah;

    return flash && [`kota-${props.kota.id}`, `tambah-kecamatan-${props.kota.id}`].includes(flash.tempat) ? flash : null;
});

// Tambah kecamatan. Setelah berhasil, kolom dikosongkan dan tetap difokus untuk kecamatan berikutnya.
const formTambah = useForm({ nama_kecamatan: '' });
const kolomTambah = ref(null);

function tambahKecamatan() {
    formTambah.post(props.kota.tautanTambahKecamatan, {
        preserveScroll: true,
        onSuccess: async () => {
            formTambah.reset();
            await nextTick();
            document.getElementById(`kecamatan-baru-${props.kota.id}`)?.focus();
        },
    });
}

// Ubah atau hapus kecamatan (dialog).
const dialogKecamatan = ref(null);
const kecamatanDipilih = ref(null);
const formKecamatan = useForm({ nama_kecamatan: '' });

async function bukaKecamatan(kecamatan) {
    kecamatanDipilih.value = kecamatan;
    formKecamatan.clearErrors();
    formKecamatan.nama_kecamatan = kecamatan.nama;
    // Tunggu isi dialog dirender agar fokus awal (autofocus) jatuh ke kolom nama.
    await nextTick();
    dialogKecamatan.value.showModal();
}

function simpanKecamatan() {
    formKecamatan.put(kecamatanDipilih.value.tautanUbah, {
        preserveScroll: true,
        onSuccess: () => dialogKecamatan.value.close(),
    });
}

function hapusKecamatan() {
    router.delete(kecamatanDipilih.value.tautanHapus, {
        preserveScroll: true,
        onSuccess: () => dialogKecamatan.value.close(),
    });
}

// Ubah nama atau hapus kota/kabupaten (dialog, penghapusan perlu konfirmasi kedua).
const dialogKota = ref(null);
const formKota = useForm({ nama_kota: '' });
const yakinHapus = ref(false);

function bukaKota() {
    formKota.clearErrors();
    formKota.nama_kota = props.kota.nama;
    yakinHapus.value = false;
    dialogKota.value.showModal();
}

function simpanKota() {
    formKota.put(props.kota.tautanUbah, {
        preserveScroll: true,
        onSuccess: () => dialogKota.value.close(),
    });
}

function hapusKota() {
    if (!yakinHapus.value) {
        yakinHapus.value = true;

        return;
    }

    router.delete(props.kota.tautanHapus, {
        preserveScroll: true,
        onSuccess: () => dialogKota.value?.close(),
    });
}
</script>

<template>
    <section :id="`kota-${kota.id}`" class="app-kartu" :aria-labelledby="`judul-kota-${kota.id}`">
        <div class="app-wilayah__kepala">
            <h2 :id="`judul-kota-${kota.id}`" class="app-wilayah__judul">{{ kota.nama }}</h2>
            <p class="app-baris">
                <span class="lencana lencana--info">{{ kota.kecamatan.length }} kecamatan</span>
                <span class="app-kecil app-teks-redup">{{ kota.jumlahPengajuan }} pengajuan tercatat</span>
            </p>
        </div>

        <div v-if="pesan" class="app-pesan" :class="pesan.jenis === 'sukses' ? 'app-pesan--sukses' : 'app-pesan--gagal'"
             :role="pesan.jenis === 'sukses' ? 'status' : 'alert'">
            <Ikon :nama="pesan.jenis === 'sukses' ? 'centang' : 'peringatan'" />
            <p class="app-pesan__isi">{{ pesan.teks }}</p>
        </div>

        <Kosong v-if="!kota.kecamatan.length" ringkas>
            Belum ada kecamatan. Pelanggan belum dapat memilih {{ kota.nama }} sebelum kecamatannya ditambahkan.
        </Kosong>
        <ul v-else class="app-kecamatan" :aria-label="`Kecamatan di ${kota.nama}`">
            <li v-for="kecamatan in kota.kecamatan" :key="kecamatan.id">
                <button type="button" class="app-kecamatan__tombol" @click="bukaKecamatan(kecamatan)">
                    {{ kecamatan.nama }} <Ikon nama="ubah" /><span class="visually-hidden">: ubah atau hapus</span>
                </button>
            </li>
        </ul>

        <div class="app-wilayah__bawah">
            <form novalidate @submit.prevent="tambahKecamatan">
                <div class="isian">
                    <label class="isian__label" :for="`kecamatan-baru-${kota.id}`">Tambah kecamatan di {{ kota.nama }}</label>
                    <div class="app-form-baris">
                        <input :id="`kecamatan-baru-${kota.id}`" ref="kolomTambah" v-model="formTambah.nama_kecamatan" type="text" class="uk-input"
                               maxlength="100" required placeholder="Nama kecamatan" :class="{ 'uk-form-danger': formTambah.errors.nama_kecamatan }"
                               :aria-invalid="formTambah.errors.nama_kecamatan ? 'true' : undefined"
                               :aria-describedby="formTambah.errors.nama_kecamatan ? `kecamatan-baru-${kota.id}-galat` : undefined">
                        <button type="submit" class="uk-button uk-button-primary" :disabled="formTambah.processing">
                            <Ikon nama="tambah" /> {{ formTambah.processing ? 'Menambahkan…' : 'Tambah kecamatan' }}
                        </button>
                    </div>
                    <p v-if="formTambah.errors.nama_kecamatan" :id="`kecamatan-baru-${kota.id}-galat`" class="isian__galat">
                        <Ikon nama="peringatan" /> {{ formTambah.errors.nama_kecamatan }}
                    </p>
                </div>
            </form>
            <div>
                <button type="button" class="uk-button uk-button-default" @click="bukaKota">
                    <Ikon nama="ubah" /> Ubah nama atau hapus {{ kota.nama }}
                </button>
            </div>
        </div>

        <!-- Dialog ubah/hapus kecamatan -->
        <dialog ref="dialogKecamatan" class="app-dialog" :aria-labelledby="`${idDialog}-kecamatan`">
            <form v-if="kecamatanDipilih" class="app-dialog__isi" novalidate @submit.prevent="simpanKecamatan">
                <h2 :id="`${idDialog}-kecamatan`" class="app-dialog__judul">Ubah kecamatan {{ kecamatanDipilih.nama }}</h2>
                <Isian :id="`kecamatan-${kecamatanDipilih.id}-nama`" v-model="formKecamatan.nama_kecamatan" label="Nama kecamatan" required
                       maxlength="100" autofocus :galat="formKecamatan.errors.nama_kecamatan"
                       bantuan="Pengajuan lama yang memakai kecamatan ini ikut memakai nama baru." />
                <div class="app-dialog__aksi">
                    <button type="button" class="uk-button tombol--bahaya-garis" :disabled="formKecamatan.processing" @click="hapusKecamatan">
                        <Ikon nama="hapus" /> Hapus
                    </button>
                    <button type="button" class="uk-button uk-button-default" @click="dialogKecamatan.close()">Batal</button>
                    <button type="submit" class="uk-button uk-button-primary" :disabled="formKecamatan.processing">
                        {{ formKecamatan.processing ? 'Menyimpan…' : 'Simpan' }}
                    </button>
                </div>
                <p class="app-kecil app-teks-redup">Kecamatan yang dihapus tidak dapat dipilih lagi pada pengajuan baru; pengajuan lama tetap menyimpan nama kecamatannya.</p>
            </form>
        </dialog>

        <!-- Dialog ubah nama/hapus kota/kabupaten -->
        <dialog ref="dialogKota" class="app-dialog" :aria-labelledby="`${idDialog}-kota`">
            <form class="app-dialog__isi" novalidate @submit.prevent="simpanKota">
                <h2 :id="`${idDialog}-kota`" class="app-dialog__judul">Ubah {{ kota.nama }}</h2>
                <Isian :id="`kota-${kota.id}-nama`" v-model="formKota.nama_kota" label="Nama kota/kabupaten" required maxlength="100" autofocus
                       :galat="formKota.errors.nama_kota" :bantuan="`Pengajuan lama di ${kota.nama} ikut memakai nama baru.`" />
                <div class="app-dialog__aksi">
                    <button type="button" class="uk-button uk-button-default" @click="dialogKota.close()">Batal</button>
                    <button type="submit" class="uk-button uk-button-primary" :disabled="formKota.processing">
                        {{ formKota.processing ? 'Menyimpan…' : 'Simpan nama' }}
                    </button>
                </div>
                <div class="app-wilayah__bawah">
                    <p v-if="!kota.tautanHapus" class="app-kecil app-teks-redup">
                        {{ kota.nama }} tidak dapat dihapus karena sudah dipakai {{ kota.jumlahPengajuan }} pengajuan.
                    </p>
                    <template v-else>
                        <p v-if="yakinHapus" class="app-pesan app-pesan--gagal" role="alert">
                            <Ikon nama="peringatan" />
                            <span class="app-pesan__isi">{{ kota.nama }} dan semua kecamatannya akan dihapus dari wilayah layanan. Tekan sekali lagi untuk menghapus.</span>
                        </p>
                        <div>
                            <button type="button" class="uk-button" :class="yakinHapus ? 'uk-button-danger' : 'tombol--bahaya-garis'" @click="hapusKota">
                                <Ikon nama="hapus" /> {{ yakinHapus ? `Ya, ${kota.labelHapus.toLowerCase()}` : kota.labelHapus }}
                            </button>
                        </div>
                    </template>
                </div>
            </form>
        </dialog>
    </section>
</template>
