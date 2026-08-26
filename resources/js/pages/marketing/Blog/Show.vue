<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { index } from '@/routes/blog';

defineProps<{
    canRegister?: boolean;
    blog: {
        id: string;
        title: string;
        slug: string;
        excerpt: string | null;
        body: string;
        cover_image_path: string | null;
        published_at: string | null;
        meta_title: string | null;
        meta_description: string | null;
        category: {
            id: string;
            name: string;
            slug: string;
        } | null;
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
    <Head :title="blog.meta_title ?? blog.title">
        <meta
            v-if="blog.meta_description"
            name="description"
            :content="blog.meta_description"
        />
    </Head>

    <MarketingLayout :can-register="canRegister">
        <article class="mx-auto min-w-0 max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <Link
                :href="index()"
                class="text-muted-foreground text-sm hover:text-foreground"
            >
                ← Back to blog
            </Link>

            <header class="mt-6 space-y-4">
                <p v-if="blog.category" class="text-eyebrow text-gold">
                    {{ blog.category.name }}
                </p>
                <h1 class="text-display">{{ blog.title }}</h1>
                <p v-if="blog.excerpt" class="text-body text-lg text-muted-foreground">
                    {{ blog.excerpt }}
                </p>
                <time
                    v-if="blog.published_at"
                    class="block text-muted-foreground text-sm"
                    :datetime="blog.published_at"
                >
                    {{ formatDate(blog.published_at) }}
                </time>
            </header>

            <div
                v-if="blog.cover_image_path"
                class="mt-8 overflow-hidden rounded-xl"
            >
                <img
                    :src="blog.cover_image_path"
                    :alt="blog.title"
                    class="w-full object-cover"
                />
            </div>

            <div
                class="prose prose-neutral mt-8 max-w-none dark:prose-invert"
                v-html="blog.body"
            />
        </article>
    </MarketingLayout>
</template>
