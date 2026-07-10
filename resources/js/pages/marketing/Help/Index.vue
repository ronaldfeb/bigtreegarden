<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { HelpCircle } from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { topic } from '@/routes/help';

withDefaults(
    defineProps<{
        canRegister?: boolean;
        topics?: Array<{
            id: string;
            name: string;
            slug: string;
            description: string | null;
            icon: string | null;
            published_articles_count: number;
        }>;
    }>(),
    {
        canRegister: true,
        topics: () => [],
    },
);
</script>

<template>
    <Head title="Help Center" />

    <MarketingLayout :can-register="canRegister">
        <section class="mx-auto min-w-0 max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="space-y-4 text-center">
                <p class="text-eyebrow text-gold">Support</p>
                <h1 class="text-display">How can we help?</h1>
                <p class="mx-auto max-w-2xl text-body text-pretty text-muted-foreground">
                    Browse topics to find answers about memorial pages, pamphlets, and your account.
                </p>
            </div>

            <div
                v-if="topics.length > 0"
                class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="helpTopic in topics"
                    :key="helpTopic.id"
                    :href="topic.url(helpTopic.slug)"
                    class="group"
                >
                    <Card class="h-full transition-shadow group-hover:shadow-warm-lg">
                        <CardHeader>
                            <div class="mb-2 flex size-10 items-center justify-center rounded-lg bg-brand-soft text-brand-strong">
                                <HelpCircle class="size-5" />
                            </div>
                            <CardTitle class="text-heading text-lg group-hover:text-brand-strong">
                                {{ helpTopic.name }}
                            </CardTitle>
                            <CardDescription v-if="helpTopic.description">
                                {{ helpTopic.description }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground text-sm">
                                {{ helpTopic.published_articles_count }}
                                {{ helpTopic.published_articles_count === 1 ? 'article' : 'articles' }}
                            </p>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <p v-else class="mt-10 text-center text-muted-foreground">
                Help articles are coming soon.
            </p>
        </section>
    </MarketingLayout>
</template>
