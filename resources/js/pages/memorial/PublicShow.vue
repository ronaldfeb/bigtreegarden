<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    pamphlet: {
        heading: string;
        person_full_name: string;
        date_of_birth: string;
        date_of_passing: string;
        date_format: string;
        memorial_page?: {
            funeral_programme?: string | null;
            obituary?: string | null;
            hymns?: string | null;
            gallery_images?: Array<{
                id: string;
                image_path: string;
            }>;
        } | null;
    };
}>();

const normalizedDateFormat = computed(() => props.pamphlet.date_format || 'd M Y');
const activeTab = ref<'funeral_programme' | 'obituary' | 'hymns'>('funeral_programme');
const isGalleryOpen = ref(false);
const activeImageIndex = ref(0);
const galleryImages = computed(() => props.pamphlet.memorial_page?.gallery_images ?? []);
const imagePlaceholderSrc = 'https://placehold.net/default.svg';

const formatDate = (value: string): string => {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    switch (normalizedDateFormat.value) {
        case 'd/m/Y':
            return date.toLocaleDateString('en-GB');
        case 'Y-m-d':
            return date.toISOString().slice(0, 10);
        case 'j F Y':
            return date.toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });
        default:
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
    }
};

const openGallery = (index: number): void => {
    activeImageIndex.value = index;
    isGalleryOpen.value = true;
};

const closeGallery = (): void => {
    isGalleryOpen.value = false;
};

const showPrevImage = (): void => {
    if (galleryImages.value.length === 0) {
        return;
    }

    activeImageIndex.value =
        (activeImageIndex.value - 1 + galleryImages.value.length) % galleryImages.value.length;
};

const showNextImage = (): void => {
    if (galleryImages.value.length === 0) {
        return;
    }

    activeImageIndex.value = (activeImageIndex.value + 1) % galleryImages.value.length;
};

const setImageFallback = (event: Event): void => {
    const image = event.target as HTMLImageElement;

    if (image.src.endsWith(imagePlaceholderSrc)) {
        return;
    }

    image.src = imagePlaceholderSrc;
};
</script>

<template>

    <Head :title="pamphlet.person_full_name" />
    <div class="min-h-screen bg-background px-4 py-6 md:px-8 md:py-8" data-surface="memorial">
        <div class="mx-auto max-w-[57.5rem] space-y-6">
            <header class="space-y-3 border-b border-border/40 pb-6">
                <p class="text-eyebrow text-gold-light">{{ pamphlet.heading }}</p>
                <h1 class="text-balance text-display text-4xl md:text-5xl">
                    {{ pamphlet.person_full_name }}
                </h1>
                <p class="text-meta text-muted-foreground">
                    {{ formatDate(pamphlet.date_of_birth) }} – {{ formatDate(pamphlet.date_of_passing) }}
                </p>
            </header>

            <section class="space-y-3">
                <div v-if="galleryImages.length > 0"
                    class="grid auto-rows-[150px] gap-3 md:grid-cols-4 md:auto-rows-[170px]">
                    <button type="button"
                        class="col-span-2 row-span-2 overflow-hidden rounded-xl border border-border text-left md:col-span-2 md:row-span-2"
                        @click="openGallery(0)">
                        <img :src="`/storage/${galleryImages[0].image_path}`" alt="Memorial gallery image"
                            @error="setImageFallback"
                            class="h-full w-full object-cover transition-transform duration-300 hover:scale-105" />
                    </button>

                    <button v-for="(galleryImage, index) in galleryImages.slice(1, 5)" :key="galleryImage.id"
                        type="button" class="overflow-hidden rounded-xl border border-border text-left"
                        @click="openGallery(index + 1)">
                        <img :src="`/storage/${galleryImage.image_path}`" alt="Memorial gallery image"
                            @error="setImageFallback"
                            class="h-full w-full object-cover transition-transform duration-300 hover:scale-105" />
                    </button>
                </div>
                <div v-else
                    class="rounded-xl border border-dashed border-border p-6 text-center text-muted-foreground text-sm">
                    No gallery images uploaded yet.
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border border-border bg-card p-6 shadow-warm-sm md:p-8">
                <div class="flex flex-wrap gap-2 border-b border-border pb-3">
                    <button type="button" class="rounded-full px-4 py-2 text-sm transition-colors"
                        :class="activeTab === 'funeral_programme' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground hover:bg-muted/80'"
                        @click="activeTab = 'funeral_programme'">
                        Funeral Programme
                    </button>
                    <button type="button" class="rounded-full px-4 py-2 text-sm transition-colors"
                        :class="activeTab === 'obituary' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground hover:bg-muted/80'"
                        @click="activeTab = 'obituary'">
                        Obituary
                    </button>
                    <button type="button" class="rounded-full px-4 py-2 text-sm transition-colors"
                        :class="activeTab === 'hymns' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground hover:bg-muted/80'"
                        @click="activeTab = 'hymns'">
                        Hymns
                    </button>
                </div>

                <div
                    v-if="activeTab === 'funeral_programme'"
                    class="prose-memorial whitespace-pre-wrap text-foreground"
                >
                    {{ pamphlet.memorial_page?.funeral_programme || 'No funeral programme provided yet.' }}
                </div>
                <div
                    v-else-if="activeTab === 'obituary'"
                    class="prose-memorial whitespace-pre-wrap text-foreground"
                >
                    {{ pamphlet.memorial_page?.obituary || 'No obituary provided yet.' }}
                </div>
                <div v-else class="prose-memorial whitespace-pre-wrap text-foreground">
                    {{ pamphlet.memorial_page?.hymns || 'No hymns provided yet.' }}
                </div>
            </section>
        </div>

        <div v-if="isGalleryOpen && galleryImages.length > 0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
            <button type="button"
                class="absolute right-4 top-4 rounded-md bg-white/10 px-3 py-2 text-white text-sm hover:bg-white/20"
                @click="closeGallery">
                Close
            </button>
            <p class="absolute top-4 text-white/90 text-sm">
                {{ activeImageIndex + 1 }} / {{ galleryImages.length }}
            </p>
            <button type="button"
                class="absolute left-4 rounded-md bg-white/10 px-3 py-2 text-white text-sm hover:bg-white/20"
                @click="showPrevImage">
                Prev
            </button>
            <img :src="`/storage/${galleryImages[activeImageIndex].image_path}`" alt="Zoomed memorial gallery image"
                @error="setImageFallback" class="max-h-[85vh] max-w-[85vw] rounded-lg object-contain" />
            <button type="button"
                class="absolute right-4 rounded-md bg-white/10 px-3 py-2 text-white text-sm hover:bg-white/20"
                @click="showNextImage">
                Next
            </button>
        </div>
    </div>
</template>
