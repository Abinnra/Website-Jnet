<script setup>
import { usePage, usePoll } from '@inertiajs/vue3';
import Ikon from '../../Components/Ikon.vue';
import JudulHalaman from '../../Components/JudulHalaman.vue';
import Kosong from '../../Components/Kosong.vue';
import Lencana from '../../Components/Lencana.vue';

const props = defineProps({
    pengajuan: { type: Object, required: true },
    tautanKembali: { type: String, required: true },
});

const page = usePage();

// Selama pengajuan masih berjalan, status diperbarui otomatis setiap menit tanpa memuat ulang halaman.
// Polling dijeda saat tab tidak terlihat dan tidak dijalankan lagi begitu status final.
const { stop } = usePoll(60000, {
    only: ['pengajuan'],
    onSuccess: () => {
        if (props.pengajuan.final) {
            stop();
        }
    },
}, { background: 'pause', autoStart: !props.pengajuan.final });
</script>

<template>
    <JudulHalaman :judul="pengajuan.nomor" :judul-tab="`Pengajuan ${pengajuan.nomor}`"
                  :kembali="{ href: tautanKembali, label: 'Kembali ke pengajuan saya' }">
        Diajukan {{ pengajuan.diajukan }}
        <template #aksi><Lencana :status="pengajuan.status" /></template>
    </JudulHalaman>

    <div v-if="page.flash?.pengajuan_baru" class="app-konfirmasi" role="status">
        <p class="app-konfirmasi__judul"><Ikon nama="centang" /> Pengajuan berhasil dikirim</p>
        <p class="app-konfirmasi__nomor">{{ pengajuan.nomor }}</p>
        <p>
            Simpan nomor ini untuk menanyakan status pengajuan. Admin JNET akan memeriksa data Anda, dan perkembangan terbaru dapat
            dilihat di halaman ini.
        </p>
        <p v-if="pengajuan.luarWilayah">
            Lokasi Anda berada di luar Kota dan Kabupaten Probolinggo, sehingga jangkauannya dikonfirmasi admin terlebih dahulu.
        </p>
    </div>

    <div class="app-grid app-grid--sisi">
        <div class="app-tumpuk app-tumpuk--besar">
            <!-- Status dan langkah berikutnya -->
            <section class="app-kartu" aria-labelledby="judul-status">
                <h2 id="judul-status" class="app-kartu__judul app-kartu__kepala">Status pengajuan</h2>
                <p class="app-langkah">{{ pengajuan.keterangan }}</p>
                <p v-if="!pengajuan.final" class="app-kecil app-teks-redup" style="margin-top: 8px">Status di halaman ini diperbarui otomatis.</p>

                <div v-if="pengajuan.luarWilayahMenunggu" class="app-pesan app-pesan--info" style="margin: 16px 0 0">
                    <Ikon nama="info" />
                    <p class="app-pesan__isi">
                        Lokasi Anda di luar Kota dan Kabupaten Probolinggo. Admin JNET akan memastikan lokasi ini dapat dijangkau sebelum
                        menjadwalkan pemasangan.
                    </p>
                </div>

                <ol v-if="pengajuan.langkah" class="pelacak" aria-label="Tahap pengajuan">
                    <li v-for="langkah in pengajuan.langkah" :key="langkah.nomor" class="pelacak__langkah"
                        :class="`pelacak__langkah--${langkah.keadaan}`" :aria-current="langkah.sekarang ? 'step' : undefined">
                        <span class="pelacak__titik" aria-hidden="true">
                            <Ikon v-if="langkah.keadaan === 'selesai'" nama="centang-garis" class="app-ikon--kecil" />
                            <template v-else>{{ langkah.nomor }}</template>
                        </span>
                        <span class="pelacak__nama">{{ langkah.nama }}<span class="visually-hidden">: {{ langkah.teks }}</span></span>
                    </li>
                </ol>

                <div v-if="pengajuan.pesan" class="pesan-jnet" role="note" aria-label="Pesan dari JNET">
                    <p class="pesan-jnet__judul"><Ikon nama="chat" class="app-ikon--kecil" /> Pesan dari JNET</p>
                    <p class="pesan-jnet__isi">{{ pengajuan.pesan }}</p>
                </div>

                <a v-if="pengajuan.tautanWa" :href="pengajuan.tautanWa" class="uk-button uk-button-default uk-button-small" style="margin-top: 16px"
                   target="_blank" rel="noopener"><Ikon nama="chat" /> Tanyakan lewat WhatsApp</a>
            </section>

            <!-- Jadwal kunjungan: hanya tanggal, jam, jenis, dan status. Catatan internal admin tidak ditampilkan. -->
            <section class="app-kartu" aria-labelledby="judul-jadwal">
                <h2 id="judul-jadwal" class="app-kartu__judul app-kartu__kepala">Jadwal kunjungan</h2>
                <Kosong v-if="!pengajuan.jadwal.length" ringkas>{{ pengajuan.jadwalKosong }}</Kosong>
                <ul v-else class="app-kunjungan">
                    <li v-for="item in pengajuan.jadwal" :key="item.id">
                        <span class="app-kunjungan__ikon"><Ikon nama="jadwal" /></span>
                        <div>
                            <p class="app-kunjungan__jenis">{{ item.jenis }}</p>
                            <p class="app-kunjungan__waktu">{{ item.waktu }}</p>
                        </div>
                        <Lencana :status="item.status" />
                    </li>
                </ul>
            </section>
        </div>

        <!-- Data yang dikirim -->
        <aside class="app-kartu" aria-labelledby="judul-data">
            <h2 id="judul-data" class="app-kartu__judul app-kartu__kepala">Data pengajuan</h2>
            <dl class="app-data">
                <div><dt>Paket</dt><dd>{{ pengajuan.data.paket }}</dd></div>
                <div><dt>Harga saat pengajuan</dt><dd>{{ pengajuan.data.harga }}</dd></div>
                <div><dt>Nama pemohon</dt><dd>{{ pengajuan.data.nama }}</dd></div>
                <div><dt>Nomor WhatsApp</dt><dd>{{ pengajuan.data.whatsapp }}</dd></div>
                <div><dt>Alamat pemasangan</dt><dd class="app-pre">{{ pengajuan.data.alamat }}</dd></div>
                <div><dt>Kecamatan</dt><dd>{{ pengajuan.data.kecamatan }}</dd></div>
                <div><dt>Kabupaten/kota</dt><dd>{{ pengajuan.data.kota }}</dd></div>
                <div>
                    <dt>Titik lokasi</dt>
                    <dd>
                        <a v-if="pengajuan.data.peta" :href="pengajuan.data.peta" target="_blank" rel="noopener">Lihat di peta</a>
                        <template v-else>Tidak diisi</template>
                    </dd>
                </div>
                <div v-if="pengajuan.data.catatan"><dt>Catatan</dt><dd class="app-pre">{{ pengajuan.data.catatan }}</dd></div>
            </dl>
        </aside>
    </div>
</template>
