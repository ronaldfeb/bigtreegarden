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
import { index, show, store, update } from '@/routes/staff/marketing/adverts';
defineProps<{ advert: Record<string, unknown> }>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit advert" />

        <StaffPageHeader title="Edit advert" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(advert.id)"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="client_name">Client name</Label>
    <Input id="client_name" name="client_name" type="text" :default-value="advert.client_name" required />
    <InputError :message="errors.client_name" />
</div>

                <div class="grid gap-2">
    <Label for="campaign_name">Campaign name</Label>
    <Input id="campaign_name" name="campaign_name" type="text" :default-value="advert.campaign_name"  />
    <InputError :message="errors.campaign_name" />
</div>

                <div class="grid gap-2">
    <Label for="destination_url">Destination URL</Label>
    <Input id="destination_url" name="destination_url" type="url" :default-value="advert.destination_url" required />
    <InputError :message="errors.destination_url" />
</div>

                <div class="grid gap-2">
    <Label for="utm_source">UTM source</Label>
    <Input id="utm_source" name="utm_source" type="text" :default-value="advert.utm_source" required />
    <InputError :message="errors.utm_source" />
</div>

                <div class="grid gap-2">
    <Label for="utm_medium">UTM medium</Label>
    <Input id="utm_medium" name="utm_medium" type="text" :default-value="advert.utm_medium"  />
    <InputError :message="errors.utm_medium" />
</div>

                <div class="grid gap-2">
    <Label for="utm_campaign">UTM campaign</Label>
    <Input id="utm_campaign" name="utm_campaign" type="text" :default-value="advert.utm_campaign" required />
    <InputError :message="errors.utm_campaign" />
</div>

                <div class="grid gap-2">
    <Label for="utm_content">UTM content</Label>
    <Input id="utm_content" name="utm_content" type="text" :default-value="advert.utm_content"  />
    <InputError :message="errors.utm_content" />
</div>

                <div class="grid gap-2">
    <Label for="status">Status</Label>
    <select id="status" name="status" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="active">Active</option>
            <option value="paused">Paused</option>
            <option value="archived">Archived</option>
    </select>
    <InputError :message="errors.status" />
</div>

                <div class="grid gap-2">
    <Label for="starts_at">Starts at</Label>
    <Input id="starts_at" name="starts_at" type="datetime-local" :default-value="advert.starts_at"  />
    <InputError :message="errors.starts_at" />
</div>

                <div class="grid gap-2">
    <Label for="ends_at">Ends at</Label>
    <Input id="ends_at" name="ends_at" type="datetime-local" :default-value="advert.ends_at"  />
    <InputError :message="errors.ends_at" />
</div>

                    <StaffFormActions :cancel-href="show(advert.id)" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
