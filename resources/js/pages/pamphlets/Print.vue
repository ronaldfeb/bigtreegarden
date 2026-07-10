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
        qr_code_image_path: string | null;
        qr_code_target_url: string | null;
    };
}>();

const printTextStyle = computed(() => ({
    fontFamily: props.pamphlet.font_family || 'Georgia',
    fontWeight: props.pamphlet.is_bold ? '700' : '400',
    fontStyle: props.pamphlet.is_italic ? 'italic' : 'normal',
}));

const formattedDateRange = computed(() => {
    return `${formatDate(props.pamphlet.date_of_birth)} - ${formatDate(props.pamphlet.date_of_passing)}`;
});

const backgroundImageStyle = computed(() =>
    props.pamphlet.background_asset_path
        ? {
            backgroundImage: `url('/${props.pamphlet.background_asset_path}')`,
        }
        : { backgroundColor: 'rgb(245 245 245)' },
);

const memorialImageContainerClasses = computed(() =>
    props.pamphlet.image_shape === 'circle'
        ? 'mx-auto h-72 w-72 overflow-hidden rounded-full border border-border bg-white/85'
        : 'mx-auto h-80 w-full max-w-2xl overflow-hidden rounded-lg border border-border bg-white/85',
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

    <div class="min-h-screen bg-background px-4 py-6 text-foreground print:bg-white print:p-0">
        <div class="print-hidden mx-auto flex w-full max-w-5xl items-center justify-between pb-4">
            <Link :href="`/pamphlets/${pamphlet.id}`" class="text-sm text-muted-foreground hover:text-foreground">
                Back to pamphlet
            </Link>
        </div>

        <main class="mx-auto w-full max-w-6xl space-y-6 print:max-w-none print:space-y-0">
            <section
                class="relative min-h-screen overflow-hidden rounded-2xl border border-border bg-center bg-cover bg-no-repeat shadow-sm print:rounded-none print:border-none print:shadow-none"
                :style="backgroundImageStyle">
                <div
                    class="relative flex min-h-screen flex-col justify-between p-8 text-foreground bg-black/10 print:bg-black/35">
                    <div class="space-y-4 text-center">
                        <h1 class="text-balance font-semibold text-4xl leading-tight md:text-5xl"
                            :style="printTextStyle">
                            {{ pamphlet.heading }}
                        </h1>
                        <p class="text-xl md:text-2xl" :style="printTextStyle">{{ pamphlet.person_full_name }}</p>
                        <p class="text-sm md:text-base" :style="printTextStyle">{{ formattedDateRange }}</p>
                    </div>

                    <div class="mx-auto w-full max-w-4xl space-y-5 rounded-xl bg-white/55 p-6 text-center">
                        <div :class="memorialImageContainerClasses">
                            <img
                                v-if="pamphlet.uploaded_image_url"
                                :src="pamphlet.uploaded_image_url"
                                alt="Memorial image"
                                :class="memorialImageClasses"
                            />
                        </div>

                        <p class="mx-auto max-w-3xl whitespace-pre-wrap text-sm leading-relaxed md:text-base"
                            :style="printTextStyle">
                            {{ pamphlet.short_text }}
                        </p>
                    </div>

                    <div class="mt-6 flex flex-col items-center gap-2 text-center">
                        <div class="rounded-xl bg-white p-3">
                            <img v-if="pamphlet.qr_code_image_path" :src="pamphlet.qr_code_image_path"
                                alt="Memorial page QR code" class="h-28 w-28" />
                        </div>
                        <p class="text-xs uppercase tracking-wide">Scan to view memorial page</p>
                        <p class="max-w-sm break-all text-[11px] opacity-90">{{ pamphlet.qr_code_target_url }}</p>
                    </div>
                </div>
            </section>

            <div class="print-hidden flex justify-center pb-6">
                <button type="button"
                    class="inline-flex items-center rounded-md bg-primary px-5 py-2.5 text-primary-foreground text-sm"
                    @click="handlePrint">
                    Print
                </button>
            </div>
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
