<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { article, index } from '@/routes/help';

defineProps<{
    canRegister?: boolean;
    topic: {
        id: string;
        name: string;
        slug: string;
        description: string | null;
        icon: string | null;
    };
    articles: Array<{
        id: string;
        title: string;
        slug: string;
        excerpt: string | null;
        published_at: string | null;
    }>;
}>();
</script>

<template>
    <Head :title="topic.name" />

    <MarketingLayout :can-register="canRegister">
        <section class="mx-auto min-w-0 max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <Link
                :href="index()"
                class="text-muted-foreground text-sm hover:text-foreground"
            >
                ← Help center
            </Link>

            <header class="mt-6 space-y-3">
                <p class="text-eyebrow text-gold">Topic</p>
                <h1 class="text-display">{{ topic.name }}</h1>
                <p v-if="topic.description" class="text-body text-muted-foreground">
                    {{ topic.description }}
                </p>
            </header>

            <div v-if="articles.length > 0" class="mt-8 space-y-3">
                <Link
                    v-for="helpArticle in articles"
                    :key="helpArticle.id"
                    :href="article.url({ helpCenterTopic: topic.slug, helpCenterArticle: helpArticle.slug })"
                    class="group block"
                >
                    <Card class="transition-shadow group-hover:shadow-warm-lg">
                        <CardHeader class="flex flex-row items-center justify-between gap-4">
                            <div class="min-w-0">
                                <CardTitle class="text-heading text-base group-hover:text-brand-strong">
                                    {{ helpArticle.title }}
                                </CardTitle>
                                <CardDescription v-if="helpArticle.excerpt" class="mt-1">
                                    {{ helpArticle.excerpt }}
                                </CardDescription>
                            </div>
                            <ChevronRight class="size-5 shrink-0 text-muted-foreground group-hover:text-brand" />
                        </CardHeader>
                    </Card>
                </Link>
            </div>

            <p v-else class="mt-8 text-muted-foreground">No articles in this topic yet.</p>
        </section>
    </MarketingLayout>
</template>
