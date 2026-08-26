<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, show, edit } from '@/routes/staff/marketing/adverts';
import type { Paginated } from '@/types/staff';

defineProps<{
    adverts: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "client_name",
            "label": "Client"
        },
        {
            "key": "campaign_name",
            "label": "Campaign"
        },
        {
            "key": "status",
            "label": "Status"
        },
        {
            "key": "visits_count",
            "label": "Visits"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Adverts" />

        <StaffPageHeader
            title="Adverts"
            description="Manage adverts."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="adverts"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
