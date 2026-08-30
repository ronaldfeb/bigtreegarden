<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';
import { Button } from '@/components/ui/button';

defineProps<{
    eyebrow: string;
    title: string;
    body: string;
    benefits: ReadonlyArray<{
        icon: Component;
        text: string;
    }>;
    ctaHref: { url: string };
    ctaLabel: string;
    reverse?: boolean;
    muted?: boolean;
}>();
</script>

<template>
    <section :class="muted ? 'gold-rule' : 'gold-rule bg-card'">
        <div class="mx-auto min-w-0 max-w-6xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div class="space-y-8" :class="reverse ? 'lg:order-2' : ''">
                    <div class="space-y-4">
                        <p class="text-eyebrow text-gold">{{ eyebrow }}</p>
                        <h2 class="text-balance text-heading">{{ title }}</h2>
                        <p class="text-body text-pretty text-muted-foreground">{{ body }}</p>
                    </div>

                    <ul class="space-y-4" role="list">
                        <li v-for="benefit in benefits" :key="benefit.text" class="flex gap-3">
                            <span
                                class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-soft text-brand-strong"
                                aria-hidden="true"
                            >
                                <component :is="benefit.icon" class="size-4" />
                            </span>
                            <span class="text-body text-pretty">{{ benefit.text }}</span>
                        </li>
                    </ul>

                    <Button as-child size="lg">
                        <Link :href="ctaHref">{{ ctaLabel }}</Link>
                    </Button>
                </div>

                <div :class="reverse ? 'lg:order-1' : ''">
                    <slot />
                </div>
            </div>
        </div>
    </section>
</template>
