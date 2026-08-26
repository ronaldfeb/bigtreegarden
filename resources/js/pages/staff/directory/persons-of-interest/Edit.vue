<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { show, update } from '@/routes/staff/directory/persons-of-interest';
defineProps<{ personOfInterest: Record<string, unknown> }>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit person of interest" />

        <StaffPageHeader title="Edit person of interest" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(personOfInterest.id)"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="first_name">First name</Label>
    <Input id="first_name" name="first_name" type="text" :default-value="personOfInterest.first_name" required />
    <InputError :message="errors.first_name" />
</div>

                <div class="grid gap-2">
    <Label for="last_name">Last name</Label>
    <Input id="last_name" name="last_name" type="text" :default-value="personOfInterest.last_name" required />
    <InputError :message="errors.last_name" />
</div>

                <div class="grid gap-2">
    <Label for="display_name">Display name</Label>
    <Input id="display_name" name="display_name" type="text" :default-value="personOfInterest.display_name"  />
    <InputError :message="errors.display_name" />
</div>

                <div class="grid gap-2">
    <Label for="date_of_birth">Date of birth</Label>
    <Input id="date_of_birth" name="date_of_birth" type="date" :default-value="personOfInterest.date_of_birth" required />
    <InputError :message="errors.date_of_birth" />
</div>

                <div class="grid gap-2">
    <Label for="date_of_passing">Date of passing</Label>
    <Input id="date_of_passing" name="date_of_passing" type="date" :default-value="personOfInterest.date_of_passing" required />
    <InputError :message="errors.date_of_passing" />
</div>

                <div class="grid gap-2">
    <Label for="place_of_birth">Place of birth</Label>
    <Input id="place_of_birth" name="place_of_birth" type="text" :default-value="personOfInterest.place_of_birth"  />
    <InputError :message="errors.place_of_birth" />
</div>

                <div class="grid gap-2">
    <Label for="place_of_passing">Place of passing</Label>
    <Input id="place_of_passing" name="place_of_passing" type="text" :default-value="personOfInterest.place_of_passing"  />
    <InputError :message="errors.place_of_passing" />
</div>

                <div class="grid gap-2">
    <Label for="profile_image_path">Profile image path</Label>
    <Input id="profile_image_path" name="profile_image_path" type="text" :default-value="personOfInterest.profile_image_path"  />
    <InputError :message="errors.profile_image_path" />
</div>

                <div class="grid gap-2">
    <Label for="status">Status</Label>
    <select id="status" name="status" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" >
        <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="archived">Archived</option>
    </select>
    <InputError :message="errors.status" />
</div>

                    <StaffFormActions :cancel-href="show(personOfInterest.id)" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
