<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Ikon from '../../Components/Ikon.vue';
import Isian from '../../Components/Isian.vue';
import JudulHalaman from '../../Components/JudulHalaman.vue';
import RingkasanGalat from '../../Components/RingkasanGalat.vue';

/**
 * Revisi 5 (UML): Use Case "Mengubah Kata Sandi". Akun yang dibuat lewat Google belum punya
 * kata sandi, jadi halaman ini menjadi "Buat kata sandi" tanpa isian kata sandi lama.
 */
const props = defineProps({
    email: { type: String, required: true },
    punyaSandi: { type: Boolean, required: true },
    wajibGanti: { type: Boolean, required: true },
    terhubungGoogle: { type: Boolean, required: true },
    tautanSimpan: { type: String, required: true },
    tautanKembali: { type: String, required: true },
});

const judul = computed(() => {
    if (props.wajibGanti) {
        return 'Buat kata sandi baru';
    }

    return props.punyaSandi ? 'Ubah kata sandi' : 'Buat kata sandi';
});

const form = useForm({ kata_sandi_lama: '', password: '', password_confirmation: '' });

function simpan() {
    form.transform((data) => (props.punyaSandi ? data : { password: data.password, password_confirmation: data.password_confirmation }))
        .put(props.tautanSimpan, {
            onFinish: () => form.reset('kata_sandi_lama', 'password', 'password_confirmation'),
        });
}
</script>

<template>
    <JudulHalaman :judul="judul" :judul-tab="punyaSandi ? 'Ubah kata sandi' : 'Buat kata sandi'"
                  :kembali="wajibGanti ? null : { href: tautanKembali, label: 'Kembali ke pengajuan saya' }">
        Akun {{ email }}
        <template v-if="terhubungGoogle"><span aria-hidden="true"> · </span>Terhubung dengan Google</template>
    </JudulHalaman>

    <div v-if="wajibGanti" class="app-pesan app-pesan--info" role="status">
        <Ikon nama="info" />
        <p class="app-pesan__isi">Kata sandi akun Anda baru saja diatur ulang oleh admin JNET. Buat kata sandi baru untuk melanjutkan.</p>
    </div>
    <div v-else-if="!punyaSandi" class="app-pesan app-pesan--info" role="status">
        <Ikon nama="info" />
        <p class="app-pesan__isi">
            Akun Anda dibuat dengan Google, jadi belum memiliki kata sandi. Buat kata sandi jika ingin juga dapat masuk dengan email dan
            kata sandi. Masuk dengan Google tetap dapat dipakai.
        </p>
    </div>

    <form class="app-kartu app-formulir app-formulir--sempit" novalidate @submit.prevent="simpan">
        <RingkasanGalat :galat="form.errors" />

        <!-- Nama pengguna tersembunyi membantu pengelola kata sandi browser mengaitkan kata sandi dengan akun ini -->
        <input type="email" name="username" :value="email" autocomplete="username" hidden readonly>

        <Isian v-if="punyaSandi" id="isian-kata_sandi_lama" v-model="form.kata_sandi_lama" type="password" autocomplete="current-password"
               required autofocus :label="wajibGanti ? 'Kata sandi sementara dari admin' : 'Kata sandi saat ini'"
               :galat="form.errors.kata_sandi_lama" />

        <Isian id="isian-password" v-model="form.password" :label="punyaSandi ? 'Kata sandi baru' : 'Kata sandi'" type="password"
               autocomplete="new-password" required :autofocus="!punyaSandi" :galat="form.errors.password"
               :bantuan="punyaSandi ? 'Minimal 8 karakter, berisi huruf dan angka, dan berbeda dari kata sandi saat ini.' : 'Minimal 8 karakter, berisi huruf dan angka.'" />

        <Isian id="isian-password_confirmation" v-model="form.password_confirmation" :label="punyaSandi ? 'Ulangi kata sandi baru' : 'Ulangi kata sandi'"
               type="password" autocomplete="new-password" required :galat="form.errors.password_confirmation" />

        <p v-if="punyaSandi" class="isian__bantuan">Setelah diubah, akun Anda otomatis keluar dari perangkat lain.</p>

        <div class="app-formulir__aksi">
            <button type="submit" class="uk-button uk-button-primary" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan…' : (punyaSandi ? 'Simpan kata sandi baru' : 'Simpan kata sandi') }}
            </button>
        </div>
    </form>
</template>
