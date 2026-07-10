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
import { index, show, store, update } from '@/routes/staff/directory/service-providers';
defineProps<{ serviceProvider: Record<string, unknown> }>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit service provider" />

        <StaffPageHeader title="Edit service provider" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(serviceProvider.id)"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="name">Name</Label>
    <Input id="name" name="name" type="text" :default-value="serviceProvider.name" required />
    <InputError :message="errors.name" />
</div>

                <div class="grid gap-2">
    <Label for="registration_number">Registration number</Label>
    <Input id="registration_number" name="registration_number" type="text" :default-value="serviceProvider.registration_number"  />
    <InputError :message="errors.registration_number" />
</div>

                <div class="grid gap-2">
    <Label for="description">Description</Label>
    <Textarea id="description" name="description" rows="8" :default-value="serviceProvider.description"  />
    <InputError :message="errors.description" />
</div>

                <div class="grid gap-2">
    <Label for="logo_path">Logo path</Label>
    <Input id="logo_path" name="logo_path" type="text" :default-value="serviceProvider.logo_path"  />
    <InputError :message="errors.logo_path" />
</div>

                <div class="grid gap-2">
    <Label for="cover_image_path">Cover image path</Label>
    <Input id="cover_image_path" name="cover_image_path" type="text" :default-value="serviceProvider.cover_image_path"  />
    <InputError :message="errors.cover_image_path" />
</div>

                <div class="grid gap-2">
    <Label for="email">Email</Label>
    <Input id="email" name="email" type="email" :default-value="serviceProvider.email" required />
    <InputError :message="errors.email" />
</div>

                <div class="grid gap-2">
    <Label for="phone">Phone</Label>
    <Input id="phone" name="phone" type="text" :default-value="serviceProvider.phone"  />
    <InputError :message="errors.phone" />
</div>

                <div class="grid gap-2">
    <Label for="website_url">Website URL</Label>
    <Input id="website_url" name="website_url" type="url" :default-value="serviceProvider.website_url"  />
    <InputError :message="errors.website_url" />
</div>

                <div class="grid gap-2">
    <Label for="physical_address">Physical address</Label>
    <Textarea id="physical_address" name="physical_address" rows="8" :default-value="serviceProvider.physical_address"  />
    <InputError :message="errors.physical_address" />
</div>

                <div class="grid gap-2">
    <Label for="city">City</Label>
    <Input id="city" name="city" type="text" :default-value="serviceProvider.city"  />
    <InputError :message="errors.city" />
</div>

                <div class="grid gap-2">
    <Label for="province">Province</Label>
    <Input id="province" name="province" type="text" :default-value="serviceProvider.province"  />
    <InputError :message="errors.province" />
</div>

                <div class="grid gap-2">
    <Label for="status">Status</Label>
    <select id="status" name="status" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" >
        <option value="pending">Pending</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
    </select>
    <InputError :message="errors.status" />
</div>

                    <StaffFormActions :cancel-href="show(serviceProvider.id)" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
