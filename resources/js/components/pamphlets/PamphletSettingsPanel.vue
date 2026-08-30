<script setup lang="ts">
import { ImagePlus, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type BackgroundOption = {
    id: string;
    name: string;
    asset_path: string;
    collection_slug: string;
};

const props = defineProps<{
    heading: string;
    personFullName: string;
    shortText: string;
    fontFamily: string;
    dateFormat: 'd M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y';
    dateOfBirth: string;
    dateOfPassing: string;
    imageShape: 'circle' | 'square';
    imageCropMode: 'cover' | 'contain';
    selectedBackgroundId: string | null;
    backgrounds: BackgroundOption[];
    uploadedImagePreviewUrl: string | null;
    uploadedFileName: string | null;
    existingImageUrl: string | null;
    errors: Record<string, string>;
}>();

const emit = defineEmits<{
    'update:heading': [string];
    'update:personFullName': [string];
    'update:shortText': [string];
    'update:fontFamily': [string];
    'update:dateFormat': ['d M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y'];
    'update:dateOfBirth': [string];
    'update:dateOfPassing': [string];
    'update:imageShape': ['circle' | 'square'];
    'update:imageCropMode': ['cover' | 'contain'];
    'update:selectedBackgroundId': [string | null];
    'image-change': [Event];
    'image-drop': [DragEvent];
    'clear-image': [];
    'open-image-picker': [];
}>();

const isDraggingImage = ref(false);
const todayDate = new Date().toISOString().slice(0, 10);

const recommendedCollectionSlug = computed(() => {
    if (props.dateOfBirth === '') {
        return null;
    }

    const birthDate = new Date(props.dateOfBirth);

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
</script>

<template>
    <div class="space-y-5">
        <div class="space-y-2">
            <Label for="heading_side">Heading</Label>
            <Input
                id="heading_side"
                :model-value="heading"
                type="text"
                required
                @update:model-value="emit('update:heading', String($event))"
            />
            <InputError :message="errors.heading" />
        </div>

        <div class="space-y-2">
            <Label for="person_full_name_side">Person full name</Label>
            <Input
                id="person_full_name_side"
                :model-value="personFullName"
                type="text"
                required
                @update:model-value="emit('update:personFullName', String($event))"
            />
            <InputError :message="errors.person_full_name" />
        </div>

        <div class="space-y-2">
            <Label for="short_text_side">Tribute text</Label>
            <textarea
                id="short_text_side"
                :value="shortText"
                rows="4"
                required
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                @input="emit('update:shortText', ($event.target as HTMLTextAreaElement).value)"
            />
            <InputError :message="errors.short_text" />
        </div>

        <div class="grid gap-4">
            <div class="space-y-2">
                <Label for="font_family_side">Font family</Label>
                <select
                    id="font_family_side"
                    :value="fontFamily"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    @change="emit('update:fontFamily', ($event.target as HTMLSelectElement).value)"
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
                <Label for="date_format_side">Date format</Label>
                <select
                    id="date_format_side"
                    :value="dateFormat"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    @change="
                        emit(
                            'update:dateFormat',
                            ($event.target as HTMLSelectElement).value as 'd M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y',
                        )
                    "
                >
                    <option value="d M Y">20 May 2026</option>
                    <option value="d/m/Y">20/05/2026</option>
                    <option value="Y-m-d">2026-05-20</option>
                    <option value="j F Y">20 May 2026 (long)</option>
                </select>
                <InputError :message="errors.date_format" />
            </div>

            <div class="space-y-2">
                <Label for="date_of_birth_side">Date of birth</Label>
                <Input
                    id="date_of_birth_side"
                    :model-value="dateOfBirth"
                    type="date"
                    required
                    @update:model-value="emit('update:dateOfBirth', String($event))"
                />
                <InputError :message="errors.date_of_birth" />
            </div>

            <div class="space-y-2">
                <Label for="date_of_passing_side">Date of passing</Label>
                <Input
                    id="date_of_passing_side"
                    :model-value="dateOfPassing"
                    type="date"
                    :min="dateOfBirth || undefined"
                    :max="todayDate"
                    required
                    @update:model-value="emit('update:dateOfPassing', String($event))"
                />
                <InputError :message="errors.date_of_passing" />
            </div>
        </div>

        <div class="space-y-2">
            <Label>Memorial image</Label>
            <div
                class="relative flex min-h-28 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-border bg-muted/30 px-3 py-4 text-center transition-colors"
                :class="isDraggingImage ? 'border-brand bg-brand/5' : 'hover:border-brand/60'"
                @click="emit('open-image-picker')"
                @dragenter.prevent="isDraggingImage = true"
                @dragover.prevent="isDraggingImage = true"
                @dragleave.prevent="isDraggingImage = false"
                @drop="
                    isDraggingImage = false;
                    emit('image-drop', $event);
                "
            >
                <template v-if="uploadedImagePreviewUrl">
                    <img
                        :src="uploadedImagePreviewUrl"
                        alt="Selected memorial image"
                        class="h-20 w-20 object-cover shadow-sm"
                        :class="imageShape === 'circle' ? 'rounded-full' : 'rounded-lg'"
                    />
                    <p class="max-w-full truncate text-xs">{{ uploadedFileName }}</p>
                    <button
                        type="button"
                        class="absolute top-2 right-2 rounded-full bg-background/90 p-1 text-muted-foreground shadow-sm hover:text-foreground"
                        aria-label="Remove image"
                        @click.stop="emit('clear-image')"
                    >
                        <X class="size-4" />
                    </button>
                </template>
                <template v-else>
                    <ImagePlus class="size-7 text-muted-foreground" />
                    <p class="font-medium text-xs">Drop or click to browse</p>
                </template>
            </div>
            <InputError :message="errors.image" />
        </div>

        <div class="space-y-2">
            <Label>Image shape</Label>
            <div class="flex items-center gap-4 rounded-md border border-input px-3 py-2 text-sm">
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        value="square"
                        :checked="imageShape === 'square'"
                        @change="emit('update:imageShape', 'square')"
                    />
                    Square
                </label>
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        value="circle"
                        :checked="imageShape === 'circle'"
                        @change="emit('update:imageShape', 'circle')"
                    />
                    Circle
                </label>
            </div>
            <InputError :message="errors.image_shape" />
        </div>

        <div class="space-y-2">
            <Label>Image crop mode</Label>
            <div class="flex flex-col gap-2 rounded-md border border-input px-3 py-2 text-sm">
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        value="cover"
                        :checked="imageCropMode === 'cover'"
                        @change="emit('update:imageCropMode', 'cover')"
                    />
                    Crop to fill
                </label>
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        value="contain"
                        :checked="imageCropMode === 'contain'"
                        @change="emit('update:imageCropMode', 'contain')"
                    />
                    Fit full image
                </label>
            </div>
            <InputError :message="errors.image_crop_mode" />
        </div>

        <div class="space-y-3">
            <Label>Choose a background</Label>
            <div v-if="recommendedBackgrounds.length > 0" class="space-y-2">
                <p class="text-muted-foreground text-xs uppercase tracking-wide">Recommended</p>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="background in recommendedBackgrounds"
                        :key="background.id"
                        type="button"
                        class="overflow-hidden rounded-lg border transition"
                        :class="
                            selectedBackgroundId === background.id
                                ? 'border-brand ring-2 ring-brand/40'
                                : 'border-border hover:border-brand/50'
                        "
                        @click="emit('update:selectedBackgroundId', background.id)"
                    >
                        <img
                            :src="`/${background.asset_path}`"
                            :alt="background.name"
                            class="h-20 w-full object-contain bg-muted/40"
                        />
                    </button>
                </div>
            </div>
            <div class="space-y-2">
                <p class="text-muted-foreground text-xs uppercase tracking-wide">All backgrounds</p>
                <div class="grid max-h-56 grid-cols-2 gap-2 overflow-y-auto pr-1">
                    <button
                        v-for="background in nonRecommendedBackgrounds"
                        :key="background.id"
                        type="button"
                        class="overflow-hidden rounded-lg border transition"
                        :class="
                            selectedBackgroundId === background.id
                                ? 'border-brand ring-2 ring-brand/40'
                                : 'border-border hover:border-brand/50'
                        "
                        @click="emit('update:selectedBackgroundId', background.id)"
                    >
                        <img
                            :src="`/${background.asset_path}`"
                            :alt="background.name"
                            class="h-16 w-full object-contain bg-muted/40"
                        />
                    </button>
                </div>
            </div>
            <InputError :message="errors.background_id" />
            <InputError :message="errors.layout" />
        </div>
    </div>
</template>
