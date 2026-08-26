<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { formatCentsAsRand } from '@/lib/utils';
import { create, edit, show } from '@/routes/staff/commerce/credit-packages';
import type { Paginated, StaffTableColumn } from '@/types/staff';

defineProps<{
    packages: Paginated<Record<string, unknown>>;
}>();

const columns: StaffTableColumn<Record<string, unknown>>[] = [
    { key: 'name', label: 'Name' },
    { key: 'page_count', label: 'Pages' },
    {
        key: 'price_cents',
        label: 'Price',
        format: (row) => formatCentsAsRand(row.price_cents as number, String(row.currency ?? 'ZAR')),
    },
    { key: 'is_active', label: 'Active' },
];
</script>

<template>
    <StaffLayout>
        <Head title="Credit packages" />

        <StaffPageHeader
            title="Provider credit packages"
            description="Bulk memorial page packs sold to funeral service providers."
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
