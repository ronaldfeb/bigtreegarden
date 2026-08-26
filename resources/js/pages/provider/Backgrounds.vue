<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
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
import { destroy, store } from '@/routes/provider/backgrounds';

type Background = {
    id: string;
    name: string;
    image_path: string;
    image_url: string | null;
    sort_order: number;
};

defineProps<{
    backgrounds: Background[];
}>();

function removeBackground(id: string): void {
    if (!confirm('Remove this background from your library?')) {
        return;
    }

    router.delete(destroy.url(id));
}
</script>

<template>
    <ProviderLayout>
        <Head title="Background library" />
        <Heading
            title="Background library"
            description="Upload reusable pamphlet backgrounds for memorials you create"
        />

        <Card>
            <CardHeader>
                <CardTitle>Add background</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    enctype="multipart/form-data"
                    class="grid gap-4 sm:grid-cols-3"
                    reset-on-success
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" type="text" required />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="image">Image</Label>
                        <Input id="image" name="image" type="file" accept="image/*" required />
                        <InputError :message="errors.image" />
                    </div>
                    <div class="flex items-end">
                        <Button type="submit" :disabled="processing">Upload</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="background in backgrounds" :key="background.id">
                <CardContent class="space-y-3 pt-6">
                    <img
                        v-if="background.image_url"
                        :src="background.image_url"
                        :alt="background.name"
                        class="h-40 w-full rounded-lg object-cover"
                    />
                    <p class="font-medium">{{ background.name }}</p>
                    <Button type="button" variant="destructive" size="sm" @click="removeBackground(background.id)">
                        Remove
                    </Button>
                </CardContent>
            </Card>
        </div>
        <p v-if="backgrounds.length === 0" class="text-sm text-muted-foreground">
            No custom backgrounds yet.
        </p>
    </ProviderLayout>
</template>
