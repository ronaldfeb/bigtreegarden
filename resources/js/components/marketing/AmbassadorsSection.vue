<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AmbassadorImageGallery from '@/components/marketing/AmbassadorImageGallery.vue';
import { show } from '@/routes/ambassadors';

defineProps<{
    ambassadors: Array<{
        id: string;
        name: string;
        title: string;
        images: Array<{
            id: string;
            image_path: string;
            caption?: string | null;
        }>;
    }>;
}>();
</script>

<template>
    <section v-if="ambassadors.length > 0" class="gold-rule overflow-x-hidden bg-card">
        <div class="mx-auto min-w-0 max-w-6xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
            <div class="space-y-4">
                <p class="text-eyebrow text-gold">Our community</p>
                <h2 class="text-balance text-heading">Our ambassadors</h2>
                <p class="max-w-2xl text-body text-pretty text-muted-foreground">
                    People who help families discover lasting ways to honour those they love.
                </p>
            </div>

            <div
                class="-mx-4 mt-10 min-w-0 overflow-x-auto overscroll-x-contain px-4 pb-2 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8"
            >
                <div class="flex w-max gap-4" role="list">
                    <article
                        v-for="ambassador in ambassadors"
                        :key="ambassador.id"
                        role="listitem"
                        class="w-72 shrink-0 overflow-hidden rounded-xl border border-border bg-background shadow-warm-sm"
                    >
                        <AmbassadorImageGallery
                            :images="ambassador.images"
                            :alt-prefix="ambassador.name"
                        />

                        <Link
                            :href="show.url(ambassador.id)"
                            class="group block space-y-1 p-4 transition-shadow hover:shadow-warm-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <h3 class="font-semibold text-base group-hover:text-brand-strong">
                                {{ ambassador.name }}
                            </h3>
                            <p class="text-muted-foreground text-sm">
                                {{ ambassador.title }}
                            </p>
                        </Link>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>
