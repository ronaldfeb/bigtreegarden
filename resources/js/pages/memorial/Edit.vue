<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    pamphlet: {
        id: string;
        memorial_page?: {
            funeral_programme?: string | null;
            obituary?: string | null;
            hymns?: string | null;
            additional_sections?: Array<{ title: string; content: string | null }>;
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

const activeTab = ref<'funeral_programme' | 'obituary' | 'hymns'>('funeral_programme');
const removedImageIds = ref<string[]>([]);
const imagePlaceholderSrc = 'https://placehold.net/default.svg';

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

                <div class="space-y-4 rounded-xl border border-border bg-card p-4 md:p-6">
                    <div class="flex flex-wrap gap-2 border-b border-border pb-3">
                        <button
                            type="button"
                            class="rounded-full px-4 py-2 text-sm transition-colors"
                            :class="
                                activeTab === 'funeral_programme'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted text-muted-foreground hover:bg-muted/80'
                            "
                            @click="activeTab = 'funeral_programme'"
                        >
                            Funeral Programme
                        </button>
                        <button
                            type="button"
                            class="rounded-full px-4 py-2 text-sm transition-colors"
                            :class="
                                activeTab === 'obituary'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted text-muted-foreground hover:bg-muted/80'
                            "
                            @click="activeTab = 'obituary'"
                        >
                            Obituary
                        </button>
                        <button
                            type="button"
                            class="rounded-full px-4 py-2 text-sm transition-colors"
                            :class="
                                activeTab === 'hymns'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted text-muted-foreground hover:bg-muted/80'
                            "
                            @click="activeTab = 'hymns'"
                        >
                            Hymns
                        </button>
                    </div>

                    <div v-show="activeTab === 'funeral_programme'" class="space-y-2">
                        <Label for="funeral_programme">Funeral programme</Label>
                        <textarea
                            id="funeral_programme"
                            name="funeral_programme"
                            :value="pamphlet.memorial_page?.funeral_programme ?? ''"
                            class="w-full rounded-md border border-input px-3 py-2"
                            rows="7"
                        />
                    </div>
                    <div v-show="activeTab === 'obituary'" class="space-y-2">
                        <Label for="obituary">Obituary</Label>
                        <textarea
                            id="obituary"
                            name="obituary"
                            :value="pamphlet.memorial_page?.obituary ?? ''"
                            class="w-full rounded-md border border-input px-3 py-2"
                            rows="7"
                        />
                    </div>
                    <div v-show="activeTab === 'hymns'" class="space-y-2">
                        <Label for="hymns">Hymns</Label>
                        <textarea
                            id="hymns"
                            name="hymns"
                            :value="pamphlet.memorial_page?.hymns ?? ''"
                            class="w-full rounded-md border border-input px-3 py-2"
                            rows="7"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>Additional section</Label>
                    <Input name="additional_sections[0][title]" placeholder="Section title" />
                    <textarea name="additional_sections[0][content]" class="w-full rounded-md border border-input px-3 py-2" rows="4" />
                    <InputError :message="errors['additional_sections.0.title']" />
                </div>

                <Button type="submit" :disabled="processing">Publish memorial page</Button>
            </Form>
        </div>
    </AppLayout>
</template>
