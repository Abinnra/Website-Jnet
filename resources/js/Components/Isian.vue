<script setup>
import { computed, ref } from 'vue';
import Ikon from './Ikon.vue';

/**
 * Isian teks dengan label di atas, teks bantuan, dan pesan galat yang terhubung lewat aria-describedby.
 * Atribut lain (required, autocomplete, inputmode, maxlength, ...) diteruskan ke <input>.
 * Kolom kata sandi mendapat tombol lihat/sembunyikan (aria-pressed, labelnya tetap).
 */
defineOptions({ inheritAttrs: false });

const model = defineModel({ type: [String, Number, null], default: '' });

const props = defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    type: { type: String, default: 'text' },
    galat: { type: String, default: null },
    bantuan: { type: String, default: null },
    opsional: { type: Boolean, default: false },
});

const tampilSandi = ref(false);
const jenis = computed(() => (props.type === 'password' && tampilSandi.value ? 'text' : props.type));
const terkait = computed(() => [props.galat ? `${props.id}-galat` : null, props.bantuan ? `${props.id}-bantuan` : null]
    .filter(Boolean).join(' ') || undefined);
</script>

<template>
    <div class="isian">
        <label class="isian__label" :for="id">
            {{ label }}<span v-if="opsional" class="isian__opsional"> (opsional)</span>
        </label>
        <div :class="{ isian__sandi: type === 'password' }">
            <input :id="id" v-model="model" :type="jenis" class="uk-input" :class="{ 'uk-form-danger': galat }"
                   :aria-invalid="galat ? 'true' : undefined" :aria-describedby="terkait" v-bind="$attrs">
            <button v-if="type === 'password'" type="button" class="isian__lihat" :aria-controls="id"
                    :aria-pressed="tampilSandi ? 'true' : 'false'" aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi"
                    @click="tampilSandi = !tampilSandi">
                <Ikon :nama="tampilSandi ? 'mata-tutup' : 'mata'" />
            </button>
        </div>
        <p v-if="galat" :id="`${id}-galat`" class="isian__galat"><Ikon nama="peringatan" /> {{ galat }}</p>
        <p v-if="bantuan" :id="`${id}-bantuan`" class="isian__bantuan">{{ bantuan }}</p>
        <slot />
    </div>
</template>
