<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Facebook, Globe, Instagram, Linkedin } from 'lucide-vue-next';
import AmbassadorImageGallery from '@/components/marketing/AmbassadorImageGallery.vue';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { home } from '@/routes';

const props = defineProps<{
    ambassador: {
        id: string;
        name: string;
        title: string;
        description: string | null;
        images: Array<{
            id: string;
            image_path: string;
            caption?: string | null;
        }>;
        handle_linkedin: string | null;
        handle_facebook: string | null;
        handle_instagram: string | null;
        website_url: string | null;
    };
}>();

const socialLinks = computed(() =>
    [
        {
            label: 'LinkedIn',
            href: props.ambassador.handle_linkedin,
            icon: Linkedin,
        },
        {
            label: 'Facebook',
            href: props.ambassador.handle_facebook,
            icon: Facebook,
        },
        {
            label: 'Instagram',
            href: props.ambassador.handle_instagram,
            icon: Instagram,
        },
        {
            label: 'Website',
            href: props.ambassador.website_url,
            icon: Globe,
        },
    ].filter((link): link is typeof link & { href: string } => Boolean(link.href)),
);
</script>

<template>
    <Head :title="ambassador.name" />

    <MarketingLayout>
        <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <Link
                :href="home()"
                class="text-muted-foreground text-sm hover:text-foreground"
            >
                &larr; Back to home
            </Link>

            <div class="mt-8 overflow-hidden rounded-2xl border border-border bg-card shadow-warm-lg">
                <AmbassadorImageGallery
                    :images="ambassador.images"
                    :alt-prefix="ambassador.name"
                />

                <div class="space-y-6 p-6 sm:p-8">
                    <header class="space-y-2">
                        <p class="text-eyebrow text-gold">Ambassador</p>
                        <h1 class="text-balance text-display text-3xl sm:text-4xl">
                            {{ ambassador.name }}
                        </h1>
                        <p class="text-body text-lg text-muted-foreground">
                            {{ ambassador.title }}
                        </p>
                    </header>

                    <p
                        v-if="ambassador.description"
                        class="text-body text-pretty whitespace-pre-line text-muted-foreground"
                    >
                        {{ ambassador.description }}
                    </p>

                    <nav
                        v-if="socialLinks.length > 0"
                        class="flex flex-wrap gap-3 border-t border-border pt-6"
                        aria-label="Ambassador links"
                    >
                        <a
                            v-for="link in socialLinks"
                            :key="link.label"
                            :href="link.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm transition-colors hover:bg-muted"
                        >
                            <component :is="link.icon" class="size-4" aria-hidden="true" />
                            {{ link.label }}
                        </a>
                    </nav>
                </div>
            </div>
        </article>
    </MarketingLayout>
</template>
