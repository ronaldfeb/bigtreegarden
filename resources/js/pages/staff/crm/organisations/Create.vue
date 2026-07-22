<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CrmOrganisationForm from '@/components/staff/crm/CrmOrganisationForm.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, store } from '@/routes/staff/crm/organisations';

defineProps<{
    types: Array<{ value: string; label: string }>;
    relationshipKinds: Array<{ value: string; label: string }>;
    partnerStages: Array<{ value: string; label: string }>;
    relationshipStatuses: Array<{ value: string; label: string }>;
    staffUsers: Array<{ id: string; name: string }>;
    serviceProviders: Array<{ id: string; name: string }>;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="New organisation" />

        <StaffPageHeader title="New organisation" description="Create a stakeholder relationship record." />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <CrmOrganisationForm
                    :action="store.form()"
                    :cancel-href="index()"
                    :types="types"
                    :relationship-kinds="relationshipKinds"
                    :partner-stages="partnerStages"
                    :relationship-statuses="relationshipStatuses"
                    :staff-users="staffUsers"
                    :service-providers="serviceProviders"
                    submit-label="Create organisation"
                />
            </CardContent>
        </Card>
    </StaffLayout>
</template>
