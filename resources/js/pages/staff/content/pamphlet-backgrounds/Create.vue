<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, store } from '@/routes/staff/content/pamphlet-backgrounds';
defineProps<{
    collections: Array<{ id: string; name: string }>;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Create pamphlet background" />

        <StaffPageHeader title="Create pamphlet background" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="collection_id">Collection</Label>
    <select id="collection_id" name="collection_id" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="">Select collection</option>
        <option v-for="collection in collections" :key="collection.id" :value="collection.id">
            {{ collection.name }}
        </option>
    </select>
    <InputError :message="errors.collection_id" />
</div>

                <div class="grid gap-2">
    <Label for="name">Name</Label>
    <Input id="name" name="name" type="text"  required />
    <InputError :message="errors.name" />
</div>

                <div class="grid gap-2">
    <Label for="image_path">Image path</Label>
    <Input id="image_path" name="image_path" type="text"  required />
    <InputError :message="errors.image_path" />
</div>

                <div class="grid gap-2">
    <Label for="thumbnail_path">Thumbnail path</Label>
    <Input id="thumbnail_path" name="thumbnail_path" type="text"   />
    <InputError :message="errors.thumbnail_path" />
</div>

                <div class="grid gap-2">
    <Label for="sort_order">Sort order</Label>
    <Input id="sort_order" name="sort_order" type="number"   />
    <InputError :message="errors.sort_order" />
</div>

                <div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0" />
    <input id="is_active" name="is_active" type="checkbox" value="1" class="size-4 rounded border-input"  />
    <Label for="is_active">Active</Label>
    <InputError :message="errors.is_active" />
</div>

                    <StaffFormActions :cancel-href="index()" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
