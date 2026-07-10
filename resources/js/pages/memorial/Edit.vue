<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EditableSection = {
    id: string | null;
    title: string;
    body: string;
};

const props = defineProps<{
    pamphlet: {
        id: string;
        memorial_page?: {
            sections?: Array<{ id: string; title: string; body: string | null }>;
            gallery_images?: Array<{
                id: string;
                image_path: string;
            }>;
        } | null;
        pamphlet_style?: {
            font_family?: string | null;
            is_bold: boolean;
            is_italic: boolean;
            date_format: string;
        } | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Memorial editor',
        href: '#',
    },
];

const removedImageIds = ref<string[]>([]);
const imagePlaceholderSrc = 'https://placehold.net/default.svg';

const localSections = ref<EditableSection[]>(
    (props.pamphlet.memorial_page?.sections ?? []).map((section) => ({
        id: section.id,
        title: section.title,
        body: section.body ?? '',
    })),
);
const removedSectionIds = ref<string[]>([]);

const addSection = (): void => {
    localSections.value = [...localSections.value, { id: null, title: '', body: '' }];
};

const removeSection = (index: number): void => {
    const section = localSections.value[index];

    if (section?.id) {
        removedSectionIds.value = [...removedSectionIds.value, section.id];
    }

    localSections.value = localSections.value.filter((_, sectionIndex) => sectionIndex !== index);
};

const moveSection = (index: number, direction: -1 | 1): void => {
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= localSections.value.length) {
        return;
    }

    const reordered = [...localSections.value];
    [reordered[index], reordered[targetIndex]] = [reordered[targetIndex], reordered[index]];
    localSections.value = reordered;
};

const toggleRemoveImage = (imageId: string): void => {
    if (removedImageIds.value.includes(imageId)) {
        removedImageIds.value = removedImageIds.value.filter((id: string) => id !== imageId);

        return;
    }

    removedImageIds.value = [...removedImageIds.value, imageId];
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
    <Head title="Memorial Editor" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-6xl p-4">
            <h1 class="mb-6 font-semibold text-2xl">Complete your memorial page</h1>
            <Form
                :action="`/pamphlets/${pamphlet.id}/memorial`"
                method="post"
                class="space-y-6"
                enctype="multipart/form-data"
                #default="{ errors, processing }"
            >
                <input type="hidden" name="_method" value="patch" />
                <input
                    v-for="imageId in removedImageIds"
                    :key="`remove-${imageId}`"
                    type="hidden"
                    name="remove_gallery_image_ids[]"
                    :value="imageId"
                />

                <section class="space-y-3">
                    <h2 class="font-medium text-base text-muted-foreground">Gallery</h2>
                    <div
                        v-if="(pamphlet.memorial_page?.gallery_images?.length ?? 0) > 0"
                        class="grid auto-rows-[150px] gap-3 md:grid-cols-4 md:auto-rows-[170px]"
                    >
                        <div
                            v-for="(galleryImage, index) in pamphlet.memorial_page?.gallery_images ?? []"
                            :key="galleryImage.id"
                            class="group relative overflow-hidden rounded-xl border border-border"
                            :class="index === 0 ? 'col-span-2 row-span-2 md:col-span-2 md:row-span-2' : ''"
                        >
                            <img
                                :src="`/storage/${galleryImage.image_path}`"
                                alt="Gallery image"
                                @error="setImageFallback"
                                class="h-full w-full object-cover transition-opacity"
                                :class="removedImageIds.includes(galleryImage.id) ? 'opacity-40' : 'opacity-100'"
                            />
                            <button
                                type="button"
                                class="absolute right-2 top-2 rounded-md px-2 py-1 text-xs transition-colors"
                                :class="removedImageIds.includes(galleryImage.id) ? 'bg-destructive text-destructive-foreground' : 'bg-black/70 text-white hover:bg-black/85'"
                                @click="toggleRemoveImage(galleryImage.id)"
                            >
                                {{ removedImageIds.includes(galleryImage.id) ? 'Undo' : 'Remove' }}
                            </button>
                            <div
                                v-if="removedImageIds.includes(galleryImage.id)"
                                class="absolute inset-0 flex items-center justify-center bg-black/50 text-center text-white text-xs"
                            >
                                Marked for removal
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="rounded-xl border border-dashed border-border p-6 text-center text-muted-foreground text-sm"
                    >
                        No gallery images uploaded yet.
                    </div>

                    <div class="space-y-2">
                        <Label for="gallery_images">Add gallery images</Label>
                        <Input
                            id="gallery_images"
                            name="gallery_images[]"
                            type="file"
                            accept="image/*"
                            multiple
                        />
                        <p class="text-muted-foreground text-xs">
                            Upload one or more images to show in the public gallery.
                        </p>
                        <InputError :message="errors.gallery_images" />
                        <InputError :message="errors['gallery_images.0']" />
                    </div>
                </section>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="font_family">Font family</Label>
                        <select
                            id="font_family"
                            name="font_family"
                            :value="pamphlet.pamphlet_style?.font_family ?? 'Georgia'"
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
                        <select
                            id="date_format"
                            name="date_format"
                            :value="pamphlet.pamphlet_style?.date_format ?? 'd M Y'"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="d M Y">20 May 2026</option>
                            <option value="d/m/Y">20/05/2026</option>
                            <option value="Y-m-d">2026-05-20</option>
                            <option value="j F Y">20 May 2026 (long)</option>
                        </select>
                        <InputError :message="errors.date_format" />
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="is_bold" value="0" />
                        <input type="checkbox" name="is_bold" value="1" :checked="pamphlet.pamphlet_style?.is_bold ?? false" />
                        Bold
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="is_italic" value="0" />
                        <input type="checkbox" name="is_italic" value="1" :checked="pamphlet.pamphlet_style?.is_italic ?? false" />
                        Italic
                    </label>
                </div>

                <section class="space-y-4 rounded-xl border border-border bg-card p-4 md:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border pb-3">
                        <div>
                            <h2 class="font-medium text-base">Memorial sections</h2>
                            <p class="text-muted-foreground text-sm">
                                Add sections like an obituary, funeral programme, hymns, or tributes.
                            </p>
                        </div>
                        <Button type="button" variant="outline" @click="addSection">Add section</Button>
                    </div>

                    <input
                        v-for="sectionId in removedSectionIds"
                        :key="`remove-section-${sectionId}`"
                        type="hidden"
                        name="remove_section_ids[]"
                        :value="sectionId"
                    />

                    <div
                        v-if="localSections.length === 0"
                        class="rounded-xl border border-dashed border-border p-6 text-center text-muted-foreground text-sm"
                    >
                        No sections yet. Use "Add section" to tell this person's story.
                    </div>

                    <div
                        v-for="(section, index) in localSections"
                        :key="section.id ?? `new-${index}`"
                        class="space-y-3 rounded-xl border border-border bg-background p-4"
                    >
                        <input
                            v-if="section.id"
                            type="hidden"
                            :name="`sections[${index}][id]`"
                            :value="section.id"
                        />
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-muted-foreground text-xs uppercase tracking-wide">
                                Section {{ index + 1 }}
                            </span>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="rounded-md border border-border px-2 py-1 text-muted-foreground text-xs transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-40"
                                    :disabled="index === 0"
                                    aria-label="Move section up"
                                    @click="moveSection(index, -1)"
                                >
                                    ↑
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md border border-border px-2 py-1 text-muted-foreground text-xs transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-40"
                                    :disabled="index === localSections.length - 1"
                                    aria-label="Move section down"
                                    @click="moveSection(index, 1)"
                                >
                                    ↓
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md border border-destructive/40 px-2 py-1 text-destructive text-xs transition-colors hover:bg-destructive/10"
                                    @click="removeSection(index)"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label :for="`section-title-${index}`">Title</Label>
                            <Input
                                :id="`section-title-${index}`"
                                v-model="section.title"
                                :name="`sections[${index}][title]`"
                                placeholder="e.g. Obituary, Funeral programme, Hymns"
                            />
                            <InputError :message="errors[`sections.${index}.title`]" />
                        </div>
                        <div class="space-y-2">
                            <Label :for="`section-body-${index}`">Content</Label>
                            <textarea
                                :id="`section-body-${index}`"
                                v-model="section.body"
                                :name="`sections[${index}][body]`"
                                class="w-full rounded-md border border-input px-3 py-2"
                                rows="6"
                                placeholder="Write the content for this section…"
                            />
                            <InputError :message="errors[`sections.${index}.body`]" />
                        </div>
                    </div>
                </section>

                <Button type="submit" :disabled="processing">Publish memorial page</Button>
            </Form>
        </div>
    </AppLayout>
</template>
