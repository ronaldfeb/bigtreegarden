<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

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
        flower_messages?: Array<{
            id: string;
            author_name: string | null;
            body: string | null;
            created_at: string | null;
        }>;
        memorial_sites?: Array<{
            latitude: number;
            longitude: number;
            geofence_radius_m: number;
        }>;
        active_day?: {
            is_active_day: boolean;
            live_comments_enabled: boolean;
            live_url: string;
        };
        flowers_store_url?: string;
        login_url?: string;
        register_url?: string;
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

const page = usePage();
const isAuthenticated = computed(() => Boolean((page.props.auth as { user: unknown } | undefined)?.user));
const flashStatus = computed(
    () => (page.props.flash as { status?: string | null } | undefined)?.status ?? null,
);

const flowerMessages = computed(() => props.pamphlet.flower_messages ?? []);
const memorialSites = computed(() => props.pamphlet.memorial_sites ?? []);
const activeDay = computed(() => props.pamphlet.active_day ?? null);
const isActiveDay = computed(() => activeDay.value?.is_active_day === true);

const flowerForm = useForm({ body: '' });
const geolocationError = ref<string | null>(null);
const isLocating = ref(false);

const getCurrentPosition = (): Promise<GeolocationPosition> =>
    new Promise((resolvePosition, rejectPosition) => {
        if (!('geolocation' in navigator)) {
            rejectPosition(new Error('unsupported'));

            return;
        }

        navigator.geolocation.getCurrentPosition(resolvePosition, rejectPosition, {
            enableHighAccuracy: true,
            timeout: 10000,
        });
    });

const submitFlowers = async (): Promise<void> => {
    if (flowerForm.processing || isLocating.value) {
        return;
    }

    geolocationError.value = null;
    isLocating.value = true;

    try {
        const position = await getCurrentPosition();

        flowerForm
            .transform((data) => ({
                ...data,
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            }))
            .post(props.pamphlet.flowers_store_url ?? '', {
                preserveScroll: true,
            });
    } catch {
        geolocationError.value =
            'We could not access your location. Flowers can only be left while you are at the memorial site, so please allow location access and try again.';
    } finally {
        isLocating.value = false;
    }
};

const distanceInMeters = (
    fromLatitude: number,
    fromLongitude: number,
    toLatitude: number,
    toLongitude: number,
): number => {
    const earthRadiusM = 6371000;
    const toRadians = (value: number): number => (value * Math.PI) / 180;
    const latitudeDelta = toRadians(toLatitude - fromLatitude);
    const longitudeDelta = toRadians(toLongitude - fromLongitude);

    const a =
        Math.sin(latitudeDelta / 2) ** 2 +
        Math.cos(toRadians(fromLatitude)) * Math.cos(toRadians(toLatitude)) * Math.sin(longitudeDelta / 2) ** 2;

    return 2 * earthRadiusM * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
};

const showSilentBanner = ref(false);

onMounted(() => {
    if (!isActiveDay.value || memorialSites.value.length === 0) {
        return;
    }

    getCurrentPosition()
        .then((position) => {
            const withinAnySite = memorialSites.value.some(
                (site) =>
                    distanceInMeters(
                        position.coords.latitude,
                        position.coords.longitude,
                        site.latitude,
                        site.longitude,
                    ) <= site.geofence_radius_m,
            );

            showSilentBanner.value = !withinAnySite;
        })
        .catch(() => {
            showSilentBanner.value = true;
        });
});

const flowerFormError = computed(() => {
    const errors = flowerForm.errors as Record<string, string>;

    return errors.location ?? errors.body ?? errors.latitude ?? errors.longitude ?? null;
});

const formatFlowerDate = (value: string | null): string => {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
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

            <div
                v-if="flashStatus"
                class="rounded-xl border border-brand/40 bg-brand/10 p-4 text-center text-foreground text-sm"
            >
                {{ flashStatus }}
            </div>

            <div
                v-if="isActiveDay && showSilentBanner"
                class="rounded-xl border border-gold/40 bg-gold/10 p-4 text-center text-foreground text-sm"
            >
                Today is the day of the service. Remember to switch your phone to silent.
            </div>

            <div
                v-if="isActiveDay && activeDay?.live_comments_enabled"
                class="rounded-xl border border-border bg-card p-4 text-center text-sm shadow-warm-sm"
            >
                <span class="text-muted-foreground">The live remembrance feed is open today.</span>
                <a
                    :href="activeDay?.live_url"
                    target="_blank"
                    rel="noopener"
                    class="ml-2 font-medium text-brand underline underline-offset-4"
                >
                    Join the live page
                </a>
            </div>

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

            <section class="space-y-4 rounded-2xl border border-border bg-card p-6 shadow-warm-sm md:p-8">
                <div class="space-y-1">
                    <p class="text-eyebrow text-gold">Flowers</p>
                    <h2 class="text-heading text-xl">Leave flowers at the memorial site</h2>
                    <p class="text-muted-foreground text-sm">
                        Flowers are a small paid tribute (R5) that can only be left while you are
                        physically at the memorial site. Approved flowers appear below.
                    </p>
                </div>

                <ul v-if="flowerMessages.length > 0" class="space-y-3">
                    <li
                        v-for="flower in flowerMessages"
                        :key="flower.id"
                        class="rounded-xl border border-border bg-background p-4"
                    >
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="font-medium text-foreground text-sm">
                                {{ flower.author_name ?? 'Anonymous' }}
                            </p>
                            <p class="shrink-0 text-muted-foreground text-xs">
                                {{ formatFlowerDate(flower.created_at) }}
                            </p>
                        </div>
                        <p class="mt-2 whitespace-pre-wrap text-foreground text-sm">{{ flower.body }}</p>
                    </li>
                </ul>
                <p v-else class="text-muted-foreground text-sm">No flowers have been left yet.</p>

                <form
                    v-if="isAuthenticated"
                    class="space-y-3 border-t border-border pt-4"
                    @submit.prevent="submitFlowers"
                >
                    <textarea
                        v-model="flowerForm.body"
                        rows="3"
                        maxlength="1000"
                        placeholder="Write a message to accompany your flowers…"
                        class="w-full resize-none rounded-xl border border-border bg-background p-3 text-foreground text-sm outline-none focus:ring-2 focus:ring-ring"
                    ></textarea>
                    <p v-if="flowerFormError" class="text-destructive text-sm">{{ flowerFormError }}</p>
                    <p v-if="geolocationError" class="text-destructive text-sm">{{ geolocationError }}</p>
                    <button
                        type="submit"
                        :disabled="flowerForm.processing || isLocating"
                        class="rounded-full bg-primary px-5 py-2 text-primary-foreground text-sm transition-opacity disabled:opacity-50"
                    >
                        {{ isLocating ? 'Checking your location…' : 'Leave flowers (R5)' }}
                    </button>
                </form>
                <p v-else class="border-t border-border pt-4 text-muted-foreground text-sm">
                    <a :href="pamphlet.login_url" class="text-foreground underline underline-offset-4">Log in</a>
                    or
                    <a :href="pamphlet.register_url" class="text-foreground underline underline-offset-4">sign up</a>
                    to leave flowers at the memorial site.
                </p>
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
