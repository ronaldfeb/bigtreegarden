<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, watch } from 'vue';
import { shutdownIntercom, syncIntercom, updateIntercom } from '@/lib/intercom';
import type { Auth } from '@/types/auth';

const page = usePage<{ auth: Auth; intercomUserJwt?: string | null }>();

let removeSuccessListener: (() => void) | undefined;

const intercomOptions = () => ({
    user: page.props.auth.user ?? null,
    userJwt: page.props.intercomUserJwt ?? null,
});

onMounted(() => {
    syncIntercom(intercomOptions());

    removeSuccessListener = router.on('success', () => {
        updateIntercom(intercomOptions());
    });
});

watch(
    () => [page.props.auth.user, page.props.intercomUserJwt] as const,
    () => {
        syncIntercom(intercomOptions());
    },
);

onUnmounted(() => {
    removeSuccessListener?.();
    shutdownIntercom();
});
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
