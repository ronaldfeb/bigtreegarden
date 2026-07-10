<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';

withDefaults(
    defineProps<{
        canRegister?: boolean;
        partners?: Array<{
            id: string;
            name: string;
            logo_path: string;
            website_url: string | null;
        }>;
    }>(),
    {
        canRegister: true,
        partners: () => [],
    },
);
</script>

<template>
    <Head title="Our Partners" />

    <MarketingLayout :can-register="canRegister">
        <section class="mx-auto min-w-0 max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="space-y-4">
                <p class="text-eyebrow text-gold">Trusted by</p>
                <h1 class="text-display">Our partners</h1>
                <p class="max-w-2xl text-body text-pretty text-muted-foreground">
                    We work alongside these organisations to help families honour their loved
                    ones with care and dignity.
                </p>
            </div>

            <div
                v-if="partners.length > 0"
                class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <component
                    :is="partner.website_url ? 'a' : 'div'"
                    v-for="partner in partners"
                    :key="partner.id"
                    :href="partner.website_url ?? undefined"
                    :target="partner.website_url ? '_blank' : undefined"
                    :rel="partner.website_url ? 'noopener noreferrer' : undefined"
                    class="group"
                >
                    <Card class="h-full transition-shadow group-hover:shadow-warm-lg">
                        <CardContent class="flex flex-col items-center gap-4 p-8 text-center">
                            <div
                                class="flex h-24 w-full items-center justify-center rounded-lg bg-background"
                            >
                                <img
                                    :src="partner.logo_path"
                                    :alt="partner.name"
                                    class="max-h-20 max-w-full object-contain"
                                />
                            </div>
                            <p
                                class="flex items-center gap-1.5 text-heading text-lg group-hover:text-brand-strong"
                            >
                                {{ partner.name }}
                                <ExternalLink
                                    v-if="partner.website_url"
                                    class="size-4 text-muted-foreground"
                                />
                            </p>
                        </CardContent>
                    </Card>
                </component>
            </div>

            <p v-else class="mt-10 text-muted-foreground">No partners listed yet.</p>
        </section>
    </MarketingLayout>
</template>
