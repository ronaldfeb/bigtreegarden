<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, show, edit } from '@/routes/staff/directory/users';
import type { Paginated } from '@/types/staff';

defineProps<{
    users: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "name",
            "label": "Name"
        },
        {
            "key": "email",
            "label": "Email"
        },
        {
            "key": "phone",
            "label": "Phone"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Users" />

        <StaffPageHeader
            title="Users"
            description="Manage users."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="users"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
