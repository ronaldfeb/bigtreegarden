<script setup lang="ts">
import { onMounted } from 'vue';

const TAWK_SRC = 'https://embed.tawk.to/6a60e5a8aa83a11d48ca6784/1ju58273b';

onMounted(() => {
    if (document.querySelector(`script[src="${TAWK_SRC}"]`)) {
        return;
    }

    window.Tawk_API = window.Tawk_API || {};
    window.Tawk_LoadStart = new Date();

    const script = document.createElement('script');
    script.async = true;
    script.src = TAWK_SRC;
    script.charset = 'UTF-8';
    script.setAttribute('crossorigin', '*');

    const firstScript = document.getElementsByTagName('script')[0];
    firstScript.parentNode?.insertBefore(script, firstScript);
});
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
