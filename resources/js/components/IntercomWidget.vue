<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, watch } from 'vue';
import { shutdownIntercom, syncIntercom, updateIntercom } from '@/lib/intercom';
import type { Auth } from '@/types/auth';

const page = usePage<{ auth: Auth }>();

let removeNavigateListener: (() => void) | undefined;

onMounted(() => {
    syncIntercom(page.props.auth.user ?? null);

    removeNavigateListener = router.on('navigate', () => {
        updateIntercom();
    });
});

watch(
    () => page.props.auth.user,
    (user) => {
        syncIntercom(user ?? null);
    },
);

onUnmounted(() => {
    removeNavigateListener?.();
    shutdownIntercom();
});
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
