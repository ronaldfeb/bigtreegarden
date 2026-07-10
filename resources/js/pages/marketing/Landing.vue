<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AmbassadorsSection from '@/components/marketing/AmbassadorsSection.vue';
import FuneralProgrammeExplainer from '@/components/marketing/FuneralProgrammeExplainer.vue';
import HeroCarousel from '@/components/marketing/HeroCarousel.vue';
import HeroCta from '@/components/marketing/HeroCta.vue';
import PartnersSection from '@/components/marketing/PartnersSection.vue';
import TestimonialsSection from '@/components/marketing/TestimonialsSection.vue';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';

withDefaults(
    defineProps<{
        canRegister?: boolean;
        ambassadors?: Array<{
            id: string;
            name: string;
            title: string;
            images: Array<{
                id: string;
                image_path: string;
                caption?: string | null;
            }>;
        }>;
        testimonials?: Array<{
            id: string;
            name: string;
            photo_path: string;
            role_or_location: string | null;
            body: string;
            rating: number | null;
        }>;
        partners?: Array<{
            id: string;
            name: string;
            logo_path: string;
            website_url: string | null;
        }>;
    }>(),
    {
        canRegister: true,
        ambassadors: () => [],
        testimonials: () => [],
        partners: () => [],
    },
);

const page = usePage<{ name: string }>();
const appName = computed(() => page.props.name);
</script>

<template>

    <Head :title="appName" />

    <MarketingLayout :can-register="canRegister">
        <section class="relative min-w-0 overflow-x-hidden px-4 py-10 sm:px-6 sm:py-12 md:min-h-[min(40rem,78vh)] md:p-0">
            <div class="hidden min-w-0 overflow-hidden md:block">
                <HeroCarousel />
            </div>
            <div
                class="mx-auto w-full max-w-xl md:pointer-events-none md:absolute md:inset-0 md:z-20 md:flex md:items-center md:justify-center md:px-6 md:pb-0 lg:px-8"
            >
                <HeroCta class="md:pointer-events-auto w-full" />
            </div>
        </section>

        <p class="mx-auto max-w-2xl px-4 pb-16 text-center text-body text-pretty text-muted-foreground sm:px-6 lg:px-8">
            Create a private, lasting place for the people you love - and the moments
            that shaped them.
        </p>

        <FuneralProgrammeExplainer />

        <TestimonialsSection :testimonials="testimonials" />

        <PartnersSection :partners="partners" />

        <AmbassadorsSection :ambassadors="ambassadors" />
    </MarketingLayout>
</template>
