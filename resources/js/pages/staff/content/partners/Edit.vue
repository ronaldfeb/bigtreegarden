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
import { index, show, store, update } from '@/routes/staff/content/partners';
defineProps<{ partner: Record<string, unknown> }>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit partner" />

        <StaffPageHeader title="Edit partner" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(partner.id)"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="name">Name</Label>
    <Input id="name" name="name" type="text" :default-value="partner.name" required />
    <InputError :message="errors.name" />
</div>

                <div class="grid gap-2">
    <Label for="logo_path">Logo path</Label>
    <Input id="logo_path" name="logo_path" type="text" :default-value="partner.logo_path" required />
    <InputError :message="errors.logo_path" />
</div>

                <div class="grid gap-2">
    <Label for="website_url">Website URL</Label>
    <Input id="website_url" name="website_url" type="url" :default-value="partner.website_url"  />
    <InputError :message="errors.website_url" />
</div>

                <div class="grid gap-2">
    <Label for="sort_order">Sort order</Label>
    <Input id="sort_order" name="sort_order" type="number" :default-value="partner.sort_order"  />
    <InputError :message="errors.sort_order" />
</div>

                <div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0" />
    <input id="is_active" name="is_active" type="checkbox" value="1" class="size-4 rounded border-input" :checked="Boolean(partner.is_active)" />
    <Label for="is_active">Active</Label>
    <InputError :message="errors.is_active" />
</div>

                    <StaffFormActions :cancel-href="show(partner.id)" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
