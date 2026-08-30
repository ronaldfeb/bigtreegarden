<script setup lang="ts">
import { computed } from 'vue';
import {
    formatPamphletDate,
    normalizePamphletLayout,
    photoBlockStyle,
    textBlockStyle,
    type PamphletLayoutData,
} from '@/lib/pamphletLayout';

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
    layout?: PamphletLayoutData | null;
    pamphlet_qr_code?: { image_path: string | null; target_url?: string | null } | null;
};

const props = withDefaults(
    defineProps<{
        pamphlet: PamphletPreviewData;
        compact?: boolean;
        printable?: boolean;
    }>(),
    {
        compact: false,
        printable: false,
    },
);

const layout = computed(() => normalizePamphletLayout(props.pamphlet.layout));
const fontFamily = computed(() => props.pamphlet.font_family || 'Georgia');

const memorialImageClasses = computed(() => [
    'h-full w-full',
    props.pamphlet.image_crop_mode === 'contain' ? 'object-contain bg-white/10' : 'object-cover',
    props.pamphlet.image_shape === 'circle' ? 'rounded-full' : 'rounded-lg',
]);

const formattedDateRange = computed(
    () =>
        `${formatPamphletDate(props.pamphlet.date_of_birth, props.pamphlet.date_format)} – ${formatPamphletDate(props.pamphlet.date_of_passing, props.pamphlet.date_format)}`,
);
</script>

<template>
    <div
        class="relative mx-auto w-full min-w-0 overflow-hidden rounded-2xl border border-border bg-muted shadow-md"
        :class="[
            compact ? 'max-w-md' : 'max-w-xl',
            printable ? 'print:max-w-none print:rounded-none print:border-none print:shadow-none' : '',
        ]"
        style="container-type: inline-size"
    >
        <img
            v-if="pamphlet.background_asset_path"
            :src="`/${pamphlet.background_asset_path}`"
            alt=""
            class="pointer-events-none block h-auto w-full max-w-full select-none"
        />
        <div v-else class="aspect-[3/4] w-full bg-muted" aria-hidden="true" />

        <div class="absolute inset-0 overflow-hidden" :style="{ fontFamily }">
            <div
                class="absolute overflow-hidden border border-black/10 bg-white/10"
                :class="pamphlet.image_shape === 'circle' ? 'rounded-full' : 'rounded-lg'"
                :style="photoBlockStyle(layout.photo)"
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

            <h2
                class="absolute break-words text-center font-bold leading-tight"
                :style="textBlockStyle(layout.heading, pamphlet.heading_color || '#000000', fontFamily)"
            >
                {{ pamphlet.heading }}
            </h2>

            <p
                class="absolute break-words text-center font-semibold leading-tight"
                :style="textBlockStyle(layout.name, pamphlet.name_color || '#000000', fontFamily)"
            >
                {{ pamphlet.person_full_name }}
            </p>

            <p
                class="absolute text-center leading-tight"
                :style="textBlockStyle(layout.dates, pamphlet.dates_color || '#000000', fontFamily)"
            >
                {{ formattedDateRange }}
            </p>

            <p
                class="absolute break-words whitespace-pre-wrap text-center leading-relaxed"
                :style="textBlockStyle(layout.tribute, pamphlet.short_text_color || '#000000', fontFamily)"
            >
                {{ pamphlet.short_text }}
            </p>

            <div class="absolute bottom-[3%] left-1/2 flex -translate-x-1/2 flex-col items-center gap-1.5">
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
