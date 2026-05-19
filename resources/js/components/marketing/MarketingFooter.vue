<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<{ name: string }>();
const appName = computed(() => page.props.name);
const year = new Date().getFullYear();
</script>

<template>
    <footer class="gold-rule py-8 text-center">
        <p class="text-meta text-muted-foreground">
            &copy; {{ year }} {{ appName }}
        </p>
    </footer>
</template>
