<script setup>
import { onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Ikon from '../../../Components/Ikon.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import TombolSalin from '../../../Components/TombolSalin.vue';

const props = defineProps({
    email: { type: String, required: true },
    sementara: { type: String, default: null },
    namaPelanggan: { type: String, default: null },
    tautanWa: { type: String, default: null },
    tautanLagi: { type: String, required: true },
});

// Revisi 5: kata sandi sementara hanya tampil di halaman ini. Nilainya disimpan di komponen saja,
// lalu dihapus dari data halaman di riwayat browser (tombol Kembali tidak menampilkannya lagi).
const sandi = ref(props.sementara);
const tautanKirim = ref(props.tautanWa);

onMounted(() => {
    if (props.sementara) {
        router.replace({
            preserveScroll: true,
            preserveState: true,
            props: (sekarang) => ({ ...sekarang, sementara: null, tautanWa: null }),
        });
    }
});
</script>

<template>
    <JudulHalaman judul="Kata sandi sementara dibuat" judul-tab="Kata sandi sementara"
                  :kembali="{ href: tautanLagi, label: 'Atur ulang akun lain' }">
        Akun {{ email }}
    </JudulHalaman>

    <section class="app-kartu app-formulir app-formulir--sempit" aria-labelledby="judul-sandi">
        <h2 id="judul-sandi" class="app-kartu__judul">Kata sandi sementara</h2>

        <template v-if="sandi">
            <div class="app-baris">
                <code class="app-sandi">{{ sandi }}</code>
                <TombolSalin :teks="sandi" label="Salin kata sandi" />
            </div>

            <div class="app-pesan app-pesan--peringatan" role="note">
                <Ikon nama="peringatan" />
                <p class="app-pesan__isi">
                    Catat atau kirim sekarang. Kata sandi ini <strong>tidak dapat ditampilkan lagi</strong> setelah halaman ini ditutup.
                    Jika terlewat, ulangi atur ulang untuk membuat kata sandi sementara yang baru.
                </p>
            </div>

            <div class="app-tumpuk">
                <h3>Kirim ke pelanggan</h3>
                <template v-if="tautanKirim">
                    <p>Pesan WhatsApp sudah berisi kata sandi sementara dan alamat halaman masuk.</p>
                    <div>
                        <a :href="tautanKirim" class="uk-button tombol--wa" target="_blank" rel="noopener noreferrer">
                            <Ikon nama="chat" /> Kirim lewat WhatsApp ke {{ namaPelanggan }}
                        </a>
                    </div>
                </template>
                <p v-else>
                    Akun ini belum memiliki pengajuan, sehingga nomor WhatsApp-nya tidak tersedia. Kirimkan kata sandi sementara lewat
                    kontak yang dipakai pelanggan saat menghubungi JNET.
                </p>
            </div>
        </template>

        <div v-else class="app-pesan app-pesan--info">
            <Ikon nama="info" />
            <div class="app-pesan__isi">
                <p>Kata sandi sementara tidak dapat ditampilkan lagi. Jika belum terkirim ke pelanggan, buat kata sandi sementara yang baru.</p>
                <div class="app-pesan__aksi">
                    <Link :href="tautanLagi" class="uk-button uk-button-default uk-button-small">Atur ulang kata sandi</Link>
                </div>
            </div>
        </div>
    </section>
</template>
