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
import { index, store } from '@/routes/staff/content/ambassadors';
</script>

<template>
    <StaffLayout>
        <Head title="Create ambassador" />

        <StaffPageHeader title="Create ambassador" />

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
    <Label for="name">Name</Label>
    <Input id="name" name="name" type="text"  required />
    <InputError :message="errors.name" />
</div>

                <div class="grid gap-2">
    <Label for="title">Title</Label>
    <Input id="title" name="title" type="text"   />
    <InputError :message="errors.title" />
</div>

                <div class="grid gap-2">
    <Label for="description">Description</Label>
    <Textarea id="description" name="description" rows="8"   />
    <InputError :message="errors.description" />
</div>

                <div class="grid gap-2">
    <Label for="status">Status</Label>
    <select id="status" name="status" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="active">Active</option>
            <option value="inactive">Inactive</option>
    </select>
    <InputError :message="errors.status" />
</div>

                <div class="grid gap-2">
    <Label for="handle_linkedin">LinkedIn handle</Label>
    <Input id="handle_linkedin" name="handle_linkedin" type="text"   />
    <InputError :message="errors.handle_linkedin" />
</div>

                <div class="grid gap-2">
    <Label for="handle_facebook">Facebook handle</Label>
    <Input id="handle_facebook" name="handle_facebook" type="text"   />
    <InputError :message="errors.handle_facebook" />
</div>

                <div class="grid gap-2">
    <Label for="handle_instagram">Instagram handle</Label>
    <Input id="handle_instagram" name="handle_instagram" type="text"   />
    <InputError :message="errors.handle_instagram" />
</div>

                <div class="grid gap-2">
    <Label for="website_url">Website URL</Label>
    <Input id="website_url" name="website_url" type="url"   />
    <InputError :message="errors.website_url" />
</div>

                <div class="grid gap-2">
    <Label for="profile_image_path">Profile image path</Label>
    <Input id="profile_image_path" name="profile_image_path" type="text"   />
    <InputError :message="errors.profile_image_path" />
</div>

                <div class="grid gap-2">
    <Label for="sort_order">Sort order</Label>
    <Input id="sort_order" name="sort_order" type="number"   />
    <InputError :message="errors.sort_order" />
</div>

                    <StaffFormActions :cancel-href="index()" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
