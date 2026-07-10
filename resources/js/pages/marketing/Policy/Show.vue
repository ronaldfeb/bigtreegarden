<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';

defineProps<{
    canRegister?: boolean;
    policy: {
        type: string;
        title: string;
        body: string;
        version: string | null;
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
    <Head :title="policy.title" />

    <MarketingLayout :can-register="canRegister">
        <article class="mx-auto min-w-0 max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <header class="space-y-3 border-b border-border pb-8">
                <h1 class="text-display">{{ policy.title }}</h1>
                <div class="flex flex-wrap gap-3 text-muted-foreground text-sm">
                    <span v-if="policy.version">Version {{ policy.version }}</span>
                    <time
                        v-if="policy.published_at"
                        :datetime="policy.published_at"
                    >
                        Updated {{ formatDate(policy.published_at) }}
                    </time>
                </div>
            </header>

            <div
                class="prose prose-neutral mt-8 max-w-none dark:prose-invert"
                v-html="policy.body"
            />
        </article>
    </MarketingLayout>
</template>
