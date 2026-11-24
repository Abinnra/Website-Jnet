<script setup>
import { computed } from 'vue';
import Ikon from './Ikon.vue';

/**
 * Area teks (textarea) dengan label, bantuan, dan galat seperti Isian.
 */
defineOptions({ inheritAttrs: false });

const model = defineModel({ type: [String, null], default: '' });

const props = defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    galat: { type: String, default: null },
    bantuan: { type: String, default: null },
    opsional: { type: Boolean, default: false },
    rows: { type: [Number, String], default: 4 },
});

const terkait = computed(() => [props.galat ? `${props.id}-galat` : null, props.bantuan ? `${props.id}-bantuan` : null]
    .filter(Boolean).join(' ') || undefined);
</script>

<template>
    <div class="isian">
        <label class="isian__label" :for="id">
            {{ label }}<span v-if="opsional" class="isian__opsional"> (opsional)</span>
        </label>
        <textarea :id="id" v-model="model" :rows="rows" class="uk-textarea" :class="{ 'uk-form-danger': galat }"
                  :aria-invalid="galat ? 'true' : undefined" :aria-describedby="terkait" v-bind="$attrs" />
        <p v-if="galat" :id="`${id}-galat`" class="isian__galat"><Ikon nama="peringatan" /> {{ galat }}</p>
        <p v-if="bantuan" :id="`${id}-bantuan`" class="isian__bantuan">{{ bantuan }}</p>
        <slot />
    </div>
</template>
