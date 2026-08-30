<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Check, Clock, Globe, MapPin, Printer, QrCode, Share2, Vault } from 'lucide-vue-next';
import { computed } from 'vue';
import AmbassadorsSection from '@/components/marketing/AmbassadorsSection.vue';
import HeroCarousel from '@/components/marketing/HeroCarousel.vue';
import HeroCta from '@/components/marketing/HeroCta.vue';
import LivingLegacyPreview from '@/components/marketing/LivingLegacyPreview.vue';
import MarketingOffering from '@/components/marketing/MarketingOffering.vue';
import PartnersSection from '@/components/marketing/PartnersSection.vue';
import TestimonialsSection from '@/components/marketing/TestimonialsSection.vue';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { register } from '@/routes';

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

const funeralBenefits = [
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
        text: 'Names, dates, and tribute text — without GPS memorial site coordinates.',
    },
] as const;

const legacyBenefits = [
    {
        icon: Printer,
        text: 'Everything in Funeral Memorial: pamphlet, QR, and an online memorial page.',
    },
    {
        icon: QrCode,
        text: 'A physical QR plaque installed at the memorial site.',
    },
    {
        icon: MapPin,
        text: 'GPS coordinates so family can find the resting place with care.',
    },
    {
        icon: Share2,
        text: 'Guests can hold a printed programme and return to the digital memorial anytime.',
    },
] as const;

const livingBenefits = [
    {
        icon: Vault,
        text: 'A private vault for messages, photos, videos, and files.',
    },
    {
        icon: Clock,
        text: 'Timeboxed handover to beneficiaries after your passing.',
    },
    {
        icon: Share2,
        text: 'Secure access codes for the people you name.',
    },
    {
        icon: Check,
        text: 'Preserve what you want them to receive, when the time is right.',
    },
] as const;
</script>

<template>

    <Head :title="appName" />

    <MarketingLayout :can-register="canRegister">
        <section
            class="relative min-w-0 overflow-x-hidden px-4 py-10 sm:px-6 sm:py-12 md:min-h-[min(40rem,78vh)] md:p-0">
            <div class="hidden min-w-0 overflow-hidden md:block">
                <HeroCarousel />
            </div>
            <div
                class="mx-auto w-full max-w-xl md:pointer-events-none md:absolute md:inset-0 md:z-20 md:flex md:items-center md:justify-center md:px-6 md:pb-0 lg:px-8">
                <HeroCta class="md:pointer-events-auto w-full" />
            </div>
        </section>

        <p class="mx-auto max-w-2xl px-4 pb-16 text-center text-body text-pretty text-muted-foreground sm:px-6 lg:px-8">
            Create a private, lasting place for the people you love - and the moments
            that shaped them.
        </p>

        <MarketingOffering eyebrow="Funeral Memorial" title="An online programme guests can hold and revisit"
            body="Give mourners a printed pamphlet they can keep, with a digital memorial page they can return to long after the service. For an individual funeral."
            :benefits="funeralBenefits" :cta-href="register({ query: { intent: 'funeral-memorial' } })"
            cta-label="Start Funeral Memorial">
            <img
                src="/assets/marketing/example_banner.png"
                alt="Example Funeral Memorial pull-up banner with portrait, dates, and QR code"
                class="mx-auto w-full max-w-sm rounded-2xl object-cover shadow-warm-lg ring-1 ring-border lg:max-w-none"
            />
        </MarketingOffering>

        <MarketingOffering eyebrow="Memorial Legacy" title="A memorial that lives online and at the resting place"
            body="The same pamphlet and memorial page as Funeral Memorial, plus a physical QR plaque installed on the grave site with GPS coordinates so family can find their way back."
            :benefits="legacyBenefits" :cta-href="register({ query: { intent: 'memorial-legacy' } })"
            cta-label="Start Memorial Legacy" reverse muted>
            <img
                src="/assets/marketing/example_tombstone.png"
                alt="Example Memorial Legacy headstone with portrait, dates, and QR code"
                class="mx-auto w-full max-w-sm rounded-2xl object-cover shadow-warm-lg ring-1 ring-border lg:max-w-none"
            />
        </MarketingOffering>

        <MarketingOffering eyebrow="Living Legacy" title="Leave messages and memories for after you are gone"
            body="A timeboxed vault for photos, videos, files, and letters - sealed until they are handed over to the people you choose."
            :benefits="livingBenefits" :cta-href="register({ query: { intent: 'living-legacy' } })"
            cta-label="Start Living Legacy">
            <LivingLegacyPreview />
        </MarketingOffering>

        <TestimonialsSection :testimonials="testimonials" />

        <PartnersSection :partners="partners" />

        <AmbassadorsSection :ambassadors="ambassadors" />
    </MarketingLayout>
</template>
