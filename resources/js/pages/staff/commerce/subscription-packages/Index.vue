<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { formatCentsAsRand } from '@/lib/utils';
import { create, edit, index, show } from '@/routes/staff/commerce/subscription-packages';
import type { Paginated, StaffTableColumn } from '@/types/staff';

defineProps<{
    packages: Paginated<Record<string, unknown>>;
}>();

const columns: StaffTableColumn<Record<string, unknown>>[] = [
    {
        key: 'name',
        label: 'Name',
    },
    {
        key: 'price_cents',
        label: 'Price',
        format: (row) => formatCentsAsRand(row.price_cents as number, String(row.currency ?? 'ZAR')),
    },
    {
        key: 'billing_interval',
        label: 'Billing',
    },
];
</script>

<template>
    <StaffLayout>
        <Head title="Subscription packages" />

        <StaffPageHeader
            title="Subscription packages"
            description="Manage subscription packages."
            :create-href="create()"
            create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="packages"
            :row-actions="[
                { label: 'View', href: (row) => show(row.id) },
                { label: 'Edit', href: (row) => edit(row.id) },
            ]"
        />
    </StaffLayout>
</template>
