<script setup lang="ts">
import { ChevronLeft, ChevronRight, Info, X } from 'lucide-vue-next';
import { computed, onUnmounted, ref, watch } from 'vue';

type GalleryImage = {
    id: string;
    image_path: string;
    caption?: string | null;
};

type RightThumbnail = {
    image: GalleryImage;
    index: number;
    rowSpan: number;
    showOverlay: boolean;
    overlayCount: number;
};

const props = withDefaults(
    defineProps<{
        images: GalleryImage[];
        altPrefix?: string;
    }>(),
    {
        altPrefix: 'Ambassador',
    },
);

const imagePlaceholderSrc = 'https://placehold.net/default.svg';
const isGalleryOpen = ref(false);
const activeImageIndex = ref(0);

const activeImage = computed(() => props.images[activeImageIndex.value] ?? null);

const rightThumbnails = computed((): RightThumbnail[] => {
    const images = props.images;

    if (images.length <= 1) {
        return [];
    }

    if (images.length === 2) {
        return [
            {
                image: images[1],
                index: 1,
                rowSpan: 3,
                showOverlay: false,
                overlayCount: 0,
            },
        ];
    }

    if (images.length === 3) {
        return [
            {
                image: images[1],
                index: 1,
                rowSpan: 2,
                showOverlay: false,
                overlayCount: 0,
            },
            {
                image: images[2],
                index: 2,
                rowSpan: 1,
                showOverlay: false,
                overlayCount: 0,
            },
        ];
    }

    return [
        {
            image: images[1],
            index: 1,
            rowSpan: 1,
            showOverlay: false,
            overlayCount: 0,
        },
        {
            image: images[2],
            index: 2,
            rowSpan: 1,
            showOverlay: false,
            overlayCount: 0,
        },
        {
            image: images[3],
            index: 3,
            rowSpan: 1,
            showOverlay: images.length > 4,
            overlayCount: images.length - 4,
        },
    ];
});

const imageSrc = (imagePath: string): string =>
    imagePath.startsWith('assets/') ? `/${imagePath}` : `/storage/${imagePath}`;

const imageAlt = (index: number): string => `${props.altPrefix} gallery image ${index + 1}`;

const openGallery = (index: number): void => {
    activeImageIndex.value = index;
    isGalleryOpen.value = true;
};

const closeGallery = (): void => {
    isGalleryOpen.value = false;
};

const showPrevImage = (): void => {
    if (props.images.length === 0) {
        return;
    }

    activeImageIndex.value =
        (activeImageIndex.value - 1 + props.images.length) % props.images.length;
};

const showNextImage = (): void => {
    if (props.images.length === 0) {
        return;
    }

    activeImageIndex.value = (activeImageIndex.value + 1) % props.images.length;
};

const setImageFallback = (event: Event): void => {
    const image = event.target as HTMLImageElement;

    if (image.src.endsWith(imagePlaceholderSrc)) {
        return;
    }

    image.src = imagePlaceholderSrc;
};

const handleKeydown = (event: KeyboardEvent): void => {
    if (!isGalleryOpen.value) {
        return;
    }

    if (event.key === 'Escape') {
        closeGallery();
    }

    if (event.key === 'ArrowLeft') {
        showPrevImage();
    }

    if (event.key === 'ArrowRight') {
        showNextImage();
    }
};

watch(isGalleryOpen, (open) => {
    if (open) {
        document.addEventListener('keydown', handleKeydown);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', handleKeydown);
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div v-if="images.length > 0">
        <button
            v-if="images.length === 1"
            type="button"
            class="relative block w-full overflow-hidden text-left"
            @click.stop="openGallery(0)"
        >
            <img
                :src="imageSrc(images[0].image_path)"
                :alt="imageAlt(0)"
                class="aspect-[4/3] w-full object-cover"
                @error="setImageFallback"
            />
            <span
                v-if="images[0].caption"
                class="absolute bottom-2 left-2 flex size-6 items-center justify-center rounded-full bg-black/50 text-white"
                :title="images[0].caption"
            >
                <Info class="size-3.5" aria-hidden="true" />
                <span class="sr-only">{{ images[0].caption }}</span>
            </span>
        </button>

        <div
            v-else
            class="grid aspect-[4/3] grid-cols-[3fr_2fr] grid-rows-3 gap-1"
        >
            <button
                type="button"
                class="relative row-span-3 overflow-hidden text-left"
                @click.stop="openGallery(0)"
            >
                <img
                    :src="imageSrc(images[0].image_path)"
                    :alt="imageAlt(0)"
                    class="size-full object-cover"
                    @error="setImageFallback"
                />
                <span
                    v-if="images[0].caption"
                    class="absolute bottom-2 left-2 flex size-6 items-center justify-center rounded-full bg-black/50 text-white"
                    :title="images[0].caption"
                >
                    <Info class="size-3.5" aria-hidden="true" />
                    <span class="sr-only">{{ images[0].caption }}</span>
                </span>
            </button>

            <button
                v-for="thumbnail in rightThumbnails"
                :key="thumbnail.image.id"
                type="button"
                class="relative overflow-hidden text-left"
                :class="{
                    'row-span-3': thumbnail.rowSpan === 3,
                    'row-span-2': thumbnail.rowSpan === 2,
                    'row-span-1': thumbnail.rowSpan === 1,
                }"
                @click.stop="openGallery(thumbnail.index)"
            >
                <img
                    :src="imageSrc(thumbnail.image.image_path)"
                    :alt="imageAlt(thumbnail.index)"
                    class="size-full object-cover"
                    @error="setImageFallback"
                />
                <span
                    v-if="thumbnail.showOverlay"
                    class="absolute inset-0 flex items-center justify-center bg-black/55 font-semibold text-lg text-white"
                >
                    +{{ thumbnail.overlayCount }}
                </span>
            </button>
        </div>

        <Teleport to="body">
            <div
                v-if="isGalleryOpen && activeImage"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                role="dialog"
                aria-modal="true"
                :aria-label="`${altPrefix} image gallery`"
            >
                <button
                    type="button"
                    class="absolute top-4 right-4 inline-flex items-center gap-1 rounded-md bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/20"
                    @click="closeGallery"
                >
                    <X class="size-4" aria-hidden="true" />
                    Close
                </button>

                <p class="absolute top-4 left-1/2 -translate-x-1/2 text-sm text-white/90">
                    {{ activeImageIndex + 1 }} / {{ images.length }}
                </p>

                <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute left-4 inline-flex items-center gap-1 rounded-md bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/20"
                    @click="showPrevImage"
                >
                    <ChevronLeft class="size-4" aria-hidden="true" />
                    Prev
                </button>

                <div class="flex max-h-[85vh] max-w-[85vw] flex-col items-center gap-3">
                    <img
                        :src="imageSrc(activeImage.image_path)"
                        :alt="imageAlt(activeImageIndex)"
                        class="max-h-[75vh] max-w-full rounded-lg object-contain"
                        @error="setImageFallback"
                    />
                    <p
                        v-if="activeImage.caption"
                        class="max-w-xl text-center text-sm text-white/90"
                    >
                        {{ activeImage.caption }}
                    </p>
                </div>

                <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute right-4 inline-flex items-center gap-1 rounded-md bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/20"
                    @click="showNextImage"
                >
                    Next
                    <ChevronRight class="size-4" aria-hidden="true" />
                </button>
            </div>
        </Teleport>
    </div>
</template>
