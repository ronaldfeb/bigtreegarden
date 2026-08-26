<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, show, edit } from '@/routes/staff/content/pamphlet-backgrounds';
import type { Paginated } from '@/types/staff';

defineProps<{
    backgrounds: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "name",
            "label": "Name"
        },
        {
            "key": "collection_id",
            "label": "Collection"
        },
        {
            "key": "is_active",
            "label": "Active"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Pamphlet backgrounds" />

        <StaffPageHeader
            title="Pamphlet backgrounds"
            description="Manage pamphlet backgrounds."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="backgrounds"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
