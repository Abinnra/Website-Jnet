<script setup>
/**
 * Tombol CS melayang di pojok kanan bawah halaman akun: membuka chat WhatsApp admin JNET.
 * Ikon headset biru JNET di atas lingkaran putih, sama dengan components/tombol-cs.blade.php.
 */
defineProps({
    cs: { type: Object, required: true }, // { tautan }
});
</script>

<template>
    <a class="uk-icon-button tombol-cs" :href="cs.tautan" target="_blank" rel="noopener" aria-label="Chat CS JNET lewat WhatsApp">
        <svg class="tombol-cs__headset" viewBox="0 0 48 48" width="34" height="34" aria-hidden="true" focusable="false">
            <path d="M10 28v-5a14 14 0 0 1 28 0v5" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" />
            <rect x="5" y="21.5" width="9.5" height="14.5" rx="4.75" fill="currentColor" />
            <rect x="33.5" y="21.5" width="9.5" height="14.5" rx="4.75" fill="currentColor" />
            <path d="M38.25 35.5v.75c0 3.6-2.9 5.75-6.5 5.75H28" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" />
            <rect class="tombol-cs__mik" x="19.5" y="38.75" width="10" height="6.5" rx="3.25" />
        </svg>
    </a>
</template>
