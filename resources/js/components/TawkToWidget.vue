<script setup lang="ts">
import { onMounted } from 'vue';
import { loadTawkToWidget } from '@/lib/tawk';

onMounted(() => {
    loadTawkToWidget();
});
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
