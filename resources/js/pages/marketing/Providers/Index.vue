<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { show } from '@/routes/providers';

withDefaults(
    defineProps<{
        canRegister?: boolean;
        providers?: Array<{
            id: string;
            name: string;
            slug: string;
            description: string | null;
            logo_path: string | null;
            city: string | null;
            province: string | null;
            specialities: Array<{
                id: string;
                name: string;
            }>;
        }>;
    }>(),
    {
        canRegister: true,
        providers: () => [],
    },
);
</script>

<template>
    <Head title="Service Providers" />

    <MarketingLayout :can-register="canRegister">
        <section class="mx-auto min-w-0 max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="space-y-4">
                <p class="text-eyebrow text-gold">Directory</p>
                <h1 class="text-display">Funeral service providers</h1>
                <p class="max-w-2xl text-body text-pretty text-muted-foreground">
                    Find trusted professionals who can help your family plan a meaningful farewell.
                </p>
            </div>

            <div
                v-if="providers.length > 0"
                class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="provider in providers"
                    :key="provider.id"
                    :href="show.url(provider.slug)"
                    class="group"
                >
                    <Card class="h-full transition-shadow group-hover:shadow-warm-lg">
                        <CardHeader class="flex flex-row items-start gap-4">
                            <div
                                v-if="provider.logo_path"
                                class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border bg-background"
                            >
                                <img
                                    :src="provider.logo_path"
                                    :alt="provider.name"
                                    class="max-h-10 max-w-10 object-contain"
                                />
                            </div>
                            <div class="min-w-0">
                                <CardTitle class="text-heading text-lg group-hover:text-brand-strong">
                                    {{ provider.name }}
                                </CardTitle>
                                <CardDescription
                                    v-if="provider.city || provider.province"
                                    class="mt-1 flex items-center gap-1"
                                >
                                    <MapPin class="size-3.5 shrink-0" />
                                    <span>
                                        {{ [provider.city, provider.province].filter(Boolean).join(', ') }}
                                    </span>
                                </CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p
                                v-if="provider.description"
                                class="line-clamp-3 text-muted-foreground text-sm"
                            >
                                {{ provider.description }}
                            </p>
                            <div
                                v-if="provider.specialities.length > 0"
                                class="mt-3 flex flex-wrap gap-1.5"
                            >
                                <span
                                    v-for="speciality in provider.specialities.slice(0, 3)"
                                    :key="speciality.id"
                                    class="rounded-full bg-brand-soft px-2.5 py-0.5 text-brand-strong text-xs"
                                >
                                    {{ speciality.name }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <p v-else class="mt-10 text-muted-foreground">No providers listed yet.</p>
        </section>
    </MarketingLayout>
</template>
