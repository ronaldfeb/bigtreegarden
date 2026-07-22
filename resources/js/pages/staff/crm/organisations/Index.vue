<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, edit, show } from '@/routes/staff/crm/organisations';
import type { Paginated } from '@/types/staff';

defineProps<{
    organisations: Paginated<Record<string, any>>;
}>();

function humanize(value: unknown): string {
    if (!value) {
        return '—';
    }

    return String(value)
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'type', label: 'Type', format: (row: Record<string, any>) => humanize(row.type) },
    { key: 'relationship_kind', label: 'Relationship', format: (row: Record<string, any>) => humanize(row.relationship_kind) },
    { key: 'relationship_status', label: 'Status', format: (row: Record<string, any>) => humanize(row.relationship_status) },
    { key: 'relationship_owner', label: 'Owner', format: (row: Record<string, any>) => row.relationship_owner?.user?.name ?? '—' },
    { key: 'contacts_count', label: 'Contacts' },
];
</script>

<template>
    <StaffLayout>
        <Head title="CRM organisations" />

        <StaffPageHeader
            title="Organisations"
            description="Strategic partners, fulfilment partners, and institutional stakeholders."
            :create-href="create()"
            create-label="New organisation"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="organisations"
            empty-title="No organisations yet"
            empty-description="Add funeral parlours, churches, museums, insurers and other stakeholders."
            :row-actions="[
                { label: 'View', href: (row) => show(row.id) },
                { label: 'Edit', href: (row) => edit(row.id) },
            ]"
        />
    </StaffLayout>
</template>
