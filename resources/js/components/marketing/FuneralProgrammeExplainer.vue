<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Check, Globe, Printer, QrCode, Share2 } from 'lucide-vue-next';
import PamphletSamplePreview from '@/components/marketing/PamphletSamplePreview.vue';
import { Button } from '@/components/ui/button';
import { create } from '@/routes/pamphlets';

const benefits = [
    {
        icon: Printer,
        text: 'Print-ready pamphlet with a portrait background and memorial photo.',
    },
    {
        icon: QrCode,
        text: 'QR on the pamphlet links guests to a living memorial page.',
    },
    {
        icon: Share2,
        text: 'Share the programme, obituary, and hymns online with family and friends.',
    },
    {
        icon: Globe,
        text: 'Easy for family to create without technical setup.',
    },
    {
        icon: Check,
        text: 'Keeps names, dates, and tribute text beautifully presented.',
    },
] as const;
</script>

<template>
    <section class="gold-rule bg-card">
        <div class="mx-auto min-w-0 max-w-6xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div class="space-y-8">
                    <div class="space-y-4">
                        <p class="text-eyebrow text-gold">Your funeral programme</p>
                        <h2 class="text-balance text-heading">
                            An online programme guests can hold and revisit
                        </h2>
                        <p class="text-body text-pretty text-muted-foreground">
                            Give mourners a printed pamphlet they can keep, with a digital memorial
                            page they can return to long after the service.
                        </p>
                    </div>

                    <ul class="space-y-4" role="list">
                        <li
                            v-for="benefit in benefits"
                            :key="benefit.text"
                            class="flex gap-3"
                        >
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
                        <Link :href="create()">Create memorial</Link>
                    </Button>
                </div>

                <div class="lg:order-none">
                    <PamphletSamplePreview />
                </div>
            </div>
        </div>
    </section>
</template>
