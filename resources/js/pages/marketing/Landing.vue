<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    canRegister?: boolean;
}>();

const page = usePage<{ name: string }>();
const appName = computed(() => page.props.name);
</script>

<template>

    <Head :title="appName" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <main class="flex flex-1 flex-col items-center justify-center px-6 py-16">
            <div class="flex w-full max-w-lg flex-col items-center gap-10 text-center">
                <img src="/assets/logo/logo_rect.webp" :alt="appName" class="h-auto w-full max-w-md object-contain"
                    width="480" height="120" />

                <div class="space-y-3">
                    <p class="text-muted-foreground text-pretty text-base leading-relaxed">
                        We are building a platform to help you create eternal memories.
                    </p>
                </div>
            </div>
        </main>

        <footer class="border-border border-t py-6 text-center text-muted-foreground text-sm">
            <p>&copy; {{ new Date().getFullYear() }} {{ appName }}</p>
            <a href="/docs/Malpractice_Sep25_FINAL.pdf" target="_blank">Access PDF</a>
        </footer>
    </div>
</template>
