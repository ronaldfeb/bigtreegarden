<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, show, edit } from '@/routes/staff/content/help-center-topics';
import type { Paginated } from '@/types/staff';

defineProps<{
    topics: Paginated<Record<string, unknown>>;
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
        <Head title="Help center topics" />

        <StaffPageHeader
            title="Help center topics"
            description="Manage help center topics."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="topics"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
