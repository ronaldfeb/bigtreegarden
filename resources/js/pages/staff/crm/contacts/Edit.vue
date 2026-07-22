<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CrmContactForm from '@/components/staff/crm/CrmContactForm.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { show, update } from '@/routes/staff/crm/contacts';

const props = defineProps<{
    contact: Record<string, any>;
    journeys: Array<{ value: string; label: string }>;
    lifecycleStages: Array<{ value: string; label: string }>;
    organisations: Array<{ id: string; name: string }>;
    staffUsers: Array<{ id: string; name: string }>;
    users: Array<{ id: string; name: string; email: string }>;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit contact" />

        <StaffPageHeader :title="`Edit ${props.contact.name}`" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <CrmContactForm
                    :action="update.form(props.contact.id)"
                    :cancel-href="show(props.contact.id)"
                    :contact="props.contact"
                    :journeys="journeys"
                    :lifecycle-stages="lifecycleStages"
                    :organisations="organisations"
                    :staff-users="staffUsers"
                    :users="users"
                    submit-label="Save changes"
                />
            </CardContent>
        </Card>
    </StaffLayout>
</template>
