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
import { index, show, store, update } from '@/routes/staff/content/blog-categories';

</script>

<template>
    <StaffLayout>
        <Head title="Create blog category" />

        <StaffPageHeader title="Create blog category" />

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
    <Label for="description">Description</Label>
    <Textarea id="description" name="description" rows="8"   />
    <InputError :message="errors.description" />
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
