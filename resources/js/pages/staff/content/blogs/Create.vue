<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, store } from '@/routes/staff/content/blogs';
defineProps<{
    categories: Array<{ id: string; name: string }>;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Create blog" />

        <StaffPageHeader title="Create blog" />

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
    <Label for="title">Title</Label>
    <Input id="title" name="title" type="text"  required />
    <InputError :message="errors.title" />
</div>

                <div class="grid gap-2">
    <Label for="blog_category_id">Category</Label>
    <select id="blog_category_id" name="blog_category_id" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm">
        <option value="">None</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
        </option>
    </select>
    <InputError :message="errors.blog_category_id" />
</div>

                <div class="grid gap-2">
    <Label for="excerpt">Excerpt</Label>
    <Textarea id="excerpt" name="excerpt" rows="8"   />
    <InputError :message="errors.excerpt" />
</div>

                <div class="grid gap-2">
    <Label for="body">Body</Label>
    <Textarea id="body" name="body" rows="8"  required />
    <InputError :message="errors.body" />
</div>

                <div class="grid gap-2">
    <Label for="cover_image_path">Cover image path</Label>
    <Input id="cover_image_path" name="cover_image_path" type="text"   />
    <InputError :message="errors.cover_image_path" />
</div>

                <div class="grid gap-2">
    <Label for="status">Status</Label>
    <select id="status" name="status" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="draft">Draft</option>
            <option value="published">Published</option>
    </select>
    <InputError :message="errors.status" />
</div>

                <div class="grid gap-2">
    <Label for="published_at">Published at</Label>
    <Input id="published_at" name="published_at" type="datetime-local"   />
    <InputError :message="errors.published_at" />
</div>

                <div class="grid gap-2">
    <Label for="meta_title">Meta title</Label>
    <Input id="meta_title" name="meta_title" type="text"   />
    <InputError :message="errors.meta_title" />
</div>

                <div class="grid gap-2">
    <Label for="meta_description">Meta description</Label>
    <Input id="meta_description" name="meta_description" type="text"   />
    <InputError :message="errors.meta_description" />
</div>

                    <StaffFormActions :cancel-href="index()" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
