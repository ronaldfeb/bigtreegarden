<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, ImagePlus, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';

type BackgroundOption = {
    id: string;
    name: string;
    asset_path: string;
    collection_slug: string;
};

type EditablePamphlet = {
    id: string;
    heading: string;
    person_full_name: string;
    date_of_birth: string | null;
    date_of_passing: string | null;
    date_format: 'd M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y' | string;
    image_shape: 'circle' | 'square' | string;
    image_crop_mode: 'cover' | 'contain' | string;
    short_text: string;
    background_id: string | null;
    uploaded_image_url: string | null;
    font_family: string;
    heading_color: string;
    name_color: string;
    short_text_color: string;
    dates_color: string;
};

const props = withDefaults(
    defineProps<{
        backgrounds: BackgroundOption[];
        canRegister?: boolean;
        pamphlet?: EditablePamphlet | null;
    }>(),
    {
        canRegister: true,
        pamphlet: null,
    },
);

const page = usePage<{ auth: { user: { id: string } | null } }>();

const isEditing = computed(() => props.pamphlet !== null);
const existingImageUrl = props.pamphlet?.uploaded_image_url ?? null;

const heading = ref(props.pamphlet?.heading ?? '');
const personFullName = ref(props.pamphlet?.person_full_name ?? '');
const fontFamily = ref(props.pamphlet?.font_family ?? 'Georgia');
const dateOfBirth = ref(props.pamphlet?.date_of_birth ?? '');
const dateOfPassing = ref(props.pamphlet?.date_of_passing ?? '');
const shortText = ref(props.pamphlet?.short_text ?? '');
const dateFormat = ref<'d M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y'>(
    (props.pamphlet?.date_format as 'd M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y') ?? 'd M Y',
);
const imageShape = ref<'circle' | 'square'>(
    props.pamphlet?.image_shape === 'circle' ? 'circle' : 'square',
);
const imageCropMode = ref<'cover' | 'contain'>(
    props.pamphlet?.image_crop_mode === 'contain' ? 'contain' : 'cover',
);
const selectedBackgroundId = ref<string | null>(props.pamphlet?.background_id ?? null);
const uploadedImagePreviewUrl = ref<string | null>(existingImageUrl);
const uploadedFileName = ref<string | null>(null);
const headingColor = ref(props.pamphlet?.heading_color ?? '#000000');
const nameColor = ref(props.pamphlet?.name_color ?? '#000000');
const shortTextColor = ref(props.pamphlet?.short_text_color ?? '#000000');
const datesColor = ref(props.pamphlet?.dates_color ?? '#000000');
const imageInput = ref<HTMLInputElement | null>(null);
const headingInput = ref<HTMLInputElement | null>(null);
const isDraggingImage = ref(false);
const settingsOpen = ref(true);
const todayDate = new Date().toISOString().slice(0, 10);

const selectedBackground = computed(
    () => props.backgrounds.find((background) => background.id === selectedBackgroundId.value) ?? null,
);

const isFormComplete = computed(
    () =>
        heading.value.trim() !== '' &&
        personFullName.value.trim() !== '' &&
        shortText.value.trim() !== '' &&
        dateOfBirth.value !== '' &&
        dateOfPassing.value !== '' &&
        selectedBackgroundId.value !== null &&
        uploadedImagePreviewUrl.value !== null &&
        headingColor.value !== '' &&
        nameColor.value !== '' &&
        shortTextColor.value !== '' &&
        datesColor.value !== '',
);

const recommendedCollectionSlug = computed(() => {
    if (dateOfBirth.value === '') {
        return null;
    }

    const birthDate = new Date(dateOfBirth.value);

    if (Number.isNaN(birthDate.getTime())) {
        return null;
    }

    const age = new Date().getFullYear() - birthDate.getFullYear();

    if (age <= 12) {
        return 'kids';
    }

    if (age <= 18) {
        return 'teens';
    }

    return 'adults';
});

const recommendedBackgrounds = computed(() =>
    props.backgrounds.filter((background) => background.collection_slug === recommendedCollectionSlug.value),
);

const nonRecommendedBackgrounds = computed(() =>
    props.backgrounds.filter((background) => background.collection_slug !== recommendedCollectionSlug.value),
);

const formattedPreviewDateOfBirth = computed(() => formatPreviewDate(dateOfBirth.value));
const formattedPreviewDateOfPassing = computed(() => formatPreviewDate(dateOfPassing.value));

const previewImageClasses = computed(() => [
    imageShape.value === 'circle' ? 'rounded-full' : 'rounded-md',
    imageCropMode.value === 'contain' ? 'object-contain bg-black/5' : 'object-cover',
]);

const canvasInputClass =
    'min-w-0 flex-1 border-0 bg-transparent text-center shadow-none outline-none ring-0 placeholder:opacity-55 focus:ring-1 focus:ring-black/25';

const colorSwatchClass =
    'mt-1 size-8 shrink-0 cursor-pointer rounded border border-black/25 bg-transparent p-0';

const assignImageFile = (file: File | null): void => {
    if (uploadedImagePreviewUrl.value !== null && uploadedImagePreviewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(uploadedImagePreviewUrl.value);
    }

    uploadedImagePreviewUrl.value = null;
    uploadedFileName.value = null;

    if (file === null || imageInput.value === null) {
        if (imageInput.value !== null) {
            imageInput.value.value = '';
        }

        return;
    }

    const transfer = new DataTransfer();
    transfer.items.add(file);
    imageInput.value.files = transfer.files;
    uploadedImagePreviewUrl.value = URL.createObjectURL(file);
    uploadedFileName.value = file.name;
};

const handleImageChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    assignImageFile(target.files?.[0] ?? null);
};

const handleImageDrop = (event: DragEvent): void => {
    event.preventDefault();
    isDraggingImage.value = false;
    const file = event.dataTransfer?.files?.[0] ?? null;

    if (file !== null && file.type.startsWith('image/')) {
        assignImageFile(file);
    }
};

const clearImage = (): void => {
    assignImageFile(null);

    if (existingImageUrl !== null) {
        uploadedImagePreviewUrl.value = existingImageUrl;
        uploadedFileName.value = null;
    }
};

const formAction = computed(() =>
    isEditing.value && props.pamphlet !== null ? `/pamphlets/${props.pamphlet.id}` : '/pamphlets',
);

const formMethod = computed(() => (isEditing.value ? 'put' : 'post'));

const pageTitle = computed(() => (isEditing.value ? 'Edit Memorial Pamphlet' : 'Create Memorial Pamphlet'));

const submitLabel = computed(() => (isEditing.value ? 'Save changes' : 'Continue'));

const backHref = computed(() => {
    if (isEditing.value && props.pamphlet !== null) {
        return `/pamphlets/${props.pamphlet.id}`;
    }

    return page.props.auth?.user ? '/dashboard' : '/';
});

const backLabel = computed(() => {
    if (isEditing.value) {
        return 'Back to review';
    }

    return page.props.auth?.user ? 'Back to dashboard' : 'Back to home';
});

const formatPreviewDate = (value: string): string => {
    if (value === '') {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    switch (dateFormat.value) {
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

onBeforeUnmount(() => {
    if (uploadedImagePreviewUrl.value !== null && uploadedImagePreviewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(uploadedImagePreviewUrl.value);
    }
});

watch(settingsOpen, async (isOpen) => {
    if (isOpen) {
        return;
    }

    await nextTick();
    headingInput.value?.focus();
});
</script>

<template>

    <Head :title="pageTitle" />

    <MarketingLayout :can-register="canRegister">
        <div class="mx-auto w-full min-w-0 max-w-4xl overflow-x-hidden px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-4">
                <Link :href="backHref" class="text-muted-foreground text-sm hover:text-foreground">
                    {{ backLabel }}
                </Link>
            </div>
            <h1 class="mb-6 font-semibold text-2xl">{{ pageTitle }}</h1>

            <Form
                :action="formAction"
                :method="formMethod"
                enctype="multipart/form-data"
                class="min-w-0 max-w-full space-y-6 overflow-x-hidden"
                #default="{ errors, processing }"
            >
                <Collapsible v-model:open="settingsOpen" class="min-w-0 max-w-full rounded-2xl border border-border bg-card shadow-sm">
                    <CollapsibleTrigger
                        class="flex w-full items-center justify-between gap-3 px-4 py-4 text-left sm:px-5">
                        <div>
                            <h2 class="font-medium text-base">Design settings</h2>
                            <p class="text-muted-foreground text-sm">
                                Font, dates, image, and background.
                            </p>
                        </div>
                        <ChevronDown class="size-5 shrink-0 text-muted-foreground transition-transform duration-200"
                            :class="settingsOpen ? 'rotate-180' : ''" aria-hidden="true" />
                    </CollapsibleTrigger>

                    <CollapsibleContent
                        force-mount
                        class="min-w-0 max-w-full space-y-5 overflow-x-hidden border-t border-border/60 px-4 py-4 sm:px-5"
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="font_family">Font family</Label>
                                <select id="font_family" v-model="fontFamily" name="font_family"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                    <option value="Georgia">Georgia</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Garamond">Garamond</option>
                                    <option value="Merriweather">Merriweather</option>
                                    <option value="Arial">Arial</option>
                                </select>
                                <InputError :message="errors.font_family" />
                            </div>
                            <div class="space-y-2">
                                <Label for="date_format">Date format</Label>
                                <select id="date_format" v-model="dateFormat" name="date_format"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                    <option value="d M Y">20 May 2026</option>
                                    <option value="d/m/Y">20/05/2026</option>
                                    <option value="Y-m-d">2026-05-20</option>
                                    <option value="j F Y">20 May 2026 (long)</option>
                                </select>
                                <InputError :message="errors.date_format" />
                            </div>
                            <div class="space-y-2">
                                <Label for="date_of_birth">Date of birth</Label>
                                <Input id="date_of_birth" v-model="dateOfBirth" name="date_of_birth" type="date"
                                    required />
                                <InputError :message="errors.date_of_birth" />
                            </div>
                            <div class="space-y-2">
                                <Label for="date_of_passing">Date of passing</Label>
                                <Input id="date_of_passing" v-model="dateOfPassing" name="date_of_passing" type="date"
                                    :min="dateOfBirth || undefined" :max="todayDate" required />
                                <InputError :message="errors.date_of_passing" />
                            </div>
                        </div>



                        <div class="space-y-2">
                            <Label>Memorial image</Label>
                            <input ref="imageInput" id="image" name="image" type="file" accept="image/*" class="sr-only"
                                :required="existingImageUrl === null" @change="handleImageChange" />
                            <div class="relative flex min-h-36 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-border bg-muted/30 px-4 py-6 text-center transition-colors"
                                :class="isDraggingImage ? 'border-brand bg-brand/5' : 'hover:border-brand/60'"
                                @click="imageInput?.click()" @dragenter.prevent="isDraggingImage = true"
                                @dragover.prevent="isDraggingImage = true" @dragleave.prevent="isDraggingImage = false"
                                @drop="handleImageDrop">
                                <template v-if="uploadedImagePreviewUrl">
                                    <img :src="uploadedImagePreviewUrl" alt="Selected memorial image"
                                        class="h-24 w-24 rounded-lg object-cover shadow-sm"
                                        :class="imageShape === 'circle' ? 'rounded-full' : 'rounded-lg'" />
                                    <p class="max-w-full truncate text-sm">{{ uploadedFileName }}</p>
                                    <p class="text-muted-foreground text-xs">Click or drop to replace</p>
                                    <button type="button"
                                        class="absolute top-2 right-2 rounded-full bg-background/90 p-1 text-muted-foreground shadow-sm hover:text-foreground"
                                        aria-label="Remove image" @click.stop="clearImage">
                                        <X class="size-4" />
                                    </button>
                                </template>
                                <template v-else>
                                    <ImagePlus class="size-8 text-muted-foreground" />
                                    <p class="font-medium text-sm">Drop image or click to browse</p>
                                    <p class="text-muted-foreground text-xs">JPG, PNG up to 5MB</p>
                                </template>
                            </div>
                            <InputError :message="errors.image" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label>Image shape</Label>
                                <div class="flex items-center gap-4 rounded-md border border-input px-3 py-2 text-sm">
                                    <label class="flex items-center gap-2">
                                        <input v-model="imageShape" type="radio" name="image_shape" value="square" />
                                        Square
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input v-model="imageShape" type="radio" name="image_shape" value="circle" />
                                        Circle
                                    </label>
                                </div>
                                <InputError :message="errors.image_shape" />
                            </div>
                            <div class="space-y-2">
                                <Label>Image crop mode</Label>
                                <div class="flex items-center gap-4 rounded-md border border-input px-3 py-2 text-sm">
                                    <label class="flex items-center gap-2">
                                        <input v-model="imageCropMode" type="radio" name="image_crop_mode"
                                            value="cover" />
                                        Crop to fill
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input v-model="imageCropMode" type="radio" name="image_crop_mode"
                                            value="contain" />
                                        Fit full image
                                    </label>
                                </div>
                                <InputError :message="errors.image_crop_mode" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <Label>Choose a background</Label>
                            <div v-if="recommendedBackgrounds.length > 0" class="space-y-2">
                                <p class="text-muted-foreground text-xs uppercase tracking-wide">Recommended</p>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <label v-for="background in recommendedBackgrounds" :key="background.id"
                                        class="cursor-pointer overflow-hidden rounded-lg border transition" :class="selectedBackgroundId === background.id
                                            ? 'border-brand ring-2 ring-brand/40'
                                            : 'border-border hover:border-brand/50'
                                            ">
                                        <img :src="`/${background.asset_path}`" :alt="background.name"
                                            class="h-24 w-full object-contain bg-muted/40" />
                                        <input v-model="selectedBackgroundId" type="radio" name="background_id"
                                            :value="background.id" class="sr-only" required />
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <p class="text-muted-foreground text-xs uppercase tracking-wide">All backgrounds</p>
                                <div class="min-w-0 max-w-full overflow-x-auto overscroll-x-contain pb-1">
                                    <div class="flex w-max max-w-none gap-3">
                                        <label v-for="background in nonRecommendedBackgrounds" :key="background.id"
                                            class="w-36 shrink-0 cursor-pointer overflow-hidden rounded-lg border transition"
                                            :class="selectedBackgroundId === background.id
                                                ? 'border-brand ring-2 ring-brand/40'
                                                : 'border-border hover:border-brand/50'
                                                ">
                                            <img :src="`/${background.asset_path}`" :alt="background.name"
                                                class="h-20 w-full object-contain bg-muted/40" />
                                            <input v-model="selectedBackgroundId" type="radio" name="background_id"
                                                :value="background.id" class="sr-only" required />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <InputError :message="errors.background_id" />
                        </div>
                    </CollapsibleContent>
                </Collapsible>

                <section class="min-w-0 max-w-full space-y-3">
                    <div>
                        <h2 class="font-medium text-base">Pamphlet canvas</h2>
                        <p class="text-muted-foreground text-sm">
                            Type directly on the background. Use the colour swatches to match the artwork.
                        </p>
                    </div>

                    <div
                        class="relative mx-auto w-full min-w-0 max-w-xl overflow-hidden rounded-2xl border border-border bg-muted shadow-md"
                    >
                        <img
                            v-if="selectedBackground"
                            :src="`/${selectedBackground.asset_path}`"
                            alt=""
                            class="pointer-events-none block h-auto w-full max-w-full select-none"
                        />
                        <div v-else class="aspect-[3/4] w-full bg-muted" aria-hidden="true" />

                        <div
                            class="absolute inset-0 flex flex-col items-center justify-between overflow-hidden px-8 py-8 text-center sm:px-10"
                            :style="{ fontFamily }"
                        >
                            <div class="flex w-full min-w-0 flex-col items-center gap-2">
                                <div class="flex w-full min-w-0 items-start justify-center gap-2">
                                    <input
                                        ref="headingInput"
                                        v-model="heading"
                                        name="heading"
                                        type="text"
                                        required
                                        placeholder="Heading"
                                        :class="[canvasInputClass, 'font-bold text-3xl leading-tight sm:text-4xl']"
                                        :style="{ color: headingColor, fontFamily }"
                                    />
                                    <input
                                        v-model="headingColor"
                                        name="heading_color"
                                        type="color"
                                        :class="[colorSwatchClass, 'mt-2']"
                                        title="Heading colour"
                                        aria-label="Heading colour"
                                    />
                                </div>
                                <InputError :message="errors.heading" />
                                <InputError :message="errors.heading_color" />
                            </div>

                            <div class="flex w-full min-w-0 flex-1 flex-col items-center justify-center gap-4 overflow-hidden">
                                <div
                                    class="overflow-hidden border border-white/30 bg-white/10 backdrop-blur-[1px]"
                                    :class="
                                        imageShape === 'circle'
                                            ? 'h-40 w-40 shrink-0 rounded-full sm:h-48 sm:w-48'
                                            : 'h-48 w-full max-w-sm shrink-0 rounded-lg sm:h-56'
                                    "
                                >
                                    <img
                                        v-if="uploadedImagePreviewUrl"
                                        :src="uploadedImagePreviewUrl"
                                        alt="Memorial preview image"
                                        class="h-full w-full"
                                        :class="previewImageClasses"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center text-sm text-black/50"
                                    >
                                        Image preview
                                    </div>
                                </div>

                                <div class="flex w-full min-w-0 items-start justify-center gap-2">
                                    <input
                                        v-model="personFullName"
                                        name="person_full_name"
                                        type="text"
                                        required
                                        placeholder="Person full name"
                                        :class="[canvasInputClass, 'font-semibold text-xl sm:text-2xl']"
                                        :style="{ color: nameColor, fontFamily }"
                                    />
                                    <input
                                        v-model="nameColor"
                                        name="name_color"
                                        type="color"
                                        :class="colorSwatchClass"
                                        title="Name colour"
                                        aria-label="Name colour"
                                    />
                                </div>
                                <InputError :message="errors.person_full_name" />
                                <InputError :message="errors.name_color" />

                                <div class="flex w-full min-w-0 items-center justify-center gap-2">
                                    <p class="min-w-0 flex-1 text-sm" :style="{ color: datesColor, fontFamily }">
                                        {{ formattedPreviewDateOfBirth || 'Date of birth' }}
                                        –
                                        {{ formattedPreviewDateOfPassing || 'Date of passing' }}
                                    </p>
                                    <input
                                        v-model="datesColor"
                                        name="dates_color"
                                        type="color"
                                        :class="colorSwatchClass"
                                        title="Dates colour"
                                        aria-label="Dates colour"
                                    />
                                </div>
                                <InputError :message="errors.dates_color" />

                                <div class="flex w-full min-w-0 items-start justify-center gap-2">
                                    <textarea
                                        v-model="shortText"
                                        name="short_text"
                                        rows="3"
                                        required
                                        placeholder="Short memorial text…"
                                        :class="[canvasInputClass, 'max-h-28 resize-none text-sm leading-relaxed']"
                                        :style="{ color: shortTextColor, fontFamily }"
                                    />
                                    <input
                                        v-model="shortTextColor"
                                        name="short_text_color"
                                        type="color"
                                        :class="colorSwatchClass"
                                        title="Short text colour"
                                        aria-label="Short text colour"
                                    />
                                </div>
                                <InputError :message="errors.short_text" />
                                <InputError :message="errors.short_text_color" />
                            </div>

                            <div class="flex shrink-0 flex-col items-center gap-1.5">
                                <div class="rounded-lg bg-white p-2 shadow-sm">
                                    <svg viewBox="0 0 41 41" class="h-16 w-16" role="img" aria-label="Mock QR code">
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
                                            <rect x="16" y="2" width="2" height="2" />
                                            <rect x="20" y="2" width="2" height="2" />
                                            <rect x="16" y="6" width="2" height="2" />
                                            <rect x="22" y="6" width="2" height="2" />
                                            <rect x="18" y="10" width="2" height="2" />
                                            <rect x="16" y="14" width="2" height="2" />
                                            <rect x="20" y="14" width="2" height="2" />
                                            <rect x="24" y="14" width="2" height="2" />
                                            <rect x="2" y="16" width="2" height="2" />
                                            <rect x="6" y="16" width="2" height="2" />
                                            <rect x="10" y="18" width="2" height="2" />
                                            <rect x="14" y="18" width="2" height="2" />
                                            <rect x="18" y="18" width="5" height="5" />
                                            <rect x="26" y="16" width="2" height="2" />
                                            <rect x="30" y="18" width="2" height="2" />
                                            <rect x="34" y="16" width="2" height="2" />
                                            <rect x="16" y="26" width="2" height="2" />
                                            <rect x="20" y="28" width="2" height="2" />
                                            <rect x="24" y="26" width="2" height="2" />
                                            <rect x="28" y="28" width="2" height="2" />
                                            <rect x="32" y="30" width="2" height="2" />
                                            <rect x="36" y="28" width="2" height="2" />
                                            <rect x="28" y="34" width="2" height="2" />
                                            <rect x="34" y="36" width="2" height="2" />
                                            <rect x="16" y="34" width="2" height="2" />
                                            <rect x="20" y="36" width="2" height="2" />
                                        </g>
                                    </svg>
                                </div>
                                <p class="text-[10px] uppercase tracking-wide text-black/55">
                                    QR appears after publish
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="processing || !isFormComplete"
                        class="bg-brand hover:bg-brand-strong">
                        {{ submitLabel }}
                    </Button>
                </div>
            </Form>
        </div>
    </MarketingLayout>
</template>
