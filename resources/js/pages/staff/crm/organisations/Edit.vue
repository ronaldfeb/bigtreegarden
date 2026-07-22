<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CrmOrganisationForm from '@/components/staff/crm/CrmOrganisationForm.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { show, update } from '@/routes/staff/crm/organisations';

const props = defineProps<{
    organisation: Record<string, any>;
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
        <Head title="Edit organisation" />

        <StaffPageHeader :title="`Edit ${props.organisation.name}`" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <CrmOrganisationForm
                    :action="update.form(props.organisation.id)"
                    :cancel-href="show(props.organisation.id)"
                    :organisation="props.organisation"
                    :types="types"
                    :relationship-kinds="relationshipKinds"
                    :partner-stages="partnerStages"
                    :relationship-statuses="relationshipStatuses"
                    :staff-users="staffUsers"
                    :service-providers="serviceProviders"
                    submit-label="Save changes"
                />
            </CardContent>
        </Card>
    </StaffLayout>
</template>
