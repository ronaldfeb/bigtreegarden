<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { show } from '@/routes/blog';

withDefaults(
    defineProps<{
        canRegister?: boolean;
        blogs?: Array<{
            id: string;
            title: string;
            slug: string;
            excerpt: string | null;
            cover_image_path: string | null;
            published_at: string | null;
            category: {
                id: string;
                name: string;
                slug: string;
            } | null;
        }>;
    }>(),
    {
        canRegister: true,
        blogs: () => [],
    },
);

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
    <Head title="Blog" />

    <MarketingLayout :can-register="canRegister">
        <section class="mx-auto min-w-0 max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="space-y-4">
                <p class="text-eyebrow text-gold">Journal</p>
                <h1 class="text-display">Stories &amp; guidance</h1>
                <p class="max-w-2xl text-body text-pretty text-muted-foreground">
                    Articles on remembrance, memorial planning, and honouring those we love.
                </p>
            </div>

            <div
                v-if="blogs.length > 0"
                class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="blog in blogs"
                    :key="blog.id"
                    :href="show.url(blog.slug)"
                    class="group"
                >
                    <Card class="h-full overflow-hidden transition-shadow group-hover:shadow-warm-lg">
                        <div
                            v-if="blog.cover_image_path"
                            class="aspect-[16/9] overflow-hidden bg-muted"
                        >
                            <img
                                :src="blog.cover_image_path"
                                :alt="blog.title"
                                class="size-full object-cover transition-transform group-hover:scale-105"
                            />
                        </div>
                        <CardHeader>
                            <p
                                v-if="blog.category"
                                class="text-eyebrow text-gold"
                            >
                                {{ blog.category.name }}
                            </p>
                            <CardTitle class="text-heading text-lg group-hover:text-brand-strong">
                                {{ blog.title }}
                            </CardTitle>
                            <CardDescription v-if="blog.excerpt">{{ blog.excerpt }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <time
                                v-if="blog.published_at"
                                class="text-muted-foreground text-xs"
                                :datetime="blog.published_at"
                            >
                                {{ formatDate(blog.published_at) }}
                            </time>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <p v-else class="mt-10 text-muted-foreground">No articles published yet.</p>
        </section>
    </MarketingLayout>
</template>
