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
import { index, show, store } from '@/routes/staff/marketing/leads';

defineProps<{
    adverts: Array<{ id: string; client_name: string; code: string }>;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Create lead" />

        <StaffPageHeader title="Create lead" />

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
    <Label for="email">Email</Label>
    <Input id="email" name="email" type="email"   />
    <InputError :message="errors.email" />
</div>

                <div class="grid gap-2">
    <Label for="phone">Phone</Label>
    <Input id="phone" name="phone" type="text"   />
    <InputError :message="errors.phone" />
</div>

                <div class="grid gap-2">
    <Label for="organisation">Organisation</Label>
    <Input id="organisation" name="organisation" type="text"   />
    <InputError :message="errors.organisation" />
</div>

                <div class="grid gap-2">
    <Label for="source">Source</Label>
    <select id="source" name="source" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="advert">Advert</option>
            <option value="website">Website</option>
            <option value="referral">Referral</option>
            <option value="walk_in">Walk in</option>
            <option value="other">Other</option>
    </select>
    <InputError :message="errors.source" />
</div>

                <div class="grid gap-2">
    <Label for="status">Status</Label>
    <select id="status" name="status" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" >
        <option value="new">New</option>
            <option value="contacted">Contacted</option>
            <option value="qualified">Qualified</option>
            <option value="converted">Converted</option>
            <option value="lost">Lost</option>
    </select>
    <InputError :message="errors.status" />
</div>

                <div class="grid gap-2">
    <Label for="marketing_advert_id">Marketing advert</Label>
    <select id="marketing_advert_id" name="marketing_advert_id" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm">
        <option value="">None</option>
        <option v-for="advert in adverts" :key="advert.id" :value="advert.id">
            {{ advert.client_name }} ({{ advert.code }})
        </option>
    </select>
    <InputError :message="errors.marketing_advert_id" />
</div>

                    <StaffFormActions :cancel-href="index()" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
