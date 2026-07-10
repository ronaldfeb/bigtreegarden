<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, create, show, edit } from '@/routes/staff/directory/persons-of-interest';
import type { Paginated } from '@/types/staff';

defineProps<{
    personsOfInterest: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "display_name",
            "label": "Name"
        },
        {
            "key": "date_of_birth",
            "label": "Born"
        },
        {
            "key": "status",
            "label": "Status"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Persons of interest" />

        <StaffPageHeader
            title="Persons of interest"
            description="Manage persons of interest."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="personsOfInterest"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
