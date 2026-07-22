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
        journeys: Option[];
        lifecycleStages: Option[];
        organisations: Array<{ id: string; name: string }>;
        staffUsers: Array<{ id: string; name: string }>;
        users: Array<{ id: string; name: string; email: string }>;
        contact?: Record<string, any> | null;
        submitLabel?: string;
    }>(),
    {
        contact: null,
        submitLabel: 'Save',
    },
);

const selectClass = 'border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm';
</script>

<template>
    <Form v-bind="action" class="space-y-6" #default="{ errors, processing }">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2 sm:col-span-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" type="text" :default-value="contact?.name" required />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" name="email" type="email" :default-value="contact?.email" />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input id="phone" name="phone" type="text" :default-value="contact?.phone" />
                <InputError :message="errors.phone" />
            </div>

            <div class="grid gap-2">
                <Label for="role_title">Role / title</Label>
                <Input id="role_title" name="role_title" type="text" :default-value="contact?.role_title" />
                <InputError :message="errors.role_title" />
            </div>

            <div class="grid gap-2">
                <Label for="crm_organisation_id">Organisation</Label>
                <select id="crm_organisation_id" name="crm_organisation_id" :class="selectClass" :value="contact?.crm_organisation_id ?? ''">
                    <option value="">None (individual)</option>
                    <option v-for="organisation in organisations" :key="organisation.id" :value="organisation.id">{{ organisation.name }}</option>
                </select>
                <InputError :message="errors.crm_organisation_id" />
            </div>

            <div class="grid gap-2">
                <Label for="lifecycle_stage">Lifecycle stage</Label>
                <select id="lifecycle_stage" name="lifecycle_stage" :class="selectClass" :value="contact?.lifecycle_stage" required>
                    <option v-for="option in lifecycleStages" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
                <InputError :message="errors.lifecycle_stage" />
            </div>

            <div class="grid gap-2">
                <Label for="journey">Customer journey</Label>
                <select id="journey" name="journey" :class="selectClass" :value="contact?.journey ?? ''">
                    <option value="">Not set</option>
                    <option v-for="option in journeys" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
                <InputError :message="errors.journey" />
            </div>

            <div class="grid gap-2">
                <Label for="relationship_owner_staff_user_id">Relationship owner</Label>
                <select id="relationship_owner_staff_user_id" name="relationship_owner_staff_user_id" :class="selectClass" :value="contact?.relationship_owner_staff_user_id ?? ''">
                    <option value="">Unassigned</option>
                    <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
                </select>
                <InputError :message="errors.relationship_owner_staff_user_id" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="user_id">Linked platform account</Label>
                <select id="user_id" name="user_id" :class="selectClass" :value="contact?.user_id ?? ''">
                    <option value="">Not linked</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
                </select>
                <InputError :message="errors.user_id" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="notes">Notes</Label>
                <Textarea id="notes" name="notes" rows="3" :default-value="contact?.notes" />
                <InputError :message="errors.notes" />
            </div>
        </div>

        <StaffFormActions :cancel-href="cancelHref" :processing="processing" :submit-label="submitLabel" />
    </Form>
</template>
