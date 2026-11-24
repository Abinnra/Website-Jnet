<script setup>
import { Link } from '@inertiajs/vue3';
import Ikon from '../../Components/Ikon.vue';
import JudulHalaman from '../../Components/JudulHalaman.vue';
import Kosong from '../../Components/Kosong.vue';
import Lencana from '../../Components/Lencana.vue';
import NomorPengajuan from '../../Components/NomorPengajuan.vue';

defineProps({
    tanggal: { type: String, required: true },
    informasiKosong: { type: String, default: null },
    tautanInformasi: { type: String, required: true },
    captchaBelumAktif: { type: Boolean, default: false },
    tindakan: { type: Array, required: true },
    totalPengajuan: { type: Number, required: true },
    ringkasanStatus: { type: Array, required: true },
    pengajuanBaru: { type: Array, required: true },
    tautanPengajuanBaru: { type: String, required: true },
    jadwal: { type: Object, required: true },
});
</script>

<template>
    <JudulHalaman judul="Dashboard">Ringkasan per {{ tanggal }}</JudulHalaman>

    <div v-if="informasiKosong" class="app-pesan app-pesan--peringatan">
        <Ikon nama="peringatan" />
        <div class="app-pesan__isi">
            <p>{{ informasiKosong }}</p>
            <div class="app-pesan__aksi">
                <Link :href="tautanInformasi" class="uk-button uk-button-default uk-button-small">Lengkapi informasi</Link>
            </div>
        </div>
    </div>

    <div v-if="captchaBelumAktif" class="app-pesan app-pesan--peringatan" role="note">
        <Ikon nama="peringatan" />
        <p class="app-pesan__isi">
            Captcha belum aktif: isi TURNSTILE_SITE_KEY dan TURNSTILE_SECRET_KEY di file .env hosting (kunci gratis dari Cloudflare Turnstile).
        </p>
    </div>

    <section aria-labelledby="judul-tindakan">
        <h2 id="judul-tindakan" class="visually-hidden">Perlu tindakan</h2>
        <div class="app-tindakan">
            <Link v-for="kartu in tindakan" :key="kartu.judul" :href="kartu.tautan" class="app-tindakan__item"
                  :class="{ 'app-tindakan__item--peringatan': kartu.peringatan }">
                <span class="app-tindakan__ikon"><Ikon :nama="kartu.ikon" class="app-ikon--besar" /></span>
                <span class="app-tindakan__angka">{{ kartu.angka }}</span>
                <span class="app-tindakan__judul">{{ kartu.judul }}</span>
                <span class="app-tindakan__teks">{{ kartu.teks }}</span>
            </Link>
        </div>
    </section>

    <div class="app-grid app-grid--sisi">
        <div class="app-tumpuk app-tumpuk--besar">
            <section class="app-kartu" aria-labelledby="judul-baru">
                <div class="app-kartu__kepala">
                    <h2 id="judul-baru" class="app-kartu__judul">Pengajuan baru</h2>
                    <Link :href="tautanPengajuanBaru" class="app-kartu__tautan">Semua</Link>
                </div>
                <Kosong v-if="!pengajuanBaru.length" ringkas>Tidak ada pengajuan yang menunggu verifikasi.</Kosong>
                <ul v-else class="app-daftar">
                    <li v-for="item in pengajuanBaru" :key="item.tautan">
                        <Link :href="item.tautan" class="app-daftar__utama"><NomorPengajuan :nomor="item.nomor" /> – {{ item.nama }}</Link>
                        <span class="app-daftar__sekunder">{{ item.keterangan }}</span>
                        <span v-if="item.catatan" class="app-catatan">
                            <span class="app-catatan__label">Ada catatan:</span> {{ item.catatan }}
                        </span>
                    </li>
                </ul>
            </section>

            <section class="app-kartu" aria-labelledby="judul-jadwal">
                <div class="app-kartu__kepala">
                    <h2 id="judul-jadwal" class="app-kartu__judul">{{ jadwal.judul }}</h2>
                    <Link :href="jadwal.tautan" class="app-kartu__tautan">Semua</Link>
                </div>
                <Kosong v-if="!jadwal.daftar.length" ringkas>{{ jadwal.kosong }}</Kosong>
                <ul v-else class="app-daftar">
                    <li v-for="item in jadwal.daftar" :key="item.tautan + item.waktu">
                        <Link :href="item.tautan" class="app-daftar__utama">{{ item.waktu }}</Link>
                        <span class="app-daftar__sekunder"><NomorPengajuan :nomor="item.nomor" /> – {{ item.keterangan }}</span>
                    </li>
                </ul>
            </section>
        </div>

        <aside>
            <section class="app-kartu" aria-labelledby="judul-status">
                <div class="app-kartu__kepala">
                    <h2 id="judul-status" class="app-kartu__judul">Pengajuan per status</h2>
                    <p class="app-total"><strong>{{ totalPengajuan }}</strong> total</p>
                </div>
                <ul class="app-status">
                    <li v-for="baris in ringkasanStatus" :key="baris.tautan">
                        <Link :href="baris.tautan" class="app-status__tautan">
                            <Lencana :status="baris.status" />
                            <span class="app-status__jumlah">{{ baris.jumlah }}</span>
                            <span class="app-status__batang" :class="`app-warna--${baris.status.warna}`" aria-hidden="true">
                                <span class="app-status__isi" :style="{ width: `${baris.persen}%` }"></span>
                            </span>
                        </Link>
                    </li>
                </ul>
            </section>
        </aside>
    </div>
</template>
