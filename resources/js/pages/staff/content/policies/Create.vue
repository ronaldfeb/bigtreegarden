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
import { index, store } from '@/routes/staff/content/policies';
</script>

<template>
    <StaffLayout>
        <Head title="Create policy" />

        <StaffPageHeader title="Create policy" />

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
    <Label for="type">Type</Label>
    <select id="type" name="type" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="terms_of_service">Terms of service</option>
            <option value="privacy_policy">Privacy policy</option>
            <option value="about_us">About us</option>
    </select>
    <InputError :message="errors.type" />
</div>

                <div class="grid gap-2">
    <Label for="title">Title</Label>
    <Input id="title" name="title" type="text"  required />
    <InputError :message="errors.title" />
</div>

                <div class="grid gap-2">
    <Label for="body">Body</Label>
    <Textarea id="body" name="body" rows="8"  required />
    <InputError :message="errors.body" />
</div>

                <div class="grid gap-2">
    <Label for="version">Version</Label>
    <Input id="version" name="version" type="text"   />
    <InputError :message="errors.version" />
</div>

                <div class="grid gap-2">
    <Label for="published_at">Published at</Label>
    <Input id="published_at" name="published_at" type="datetime-local"   />
    <InputError :message="errors.published_at" />
</div>

                    <StaffFormActions :cancel-href="index()" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
