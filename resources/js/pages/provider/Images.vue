<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import { destroy, store } from '@/routes/provider/images';

type GalleryImage = {
    id: string;
    image_path: string;
    caption: string | null;
    sort_order: number;
};

defineProps<{ images: GalleryImage[] }>();
</script>

<template>
    <ProviderLayout>
        <Head title="Gallery" />

        <Heading
            title="Gallery"
            description="Photos shown on your public directory listing"
        />

        <Card>
            <CardHeader>
                <CardTitle>Upload an image</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    reset-on-success
                    class="space-y-4"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="new-image">Image</Label>
                        <Input
                            id="new-image"
                            name="image"
                            type="file"
                            accept="image/*"
                            required
                        />
                        <InputError :message="errors.image" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="new-image-caption">Caption (optional)</Label>
                        <Input id="new-image-caption" name="caption" type="text" />
                        <InputError :message="errors.caption" />
                    </div>

                    <Button :disabled="processing">Upload</Button>
                </Form>
            </CardContent>
        </Card>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="image in images" :key="image.id">
                <CardContent class="space-y-3 pt-6">
                    <img
                        :src="`/storage/${image.image_path}`"
                        :alt="image.caption ?? 'Gallery image'"
                        class="aspect-video w-full rounded-lg border object-cover"
                    />
                    <p v-if="image.caption" class="text-sm text-muted-foreground">
                        {{ image.caption }}
                    </p>
                    <Form v-bind="destroy.form(image.id)" #default="{ processing }">
                        <Button
                            variant="destructive"
                            size="sm"
                            :disabled="processing"
                        >
                            Delete
                        </Button>
                    </Form>
                </CardContent>
            </Card>
        </div>

        <p v-if="images.length === 0" class="text-sm text-muted-foreground">
            No gallery images yet.
        </p>
    </ProviderLayout>
</template>
