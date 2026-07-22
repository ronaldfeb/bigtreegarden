<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type Option = { value: string; label: string };

withDefaults(
    defineProps<{
        action: Record<string, unknown>;
        cancelHref: NonNullable<InertiaLinkProps['href']>;
        types: Option[];
        relationshipKinds: Option[];
        partnerStages: Option[];
        relationshipStatuses: Option[];
        staffUsers: Array<{ id: string; name: string }>;
        serviceProviders: Array<{ id: string; name: string }>;
        organisation?: Record<string, any> | null;
        submitLabel?: string;
    }>(),
    {
        organisation: null,
        submitLabel: 'Save',
    },
);

const selectClass =
    'border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm';
</script>

<template>
    <Form v-bind="action" class="space-y-6" #default="{ errors, processing }">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2 sm:col-span-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" type="text" :default-value="organisation?.name" required />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="type">Type</Label>
                <select id="type" name="type" :class="selectClass" :value="organisation?.type" required>
                    <option v-for="option in types" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
                <InputError :message="errors.type" />
            </div>

            <div class="grid gap-2">
                <Label for="relationship_kind">Relationship kind</Label>
                <select id="relationship_kind" name="relationship_kind" :class="selectClass" :value="organisation?.relationship_kind" required>
                    <option v-for="option in relationshipKinds" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
                <InputError :message="errors.relationship_kind" />
            </div>

            <div class="grid gap-2">
                <Label for="relationship_status">Relationship status</Label>
                <select id="relationship_status" name="relationship_status" :class="selectClass" :value="organisation?.relationship_status" required>
                    <option v-for="option in relationshipStatuses" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
                <InputError :message="errors.relationship_status" />
            </div>

            <div class="grid gap-2">
                <Label for="partner_stage">Partner stage</Label>
                <select id="partner_stage" name="partner_stage" :class="selectClass" :value="organisation?.partner_stage ?? ''">
                    <option value="">Not a staged partner</option>
                    <option v-for="option in partnerStages" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
                <InputError :message="errors.partner_stage" />
            </div>

            <div class="grid gap-2">
                <Label for="relationship_owner_staff_user_id">Relationship owner</Label>
                <select id="relationship_owner_staff_user_id" name="relationship_owner_staff_user_id" :class="selectClass" :value="organisation?.relationship_owner_staff_user_id ?? ''">
                    <option value="">Unassigned</option>
                    <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
                </select>
                <InputError :message="errors.relationship_owner_staff_user_id" />
            </div>

            <div class="grid gap-2">
                <Label for="service_provider_id">Linked service provider</Label>
                <select id="service_provider_id" name="service_provider_id" :class="selectClass" :value="organisation?.service_provider_id ?? ''">
                    <option value="">None</option>
                    <option v-for="provider in serviceProviders" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
                </select>
                <InputError :message="errors.service_provider_id" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" name="email" type="email" :default-value="organisation?.email" />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input id="phone" name="phone" type="text" :default-value="organisation?.phone" />
                <InputError :message="errors.phone" />
            </div>

            <div class="grid gap-2">
                <Label for="website_url">Website</Label>
                <Input id="website_url" name="website_url" type="url" :default-value="organisation?.website_url" />
                <InputError :message="errors.website_url" />
            </div>

            <div class="grid gap-2">
                <Label for="geographic_coverage">Geographic coverage</Label>
                <Input id="geographic_coverage" name="geographic_coverage" type="text" :default-value="organisation?.geographic_coverage" />
                <InputError :message="errors.geographic_coverage" />
            </div>

            <div class="grid gap-2">
                <Label for="next_review_at">Next review date</Label>
                <Input id="next_review_at" name="next_review_at" type="date" :default-value="organisation?.next_review_at?.slice(0, 10)" />
                <InputError :message="errors.next_review_at" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="services_offered">Services offered</Label>
                <Textarea id="services_offered" name="services_offered" rows="2" :default-value="organisation?.services_offered" />
                <InputError :message="errors.services_offered" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="notes">Notes</Label>
                <Textarea id="notes" name="notes" rows="3" :default-value="organisation?.notes" />
                <InputError :message="errors.notes" />
            </div>
        </div>

        <StaffFormActions :cancel-href="cancelHref" :processing="processing" :submit-label="submitLabel" />
    </Form>
</template>
