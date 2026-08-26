<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, show, edit } from '@/routes/staff/marketing/leads';
import type { Paginated } from '@/types/staff';

defineProps<{
    leads: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "name",
            "label": "Name"
        },
        {
            "key": "email",
            "label": "Email"
        },
        {
            "key": "source",
            "label": "Source"
        },
        {
            "key": "status",
            "label": "Status"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Leads" />

        <StaffPageHeader
            title="Leads"
            description="Manage leads."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="leads"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
