<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, store } from '@/routes/staff/content/partners';
</script>

<template>
    <StaffLayout>
        <Head title="Create partner" />

        <StaffPageHeader title="Create partner" />

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
    <Label for="logo_path">Logo path</Label>
    <Input id="logo_path" name="logo_path" type="text"  required />
    <InputError :message="errors.logo_path" />
</div>

                <div class="grid gap-2">
    <Label for="website_url">Website URL</Label>
    <Input id="website_url" name="website_url" type="url"   />
    <InputError :message="errors.website_url" />
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
