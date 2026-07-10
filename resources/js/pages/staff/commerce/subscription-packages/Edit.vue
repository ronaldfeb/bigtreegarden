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
import { index, show, store, update } from '@/routes/staff/commerce/subscription-packages';
defineProps<{ package: Record<string, unknown> }>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit subscription package" />

        <StaffPageHeader title="Edit subscription package" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(package.id)"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="name">Name</Label>
    <Input id="name" name="name" type="text" :default-value="package.name" required />
    <InputError :message="errors.name" />
</div>

                <div class="grid gap-2">
    <Label for="description">Description</Label>
    <Textarea id="description" name="description" rows="8" :default-value="package.description"  />
    <InputError :message="errors.description" />
</div>

                <div class="grid gap-2">
    <Label for="price_cents">Price (cents)</Label>
    <Input id="price_cents" name="price_cents" type="number" :default-value="package.price_cents" required />
    <InputError :message="errors.price_cents" />
</div>

                <div class="grid gap-2">
    <Label for="currency">Currency</Label>
    <Input id="currency" name="currency" type="text" :default-value="package.currency"  />
    <InputError :message="errors.currency" />
</div>

                <div class="grid gap-2">
    <Label for="billing_interval">Billing interval</Label>
    <select id="billing_interval" name="billing_interval" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="once_off">Once off</option>
            <option value="monthly">Monthly</option>
            <option value="annual">Annual</option>
    </select>
    <InputError :message="errors.billing_interval" />
</div>

                <div class="flex items-center gap-2">
    <input type="hidden" name="is_featured" value="0" />
    <input id="is_featured" name="is_featured" type="checkbox" value="1" class="size-4 rounded border-input" :checked="Boolean(package.is_featured)" />
    <Label for="is_featured">Featured</Label>
    <InputError :message="errors.is_featured" />
</div>

                <div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0" />
    <input id="is_active" name="is_active" type="checkbox" value="1" class="size-4 rounded border-input" :checked="Boolean(package.is_active)" />
    <Label for="is_active">Active</Label>
    <InputError :message="errors.is_active" />
</div>

                <div class="grid gap-2">
    <Label for="sort_order">Sort order</Label>
    <Input id="sort_order" name="sort_order" type="number" :default-value="package.sort_order"  />
    <InputError :message="errors.sort_order" />
</div>

                    <StaffFormActions :cancel-href="show(package.id)" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
