<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, create, show, edit } from '@/routes/staff/content/policies';
import type { Paginated } from '@/types/staff';

defineProps<{
    policies: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "title",
            "label": "Title"
        },
        {
            "key": "type",
            "label": "Type"
        },
        {
            "key": "version",
            "label": "Version"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Policies" />

        <StaffPageHeader
            title="Policies"
            description="Manage policies."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="policies"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
