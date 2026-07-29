<script setup lang="ts">
import { computed } from 'vue';

export type PamphletPreviewData = {
    heading: string;
    person_full_name: string;
    date_of_birth: string | null;
    date_of_passing: string | null;
    date_format: string;
    short_text: string;
    uploaded_image_url: string | null;
    image_shape: 'circle' | 'square' | string;
    image_crop_mode: 'cover' | 'contain' | string;
    background_asset_path: string | null;
    font_family: string;
    heading_color: string;
    name_color: string;
    short_text_color: string;
    dates_color: string;
    pamphlet_qr_code?: { image_path: string | null; target_url?: string | null } | null;
};

const props = withDefaults(
    defineProps<{
        pamphlet: PamphletPreviewData;
        compact?: boolean;
    }>(),
    {
        compact: false,
    },
);

const memorialImageContainerClasses = computed(() =>
    props.pamphlet.image_shape === 'circle'
        ? 'h-40 w-40 shrink-0 rounded-full sm:h-48 sm:w-48'
        : 'h-48 w-full max-w-sm shrink-0 rounded-lg sm:h-56',
);

const memorialImageClasses = computed(() => [
    'h-full w-full',
    props.pamphlet.image_crop_mode === 'contain' ? 'object-contain' : 'object-cover',
]);

const formatDate = (value: string | null): string => {
    if (value === null || value === '') {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    switch (props.pamphlet.date_format) {
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

const formattedDateRange = computed(
    () => `${formatDate(props.pamphlet.date_of_birth)} – ${formatDate(props.pamphlet.date_of_passing)}`,
);

const fontFamily = computed(() => props.pamphlet.font_family || 'Georgia');
</script>

<template>
    <div
        class="relative mx-auto w-full min-w-0 overflow-hidden rounded-2xl border border-border bg-muted shadow-md"
        :class="compact ? 'max-w-md' : 'max-w-xl'"
    >
        <img
            v-if="pamphlet.background_asset_path"
            :src="`/${pamphlet.background_asset_path}`"
            alt=""
            class="pointer-events-none block h-auto w-full max-w-full select-none"
        />
        <div v-else class="aspect-[3/4] w-full bg-muted" aria-hidden="true" />

        <div
            class="absolute inset-0 flex flex-col items-center justify-between overflow-hidden px-8 py-8 text-center sm:px-10"
            :style="{ fontFamily }"
        >
            <div class="flex w-full min-w-0 flex-col items-center gap-2">
                <h2
                    class="w-full break-words font-bold text-3xl leading-tight sm:text-4xl"
                    :style="{ color: pamphlet.heading_color || '#000000', fontFamily }"
                >
                    {{ pamphlet.heading }}
                </h2>
            </div>

            <div class="flex w-full min-w-0 flex-1 flex-col items-center justify-center gap-4 overflow-hidden">
                <div
                    class="overflow-hidden border border-black/10 bg-white/10"
                    :class="memorialImageContainerClasses"
                >
                    <img
                        v-if="pamphlet.uploaded_image_url"
                        :src="pamphlet.uploaded_image_url"
                        alt="Memorial image"
                        :class="memorialImageClasses"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-sm text-black/50">
                        No image
                    </div>
                </div>

                <p
                    class="w-full break-words font-semibold text-xl sm:text-2xl"
                    :style="{ color: pamphlet.name_color || '#000000', fontFamily }"
                >
                    {{ pamphlet.person_full_name }}
                </p>

                <p class="w-full text-sm" :style="{ color: pamphlet.dates_color || '#000000', fontFamily }">
                    {{ formattedDateRange }}
                </p>

                <p
                    class="w-full max-w-sm break-words whitespace-pre-wrap text-sm leading-relaxed"
                    :style="{ color: pamphlet.short_text_color || '#000000', fontFamily }"
                >
                    {{ pamphlet.short_text }}
                </p>
            </div>

            <div class="flex shrink-0 flex-col items-center gap-1.5">
                <div class="rounded-lg bg-white p-2 shadow-sm">
                    <img
                        v-if="pamphlet.pamphlet_qr_code?.image_path"
                        :src="pamphlet.pamphlet_qr_code.image_path"
                        alt="Memorial page QR code"
                        class="h-16 w-16"
                    />
                    <svg v-else viewBox="0 0 41 41" class="h-16 w-16" role="img" aria-label="QR placeholder">
                        <rect width="41" height="41" fill="#fff" />
                        <g fill="#111">
                            <rect x="2" y="2" width="11" height="11" />
                            <rect x="4" y="4" width="7" height="7" fill="#fff" />
                            <rect x="6" y="6" width="3" height="3" />
                            <rect x="28" y="2" width="11" height="11" />
                            <rect x="30" y="4" width="7" height="7" fill="#fff" />
                            <rect x="32" y="6" width="3" height="3" />
                            <rect x="2" y="28" width="11" height="11" />
                            <rect x="4" y="30" width="7" height="7" fill="#fff" />
                            <rect x="6" y="32" width="3" height="3" />
                            <rect x="18" y="18" width="5" height="5" />
                        </g>
                    </svg>
                </div>
                <p class="text-[10px] uppercase tracking-wide text-black/55">
                    {{ pamphlet.pamphlet_qr_code?.image_path ? 'Scan to view memorial page' : 'QR appears after publish' }}
                </p>
            </div>
        </div>
    </div>
</template>
