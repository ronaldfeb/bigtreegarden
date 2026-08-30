<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, show } from '@/routes/staff/commerce/discount-codes';
import type { Paginated, StaffTableColumn } from '@/types/staff';

defineProps<{
    codes: Paginated<Record<string, unknown>>;
}>();

const columns: StaffTableColumn<Record<string, unknown>>[] = [
    {
        key: 'formatted_code',
        label: 'Code',
    },
    {
        key: 'value_label',
        label: 'Discount',
    },
    {
        key: 'target_label',
        label: 'Target',
    },
    {
        key: 'status',
        label: 'Status',
        format: (row) => String(row.status ?? '').replace(/^\w/, (c) => c.toUpperCase()),
    },
];
</script>

<template>
    <StaffLayout>
        <Head title="Discount codes" />

        <StaffPageHeader
            title="Discount codes"
            description="Generate single-use discount codes for packages and credits."
            :create-href="create()"
            create-label="Generate code"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="codes"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }]"
        />
    </StaffLayout>
</template>
