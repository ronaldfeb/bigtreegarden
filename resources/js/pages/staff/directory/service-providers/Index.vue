<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, show, edit } from '@/routes/staff/directory/service-providers';
import type { Paginated } from '@/types/staff';

defineProps<{
    serviceProviders: Paginated<Record<string, unknown>>;
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
            "key": "status",
            "label": "Status"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Service providers" />

        <StaffPageHeader
            title="Service providers"
            description="Manage service providers."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="serviceProviders"
            :row-actions="[{ label: 'View', href: (row) => show(String(row.slug)) }, { label: 'Edit', href: (row) => edit(String(row.slug)) }]"
        />
    </StaffLayout>
</template>
