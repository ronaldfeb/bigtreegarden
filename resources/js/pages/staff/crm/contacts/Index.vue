<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, edit, show } from '@/routes/staff/crm/contacts';
import type { Paginated } from '@/types/staff';

defineProps<{
    contacts: Paginated<Record<string, any>>;
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
    { key: 'organisation', label: 'Organisation', format: (row: Record<string, any>) => row.organisation?.name ?? 'Individual' },
    { key: 'lifecycle_stage', label: 'Lifecycle', format: (row: Record<string, any>) => humanize(row.lifecycle_stage) },
    { key: 'journey', label: 'Journey', format: (row: Record<string, any>) => humanize(row.journey) },
    { key: 'relationship_owner', label: 'Owner', format: (row: Record<string, any>) => row.relationship_owner?.user?.name ?? '—' },
];
</script>

<template>
    <StaffLayout>
        <Head title="CRM contacts" />

        <StaffPageHeader
            title="Contacts"
            description="People across every customer journey and stakeholder relationship."
            :create-href="create()"
            create-label="New contact"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="contacts"
            empty-title="No contacts yet"
            empty-description="Add contacts or convert marketing leads into CRM contacts."
            :row-actions="[
                { label: 'View', href: (row) => show(row.id) },
                { label: 'Edit', href: (row) => edit(row.id) },
            ]"
        />
    </StaffLayout>
</template>
