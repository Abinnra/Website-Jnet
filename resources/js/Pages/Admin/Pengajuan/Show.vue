<script setup>
import { computed, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import DialogKonfirmasi from '../../../Components/DialogKonfirmasi.vue';
import Ikon from '../../../Components/Ikon.vue';
import IsianTeks from '../../../Components/IsianTeks.vue';
import JudulHalaman from '../../../Components/JudulHalaman.vue';
import Kosong from '../../../Components/Kosong.vue';
import Lencana from '../../../Components/Lencana.vue';
import NomorPengajuan from '../../../Components/NomorPengajuan.vue';

const props = defineProps({
    pengajuan: { type: Object, required: true },
    jadwal: { type: Array, required: true },
    tindakan: { type: Object, required: true },
    tautan: { type: Object, required: true },
});

const page = usePage();
const galatStatus = computed(() => page.props.errors?.status);

// Perubahan status manual (verifikasi, tolak, batalkan).
const formStatus = useForm({ status: '', pesan_untuk_pelanggan: '' });
const dialog = ref(null);
const aksiDipilih = ref(null);

function ubahStatus(aksi) {
    formStatus.status = aksi.status;
    formStatus.pesan_untuk_pelanggan = '';
    formStatus.patch(props.tautan.status, { preserveScroll: true });
}

function bukaKonfirmasi(aksi) {
    aksiDipilih.value = aksi;
    formStatus.clearErrors();
    formStatus.pesan_untuk_pelanggan = '';
    dialog.value.buka();
}

function kirimKonfirmasi() {
    formStatus.status = aksiDipilih.value.status;
    formStatus.patch(props.tautan.status, {
        preserveScroll: true,
        onSuccess: () => dialog.value.tutup(),
        onError: (galat) => {
            if (galat.status) {
                dialog.value.tutup();
            }
        },
    });
}

// Revisi 3: satu pesan terakhir yang TAMPIL di halaman pelanggan.
const formPesan = useForm({ pesan_untuk_pelanggan: props.pengajuan.pesanUntukPelanggan ?? '' });

function simpanPesan() {
    formPesan.put(props.tautan.pesan, { preserveScroll: true });
}
</script>

<template>
    <JudulHalaman :judul="pengajuan.nomor" :judul-tab="`Pengajuan ${pengajuan.nomor}`"
                  :kembali="{ href: tautan.daftar, label: 'Kembali ke daftar pengajuan' }">
        Diajukan {{ pengajuan.diajukan }}
        <template #judul><NomorPengajuan :nomor="pengajuan.nomor" /></template>
        <template #aksi><Lencana :status="pengajuan.status" /></template>
    </JudulHalaman>

    <div v-if="galatStatus" class="app-pesan app-pesan--gagal" role="alert">
        <Ikon nama="peringatan" />
        <p class="app-pesan__isi">{{ galatStatus }}</p>
    </div>

    <div class="app-grid app-grid--sisi">
        <div class="app-tumpuk app-tumpuk--besar">
            <section class="app-kartu" aria-labelledby="judul-pelanggan">
                <h2 id="judul-pelanggan" class="app-kartu__judul app-kartu__kepala">Data calon pelanggan</h2>
                <dl class="app-data">
                    <div><dt>Nama pemohon</dt><dd>{{ pengajuan.nama }}</dd></div>
                    <div>
                        <dt>Nomor WhatsApp</dt>
                        <dd>
                            {{ pengajuan.whatsapp }}<br>
                            <a v-if="pengajuan.tautanWhatsapp" :href="pengajuan.tautanWhatsapp" class="app-data__tautan" target="_blank" rel="noopener">
                                <Ikon nama="chat" class="app-ikon--kecil" /> Chat pelanggan lewat WhatsApp
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt>Email akun</dt>
                        <dd>
                            {{ pengajuan.email }}<br>
                            <Link :href="pengajuan.tautanLupaSandi" class="app-data__tautan">
                                <Ikon nama="kunci" class="app-ikon--kecil" /> Pelanggan lupa kata sandi?
                            </Link>
                        </dd>
                    </div>
                    <div><dt>Alamat pemasangan</dt><dd class="app-pre">{{ pengajuan.alamat }}</dd></div>
                    <div><dt>Kecamatan</dt><dd>{{ pengajuan.kecamatan }}</dd></div>
                    <div>
                        <dt>Kabupaten/kota</dt>
                        <dd>
                            {{ pengajuan.kota }}
                            <span v-if="pengajuan.luarWilayah" class="lencana lencana--luar">Luar wilayah</span>
                        </dd>
                    </div>
                    <div>
                        <dt>Titik lokasi</dt>
                        <dd v-if="pengajuan.titik">
                            {{ pengajuan.titik.koordinat }}<br>
                            <a :href="pengajuan.titik.osm" class="app-data__tautan" target="_blank" rel="noopener">
                                <Ikon nama="peta" class="app-ikon--kecil" /> Buka di OpenStreetMap
                            </a>
                            <a :href="pengajuan.titik.gmaps" class="app-data__tautan" target="_blank" rel="noopener">
                                <Ikon nama="wilayah" class="app-ikon--kecil" /> Buka di Google Maps
                            </a>
                            <template v-if="pengajuan.titik.jarak"><br>{{ pengajuan.titik.jarak }}</template>
                        </dd>
                        <dd v-else>Tidak diisi pelanggan</dd>
                    </div>
                    <div><dt>Catatan pelanggan</dt><dd class="app-pre">{{ pengajuan.catatan || 'Tidak ada' }}</dd></div>
                </dl>
            </section>

            <section class="app-kartu" aria-labelledby="judul-paket">
                <h2 id="judul-paket" class="app-kartu__judul app-kartu__kepala">Paket</h2>
                <dl class="app-data">
                    <div>
                        <dt>Paket dipilih</dt>
                        <dd>
                            {{ pengajuan.paket }}
                            <span v-if="pengajuan.paketNonaktif" class="app-tabel__sekunder">Paket ini sekarang nonaktif.</span>
                        </dd>
                    </div>
                    <div>
                        <dt>Harga saat pengajuan</dt>
                        <dd>
                            {{ pengajuan.harga }}
                            <span v-if="pengajuan.hargaSekarang" class="app-tabel__sekunder">{{ pengajuan.hargaSekarang }}</span>
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="app-kartu" aria-labelledby="judul-jadwal">
                <h2 id="judul-jadwal" class="app-kartu__judul app-kartu__kepala">Jadwal dan hasil layanan</h2>
                <Kosong v-if="!jadwal.length" ringkas>Belum ada jadwal untuk pengajuan ini.</Kosong>
                <ul v-else class="app-kunjungan">
                    <li v-for="item in jadwal" :key="item.id">
                        <span class="app-kunjungan__ikon"><Ikon nama="jadwal" /></span>
                        <div>
                            <p class="app-kunjungan__jenis">{{ item.jenis }}</p>
                            <p class="app-kunjungan__waktu">{{ item.waktu }}</p>
                            <p v-if="item.catatan" class="app-kunjungan__catatan">Catatan internal: {{ item.catatan }}</p>
                        </div>
                        <Lencana :status="item.status" />

                        <div v-if="item.hasil" class="app-hasil">
                            <p><strong>Hasil: {{ item.hasil.label }}</strong></p>
                            <p v-if="item.hasil.catatan" class="app-pre">{{ item.hasil.catatan }}</p>
                            <p class="app-kecil app-teks-redup">{{ item.hasil.dicatat }}</p>
                            <p v-if="item.hasil.tautanDokumentasi">
                                <a :href="item.hasil.tautanDokumentasi" class="app-data__tautan" target="_blank" rel="noopener">
                                    <Ikon nama="dokumen" class="app-ikon--kecil" /> Lihat dokumentasi
                                </a>
                            </p>
                        </div>

                        <!-- Catat hasil ada di kartu Tindakan (kanan), di bawah tombol kabar WhatsApp. -->
                        <div v-if="item.tautanUbah" class="app-kunjungan__aksi">
                            <Link :href="item.tautanUbah" class="uk-button uk-button-default uk-button-small">
                                <Ikon nama="ubah" /> Ubah jadwal
                            </Link>
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        <aside class="app-tumpuk app-tumpuk--besar">
            <section class="app-kartu" aria-labelledby="judul-tindakan">
                <h2 id="judul-tindakan" class="app-kartu__judul app-kartu__kepala">Tindakan</h2>
                <div class="app-tumpuk">
                    <p class="app-langkah">{{ tindakan.langkah }}</p>

                    <div v-if="tindakan.tautanKabar" class="app-kabar">
                        <a :href="tindakan.tautanKabar" class="uk-button tombol--wa" target="_blank" rel="noopener">
                            <Ikon nama="chat" /> Kabari pelanggan lewat WhatsApp
                        </a>
                        <p class="isian__bantuan">
                            Pesan otomatis berisi status terbaru, jadwal kunjungan yang aktif, dan pesan untuk pelanggan.
                            Isinya masih bisa diubah di WhatsApp sebelum dikirim.
                        </p>
                    </div>

                    <div v-if="tindakan.konfirmasiJangkauan" class="app-pesan app-pesan--peringatan">
                        <Ikon nama="peringatan" />
                        <div class="app-pesan__isi">
                            <p><strong>Perlu konfirmasi jangkauan.</strong> {{ tindakan.konfirmasiJangkauan }}</p>
                            <p>Verifikasi jika lokasi dapat dijangkau tim JNET; tolak jika tidak.</p>
                        </div>
                    </div>

                    <div class="app-tindakan-pengajuan">
                        <Link v-if="tindakan.catatHasil" :href="tindakan.catatHasil.tautan" class="uk-button uk-button-primary">
                            <Ikon nama="catat" /> {{ tindakan.catatHasil.label }}
                        </Link>
                        <p v-if="tindakan.catatHasilMulai" class="isian__bantuan">{{ tindakan.catatHasilMulai }}</p>
                        <Link v-if="tindakan.jadwalkan" :href="tindakan.jadwalkan.tautan" class="uk-button uk-button-primary">
                            <Ikon nama="jadwal-tambah" /> {{ tindakan.jadwalkan.label }}
                        </Link>
                        <template v-for="aksi in tindakan.transisi" :key="aksi.status">
                            <button v-if="!aksi.konfirmasi" type="button" class="uk-button uk-button-primary" :disabled="formStatus.processing"
                                    @click="ubahStatus(aksi)">
                                <Ikon nama="centang" /> {{ formStatus.processing && formStatus.status === aksi.status ? 'Menyimpan…' : aksi.label }}
                            </button>
                            <button v-else type="button" class="uk-button tombol--bahaya-garis" @click="bukaKonfirmasi(aksi)">
                                {{ aksi.label }}
                            </button>
                        </template>
                    </div>
                </div>
            </section>

            <section class="app-kartu" aria-labelledby="judul-pesan">
                <h2 id="judul-pesan" class="app-kartu__judul app-kartu__kepala">Pesan untuk pelanggan</h2>
                <form class="app-formulir" novalidate @submit.prevent="simpanPesan">
                    <IsianTeks id="isian-pesan_untuk_pelanggan" v-model="formPesan.pesan_untuk_pelanggan" label="Isi pesan" rows="4" maxlength="1000"
                               :galat="formPesan.errors.pesan_untuk_pelanggan"
                               bantuan="Tampil di halaman pengajuan pelanggan, misalnya alasan penolakan atau informasi jangkauan. Kosongkan lalu simpan untuk menghapus. Catatan jadwal dan uraian hasil tetap hanya untuk admin." />
                    <div>
                        <button type="submit" class="uk-button uk-button-default" :disabled="formPesan.processing">
                            {{ formPesan.processing ? 'Menyimpan…' : 'Simpan pesan' }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="app-kartu" aria-labelledby="judul-riwayat">
                <h2 id="judul-riwayat" class="app-kartu__judul app-kartu__kepala">Riwayat data</h2>
                <dl class="app-data">
                    <div><dt>Diajukan</dt><dd>{{ pengajuan.diajukan }}</dd></div>
                    <div><dt>Terakhir diperbarui</dt><dd>{{ pengajuan.diperbarui }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>

    <DialogKonfirmasi ref="dialog" :judul="aksiDipilih ? `${aksiDipilih.label}?` : 'Konfirmasi'"
                      :label-konfirmasi="aksiDipilih ? `Ya, ${aksiDipilih.label.toLowerCase()}` : 'Ya'" bahaya
                      :memproses="formStatus.processing" @konfirmasi="kirimKonfirmasi">
        <p v-if="aksiDipilih?.penjelasan">{{ aksiDipilih.penjelasan }}</p>
        <IsianTeks id="isian-alasan" v-model="formStatus.pesan_untuk_pelanggan" label="Alasan untuk pelanggan" opsional rows="3"
                   maxlength="1000" bantuan="Tampil di halaman pengajuan pelanggan." :galat="formStatus.errors.pesan_untuk_pelanggan" />
    </DialogKonfirmasi>
</template>
