<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CrmContactForm from '@/components/staff/crm/CrmContactForm.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, store } from '@/routes/staff/crm/contacts';

defineProps<{
    journeys: Array<{ value: string; label: string }>;
    lifecycleStages: Array<{ value: string; label: string }>;
    organisations: Array<{ id: string; name: string }>;
    staffUsers: Array<{ id: string; name: string }>;
    users: Array<{ id: string; name: string; email: string }>;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="New contact" />

        <StaffPageHeader title="New contact" description="Create a CRM contact record." />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <CrmContactForm
                    :action="store.form()"
                    :cancel-href="index()"
                    :journeys="journeys"
                    :lifecycle-stages="lifecycleStages"
                    :organisations="organisations"
                    :staff-users="staffUsers"
                    :users="users"
                    submit-label="Create contact"
                />
            </CardContent>
        </Card>
    </StaffLayout>
</template>
