<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Globe, Mail, MapPin, Phone } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { index } from '@/routes/providers';

const props = defineProps<{
    canRegister?: boolean;
    provider: {
        id: string;
        name: string;
        slug: string;
        registration_number: string | null;
        description: string | null;
        logo_path: string | null;
        cover_image_path: string | null;
        email: string;
        phone: string | null;
        website_url: string | null;
        physical_address: string | null;
        city: string | null;
        province: string | null;
        services: Array<{
            id: string;
            name: string;
            description: string | null;
            price_from_cents: number | null;
        }>;
        specialities: Array<{
            id: string;
            name: string;
        }>;
        social_media: Array<{
            id: string;
            platform: string;
            url: string;
        }>;
        images: Array<{
            id: string;
            image_path: string;
            caption: string | null;
        }>;
    };
}>();

const location = computed(() =>
    [props.provider.city, props.provider.province].filter(Boolean).join(', '),
);

function formatPrice(cents: number): string {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: 'ZAR',
        minimumFractionDigits: 0,
    }).format(cents / 100);
}
</script>

<template>
    <Head :title="provider.name" />

    <MarketingLayout :can-register="canRegister">
        <article class="mx-auto min-w-0 max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <Link
                :href="index()"
                class="text-muted-foreground text-sm hover:text-foreground"
            >
                ← All providers
            </Link>

            <div
                v-if="provider.cover_image_path"
                class="mt-6 overflow-hidden rounded-xl"
            >
                <img
                    :src="provider.cover_image_path"
                    :alt="provider.name"
                    class="aspect-[21/9] w-full object-cover"
                />
            </div>

            <header class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-start">
                <div
                    v-if="provider.logo_path"
                    class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border bg-card"
                >
                    <img
                        :src="provider.logo_path"
                        :alt="provider.name"
                        class="max-h-14 max-w-14 object-contain"
                    />
                </div>
                <div class="min-w-0 flex-1 space-y-2">
                    <h1 class="text-display">{{ provider.name }}</h1>
                    <p v-if="location" class="flex items-center gap-1.5 text-muted-foreground">
                        <MapPin class="size-4 shrink-0" />
                        {{ location }}
                    </p>
                    <p
                        v-if="provider.registration_number"
                        class="text-muted-foreground text-sm"
                    >
                        Reg. {{ provider.registration_number }}
                    </p>
                </div>
            </header>

            <p
                v-if="provider.description"
                class="mt-6 text-body text-pretty text-muted-foreground whitespace-pre-line"
            >
                {{ provider.description }}
            </p>

            <div
                v-if="provider.specialities.length > 0"
                class="mt-6 flex flex-wrap gap-2"
            >
                <span
                    v-for="speciality in provider.specialities"
                    :key="speciality.id"
                    class="rounded-full bg-brand-soft px-3 py-1 text-brand-strong text-sm"
                >
                    {{ speciality.name }}
                </span>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <Button v-if="provider.phone" as-child variant="outline" size="sm">
                    <a :href="`tel:${provider.phone}`">
                        <Phone class="size-4" />
                        Call
                    </a>
                </Button>
                <Button v-if="provider.email" as-child variant="outline" size="sm">
                    <a :href="`mailto:${provider.email}`">
                        <Mail class="size-4" />
                        Email
                    </a>
                </Button>
                <Button v-if="provider.website_url" as-child variant="outline" size="sm">
                    <a :href="provider.website_url" target="_blank" rel="noopener noreferrer">
                        <Globe class="size-4" />
                        Website
                    </a>
                </Button>
            </div>

            <section v-if="provider.services.length > 0" class="mt-12">
                <h2 class="text-heading">Services</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <Card v-for="service in provider.services" :key="service.id">
                        <CardHeader>
                            <CardTitle class="text-base">{{ service.name }}</CardTitle>
                            <CardDescription v-if="service.description">
                                {{ service.description }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent v-if="service.price_from_cents">
                            <p class="font-semibold text-brand-strong">
                                From {{ formatPrice(service.price_from_cents) }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <section v-if="provider.images.length > 0" class="mt-12">
                <h2 class="text-heading">Gallery</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <figure
                        v-for="image in provider.images"
                        :key="image.id"
                        class="overflow-hidden rounded-xl"
                    >
                        <img
                            :src="image.image_path"
                            :alt="image.caption ?? provider.name"
                            class="aspect-[4/3] w-full object-cover"
                        />
                        <figcaption
                            v-if="image.caption"
                            class="mt-2 text-center text-muted-foreground text-sm"
                        >
                            {{ image.caption }}
                        </figcaption>
                    </figure>
                </div>
            </section>

            <address
                v-if="provider.physical_address"
                class="mt-12 not-italic rounded-xl border border-border bg-card p-6"
            >
                <h2 class="text-heading text-lg">Address</h2>
                <p class="mt-2 text-muted-foreground whitespace-pre-line">
                    {{ provider.physical_address }}
                </p>
            </address>
        </article>
    </MarketingLayout>
</template>
