<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { topic as helpTopic } from '@/routes/help';

defineProps<{
    canRegister?: boolean;
    topic: {
        id: string;
        name: string;
        slug: string;
    };
    article: {
        id: string;
        title: string;
        slug: string;
        excerpt: string | null;
        body: string;
        published_at: string | null;
    };
}>();

function formatDate(iso: string | null): string {
    if (!iso) {
        return '';
    }

    return new Date(iso).toLocaleDateString('en-ZA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head :title="article.title" />

    <MarketingLayout :can-register="canRegister">
        <article class="mx-auto min-w-0 max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <Link
                :href="helpTopic.url(topic.slug)"
                class="text-muted-foreground text-sm hover:text-foreground"
            >
                ← {{ topic.name }}
            </Link>

            <header class="mt-6 space-y-3">
                <p class="text-eyebrow text-gold">{{ topic.name }}</p>
                <h1 class="text-display">{{ article.title }}</h1>
                <p v-if="article.excerpt" class="text-body text-lg text-muted-foreground">
                    {{ article.excerpt }}
                </p>
                <time
                    v-if="article.published_at"
                    class="block text-muted-foreground text-sm"
                    :datetime="article.published_at"
                >
                    {{ formatDate(article.published_at) }}
                </time>
            </header>

            <div
                class="prose prose-neutral mt-8 max-w-none dark:prose-invert"
                v-html="article.body"
            />
        </article>
    </MarketingLayout>
</template>
