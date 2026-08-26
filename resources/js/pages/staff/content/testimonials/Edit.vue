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
import { show, update } from '@/routes/staff/content/testimonials';
defineProps<{ testimonial: Record<string, unknown> }>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit testimonial" />

        <StaffPageHeader title="Edit testimonial" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(testimonial.id)"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="name">Name</Label>
    <Input id="name" name="name" type="text" :default-value="testimonial.name" required />
    <InputError :message="errors.name" />
</div>

                <div class="grid gap-2">
    <Label for="photo_path">Photo path</Label>
    <Input id="photo_path" name="photo_path" type="text" :default-value="testimonial.photo_path" required />
    <InputError :message="errors.photo_path" />
</div>

                <div class="grid gap-2">
    <Label for="role_or_location">Role or location</Label>
    <Input id="role_or_location" name="role_or_location" type="text" :default-value="testimonial.role_or_location"  />
    <InputError :message="errors.role_or_location" />
</div>

                <div class="grid gap-2">
    <Label for="body">Body</Label>
    <Textarea id="body" name="body" rows="8" :default-value="testimonial.body" required />
    <InputError :message="errors.body" />
</div>

                <div class="grid gap-2">
    <Label for="rating">Rating</Label>
    <Input id="rating" name="rating" type="number" :default-value="testimonial.rating"  />
    <InputError :message="errors.rating" />
</div>

                <div class="flex items-center gap-2">
    <input type="hidden" name="is_featured" value="0" />
    <input id="is_featured" name="is_featured" type="checkbox" value="1" class="size-4 rounded border-input" :checked="Boolean(testimonial.is_featured)" />
    <Label for="is_featured">Featured</Label>
    <InputError :message="errors.is_featured" />
</div>

                <div class="grid gap-2">
    <Label for="sort_order">Sort order</Label>
    <Input id="sort_order" name="sort_order" type="number" :default-value="testimonial.sort_order"  />
    <InputError :message="errors.sort_order" />
</div>

                <div class="grid gap-2">
    <Label for="status">Status</Label>
    <select id="status" name="status" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" >
        <option value="draft">Draft</option>
            <option value="published">Published</option>
    </select>
    <InputError :message="errors.status" />
</div>

                    <StaffFormActions :cancel-href="show(testimonial.id)" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
