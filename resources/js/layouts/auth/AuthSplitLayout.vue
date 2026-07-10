<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
    panelTitle?: string;
    panelDescription?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <div
            class="relative hidden h-full flex-col justify-between overflow-hidden bg-brand-strong p-10 text-white lg:flex"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(204,162,67,0.25),transparent_55%),radial-gradient(ellipse_at_bottom_right,rgba(116,143,58,0.5),transparent_60%)]"
            />
            <Link
                :href="home()"
                class="relative z-20 flex items-center text-white"
            >
                <AppLogo />
            </Link>

            <div class="relative z-20 space-y-4">
                <slot name="panel">
                    <p class="text-eyebrow text-gold-light">A place to remember</p>
                    <h2 class="text-balance font-display text-3xl leading-snug">
                        {{ panelTitle ?? 'Honour a life. Preserve their memory.' }}
                    </h2>
                    <p class="max-w-md text-pretty text-sm text-white/80">
                        {{
                            panelDescription ??
                            'Create memorial pages, share tributes with family and friends, and keep precious memories safe for generations.'
                        }}
                    </p>
                </slot>
            </div>

            <div class="relative z-20 gold-rule pt-4 text-white/60 text-xs">
                Memorials, tributes, and legacies.
            </div>
        </div>
        <div class="lg:p-8">
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[400px]"
            >
                <Link :href="home()" class="flex items-center justify-center lg:hidden">
                    <AppLogo />
                </Link>
                <div class="flex flex-col space-y-2 text-center">
                    <h1 v-if="title" class="font-medium text-xl tracking-tight">
                        {{ title }}
                    </h1>
                    <p v-if="description" class="text-muted-foreground text-sm">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
