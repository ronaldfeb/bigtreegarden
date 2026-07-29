<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    pamphlet: {
        id: string;
        heading: string;
        person_full_name: string;
        date_of_birth: string | null;
        date_of_passing: string | null;
        date_format: 'd M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y' | string;
        short_text: string;
        uploaded_image_path: string;
        uploaded_image_url: string | null;
        image_shape: 'circle' | 'square';
        image_crop_mode: 'cover' | 'contain';
        background_asset_path: string | null;
        font_family: string;
        is_bold: boolean;
        is_italic: boolean;
        heading_color: string;
        name_color: string;
        short_text_color: string;
        dates_color: string;
        qr_code_image_path: string | null;
        qr_code_target_url: string | null;
    };
}>();

const baseTextStyle = computed(() => ({
    fontFamily: props.pamphlet.font_family || 'Georgia',
    fontWeight: props.pamphlet.is_bold ? '700' : '400',
    fontStyle: props.pamphlet.is_italic ? 'italic' : 'normal',
}));

const headingStyle = computed(() => ({
    ...baseTextStyle.value,
    color: props.pamphlet.heading_color || '#000000',
}));

const nameStyle = computed(() => ({
    ...baseTextStyle.value,
    color: props.pamphlet.name_color || '#000000',
}));

const shortTextStyle = computed(() => ({
    ...baseTextStyle.value,
    color: props.pamphlet.short_text_color || '#000000',
}));

const dateStyle = computed(() => ({
    ...baseTextStyle.value,
    color: props.pamphlet.dates_color || '#000000',
}));

const formattedDateRange = computed(() => {
    return `${formatDate(props.pamphlet.date_of_birth)} – ${formatDate(props.pamphlet.date_of_passing)}`;
});

const memorialImageContainerClasses = computed(() =>
    props.pamphlet.image_shape === 'circle'
        ? 'mx-auto h-72 w-72 shrink-0 overflow-hidden rounded-full border border-black/10'
        : 'mx-auto h-80 w-full max-w-2xl shrink-0 overflow-hidden rounded-lg border border-black/10',
);

const memorialImageClasses = computed(() =>
    props.pamphlet.image_crop_mode === 'contain' ? 'h-full w-full object-contain' : 'h-full w-full object-cover',
);

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

const handlePrint = (): void => {
    window.print();
};
</script>

<template>

    <Head title="Print Pamphlet" />

    <div
        class="min-h-screen min-w-0 overflow-x-hidden bg-background px-4 py-6 text-foreground print:bg-white print:p-0">
        <div class="print-hidden mx-auto flex w-full min-w-0 max-w-5xl items-center justify-between pb-4">
            <Link :href="`/pamphlets/${pamphlet.id}`" class="text-sm text-muted-foreground hover:text-foreground">
                Back to pamphlet
            </Link>
            <button type="button"
                class="inline-flex items-center rounded-md bg-primary px-5 py-2.5 text-primary-foreground text-sm"
                @click="handlePrint">
                Print
            </button>
        </div>

        <main class="mx-auto w-full min-w-0 max-w-6xl space-y-6 overflow-x-hidden print:max-w-none print:space-y-0">
            <section
                class="relative mx-auto w-full min-w-0 max-w-3xl overflow-hidden rounded-2xl border border-border bg-muted shadow-sm print:max-w-none print:rounded-none print:border-none print:shadow-none">
                <img v-if="pamphlet.background_asset_path" :src="`/${pamphlet.background_asset_path}`" alt=""
                    class="pointer-events-none block h-auto w-full max-w-full select-none print:max-h-none" />
                <div v-else class="aspect-[3/4] min-h-[80vh] w-full bg-muted print:min-h-screen" aria-hidden="true" />

                <div
                    class="absolute inset-0 flex flex-col justify-between overflow-hidden px-8 py-10 text-center sm:px-10 print:px-10 print:py-12">
                    <div class="w-full min-w-0 space-y-4">
                        <h1 class="w-full break-words text-balance font-semibold text-4xl leading-tight md:text-5xl"
                            :style="headingStyle">
                            {{ pamphlet.heading }}
                        </h1>
                        <p class="w-full break-words text-xl md:text-2xl" :style="nameStyle">
                            {{ pamphlet.person_full_name }}
                        </p>
                        <p class="w-full text-sm md:text-base" :style="dateStyle">{{ formattedDateRange }}</p>
                    </div>

                    <div
                        class="mx-auto flex w-full min-w-0 max-w-4xl flex-1 flex-col items-center justify-center gap-5 overflow-hidden">
                        <div :class="memorialImageContainerClasses">
                            <img v-if="pamphlet.uploaded_image_url" :src="pamphlet.uploaded_image_url"
                                alt="Memorial image" :class="memorialImageClasses" />
                        </div>

                        <p class="w-full max-w-3xl break-words whitespace-pre-wrap text-sm leading-relaxed md:text-base"
                            :style="shortTextStyle">
                            {{ pamphlet.short_text }}
                        </p>
                    </div>

                    <div class="mt-6 flex shrink-0 flex-col items-center gap-2 text-center">
                        <div class="rounded-xl bg-white p-3">
                            <img v-if="pamphlet.qr_code_image_path" :src="pamphlet.qr_code_image_path"
                                alt="Memorial page QR code" class="h-28 w-28" />
                        </div>
                        <p class="text-xs uppercase tracking-wide" :style="dateStyle">Scan to view memorial page</p>
                        <p class="max-w-sm break-all text-[11px] opacity-90" :style="dateStyle">
                            {{ pamphlet.qr_code_target_url }}
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
@media print {
    .print-hidden {
        display: none !important;
    }
}
</style>
