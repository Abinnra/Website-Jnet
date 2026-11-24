<script setup>
import Ikon from './Ikon.vue';

/**
 * Kotak centang dengan label di sebelah kanan.
 */
const model = defineModel({ type: Boolean, default: false });

defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    galat: { type: String, default: null },
    bantuan: { type: String, default: null },
});
</script>

<template>
    <div class="isian isian--centang">
        <label class="centang" :for="id">
            <input :id="id" v-model="model" type="checkbox" class="uk-checkbox"
                   :aria-describedby="bantuan ? `${id}-bantuan` : undefined">
            <span>{{ label }}</span>
        </label>
        <p v-if="bantuan" :id="`${id}-bantuan`" class="isian__bantuan">{{ bantuan }}</p>
        <p v-if="galat" class="isian__galat"><Ikon nama="peringatan" /> {{ galat }}</p>
    </div>
</template>
