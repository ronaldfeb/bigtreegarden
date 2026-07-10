<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, create, show, edit } from '@/routes/staff/content/pamphlet-background-collections';
import type { Paginated } from '@/types/staff';

defineProps<{
    collections: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "name",
            "label": "Name"
        },
        {
            "key": "sort_order",
            "label": "Order"
        },
        {
            "key": "is_active",
            "label": "Active"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Pamphlet background collections" />

        <StaffPageHeader
            title="Pamphlet background collections"
            description="Manage pamphlet background collections."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="collections"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
