<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';

type BackgroundOption = {
    id: string;
    name: string;
    asset_path: string;
    collection_slug: string;
};

const props = withDefaults(
    defineProps<{
        backgrounds: BackgroundOption[];
        canRegister?: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage<{ auth: { user: { id: string } | null } }>();

const heading = ref('');
const personFullName = ref('');
const fontFamily = ref('Georgia');
const dateOfBirth = ref('');
const dateOfPassing = ref('');
const shortText = ref('');
const dateFormat = ref<'d M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y'>('d M Y');
const imageShape = ref<'circle' | 'square'>('square');
const imageCropMode = ref<'cover' | 'contain'>('cover');
const selectedBackgroundId = ref<string | null>(null);
const uploadedImagePreviewUrl = ref<string | null>(null);
const todayDate = new Date().toISOString().slice(0, 10);

const selectedBackground = computed(() =>
    props.backgrounds.find((background: BackgroundOption) => background.id === selectedBackgroundId.value) ?? null,
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
    props.backgrounds.filter((background: BackgroundOption) => background.collection_slug === recommendedCollectionSlug.value),
);

const nonRecommendedBackgrounds = computed(() =>
    props.backgrounds.filter((background: BackgroundOption) => background.collection_slug !== recommendedCollectionSlug.value),
);

const previewBackgroundStyle = computed(() => {
    if (selectedBackground.value === null) {
        return { backgroundColor: 'rgb(245 245 245)' };
    }

    return {
        backgroundImage: `url('/${selectedBackground.value.asset_path}')`,
    };
});

const formattedPreviewDateOfBirth = computed(() => formatPreviewDate(dateOfBirth.value));
const formattedPreviewDateOfPassing = computed(() => formatPreviewDate(dateOfPassing.value));

const previewImageClasses = computed(() => [
    imageShape.value === 'circle' ? 'rounded-full' : 'rounded-md',
    imageCropMode.value === 'contain' ? 'object-contain bg-background' : 'object-cover',
]);

const backHref = computed(() => (page.props.auth?.user ? '/dashboard' : '/'));
const backLabel = computed(() => (page.props.auth?.user ? 'Back to dashboard' : 'Back to home'));

const handleImageChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    if (uploadedImagePreviewUrl.value !== null) {
        URL.revokeObjectURL(uploadedImagePreviewUrl.value);
        uploadedImagePreviewUrl.value = null;
    }

    if (file !== null) {
        uploadedImagePreviewUrl.value = URL.createObjectURL(file);
    }
};

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
    if (uploadedImagePreviewUrl.value !== null) {
        URL.revokeObjectURL(uploadedImagePreviewUrl.value);
    }
});
</script>

<template>
    <Head title="Create Pamphlet" />

    <MarketingLayout :can-register="canRegister">
        <div class="mx-auto w-full min-w-0 max-w-7xl overflow-x-hidden px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-4">
                <Link :href="backHref" class="text-muted-foreground text-sm hover:text-foreground">
                    {{ backLabel }}
                </Link>
            </div>
            <h1 class="mb-6 font-semibold text-2xl">Create Memorial Pamphlet</h1>

            <div class="grid min-w-0 gap-8 lg:grid-cols-2 lg:items-start">
                <Form action="/pamphlets" method="post" class="min-w-0 space-y-6" #default="{ errors, processing }">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="heading">Heading</Label>
                            <Input id="heading" v-model="heading" name="heading" required />
                            <InputError :message="errors.heading" />
                        </div>
                        <div class="space-y-2">
                            <Label for="person_full_name">Person full name</Label>
                            <Input id="person_full_name" v-model="personFullName" name="person_full_name" required />
                            <InputError :message="errors.person_full_name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="date_of_birth">Date of birth</Label>
                            <Input id="date_of_birth" v-model="dateOfBirth" name="date_of_birth" type="date" required />
                            <InputError :message="errors.date_of_birth" />
                        </div>
                        <div class="space-y-2">
                            <Label for="date_of_passing">Date of passing</Label>
                            <Input id="date_of_passing" v-model="dateOfPassing" name="date_of_passing" type="date"
                                :min="dateOfBirth || undefined" :max="todayDate" required />
                            <InputError :message="errors.date_of_passing" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="font_family">Font family</Label>
                            <select
                                id="font_family"
                                v-model="fontFamily"
                                name="font_family"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
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
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm">
                                <option value="d M Y">20 May 2026</option>
                                <option value="d/m/Y">20/05/2026</option>
                                <option value="Y-m-d">2026-05-20</option>
                                <option value="j F Y">20 May 2026 (long)</option>
                            </select>
                            <InputError :message="errors.date_format" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="short_text">Short text</Label>
                        <textarea id="short_text" v-model="shortText" name="short_text"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm" rows="4"
                            required />
                        <InputError :message="errors.short_text" />
                    </div>

                    <section class="space-y-4 rounded-xl border border-border p-4">
                        <h2 class="font-medium text-base">Image settings</h2>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label>Image shape</Label>
                                <div class="flex items-center gap-4 rounded-md border border-input px-3 py-2 text-sm">
                                    <label class="flex items-center gap-2">
                                        <input v-model="imageShape" type="radio" name="image_shape" value="square">
                                            Square
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input v-model="imageShape" type="radio" name="image_shape" value="circle">
                                            Circle
                                    </label>
                                </div>
                                <InputError :message="errors.image_shape" />
                            </div>

                            <div class="space-y-2">
                                <Label>Image crop mode</Label>
                                <div class="flex items-center gap-4 rounded-md border border-input px-3 py-2 text-sm">
                                    <label class="flex items-center gap-2">
                                        <input v-model="imageCropMode" type="radio" name="image_crop_mode" value="cover">
                                            Crop to fill
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input v-model="imageCropMode" type="radio" name="image_crop_mode" value="contain">
                                            Fit full image
                                    </label>
                                </div>
                                <InputError :message="errors.image_crop_mode" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="image">Memorial image</Label>
                            <Input id="image" name="image" type="file" accept="image/*" required
                                @change="handleImageChange" />
                            <InputError :message="errors.image" />
                        </div>

                        <div class="space-y-4">
                            <Label>Choose a background</Label>
                            <div v-if="recommendedBackgrounds.length > 0" class="space-y-2">
                                <p class="text-muted-foreground text-xs uppercase tracking-wide">Recommended</p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label v-for="background in recommendedBackgrounds" :key="background.id"
                                        class="cursor-pointer rounded-lg border p-2"
                                        :class="selectedBackgroundId === background.id ? 'border-primary ring-1 ring-primary' : 'border-border'">
                                        <img :src="`/${background.asset_path}`" :alt="background.name"
                                            class="h-28 w-full rounded-md object-contain" />
                                        <input v-model="selectedBackgroundId" type="radio" name="background_id"
                                            :value="background.id" class="sr-only" required />
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-muted-foreground text-xs uppercase tracking-wide">All backgrounds</p>
                                <div class="overflow-x-auto overscroll-x-contain pb-1">
                                    <div class="flex w-max gap-3">
                                    <label v-for="background in nonRecommendedBackgrounds" :key="background.id"
                                        class="w-40 shrink-0 cursor-pointer rounded-lg border p-2"
                                        :class="selectedBackgroundId === background.id ? 'border-primary ring-1 ring-primary' : 'border-border'">
                                        <img :src="`/${background.asset_path}`" :alt="background.name"
                                            class="h-24 w-full rounded-md object-contain" />
                                        <input v-model="selectedBackgroundId" type="radio" name="background_id"
                                            :value="background.id" class="sr-only" required />
                                    </label>
                                    </div>
                                </div>
                            </div>
                            <InputError :message="errors.background_id" />
                        </div>
                    </section>

                    <Button type="submit" :disabled="processing">
                        Continue
                    </Button>
                </Form>

                <div class="min-w-0 overflow-hidden lg:sticky lg:top-6">
                    <p class="mb-2 font-medium text-sm text-muted-foreground">Live preview</p>
                    <div class="relative h-[760px] overflow-hidden rounded-2xl border border-border bg-center bg-no-repeat shadow-sm"
                        :style="previewBackgroundStyle">
                        <div class="absolute inset-0 bg-black/40" />
                        <div
                            class="relative flex h-full flex-col items-center justify-center space-y-4 px-8 text-center text-white">
                            <h2 class="text-balance font-bold text-3xl leading-tight">
                                {{ heading || 'Heading preview' }}
                            </h2>

                            <div class="w-full max-w-md rounded-xl bg-transparent p-4 text-foreground">
                                <div class="mb-4 overflow-hidden border border-border"
                                    :class="imageShape === 'circle' ? 'mx-auto h-48 w-48 rounded-full' : 'h-64 w-full rounded-lg'">
                                    <img v-if="uploadedImagePreviewUrl" :src="uploadedImagePreviewUrl"
                                        alt="Memorial preview image" class="h-full w-full"
                                        :class="previewImageClasses" />
                                    <div v-else
                                        class="flex h-full w-full items-center justify-center bg-muted text-muted-foreground text-sm">
                                        Image preview
                                    </div>
                                </div>

                                <p class="font-semibold text-lg" :style="{ fontFamily }">
                                    {{ personFullName || 'Person full name' }}
                                </p>
                                <p class="mt-1 text-muted-foreground text-sm">
                                    {{ formattedPreviewDateOfBirth || 'Date of birth' }} - {{
                                    formattedPreviewDateOfPassing || 'Date of passing' }}
                                </p>

                                <p class="mt-3 max-h-20 overflow-hidden text-sm leading-relaxed">
                                    {{ shortText || 'Short memorial text preview appears here as you type.' }}
                                </p>

                                <div
                                    class="mt-4 flex items-center justify-between rounded-md border border-dashed border-border bg-muted px-3 py-2">
                                    <span class="text-muted-foreground text-xs">QR code placeholder</span>
                                    <div class="h-10 w-10 rounded bg-foreground/10" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketingLayout>
</template>
