<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, create, show, edit } from '@/routes/staff/commerce/subscription-packages';
import type { Paginated } from '@/types/staff';

defineProps<{
    packages: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "name",
            "label": "Name"
        },
        {
            "key": "price_cents",
            "label": "Price (cents)"
        },
        {
            "key": "billing_interval",
            "label": "Billing"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Subscription packages" />

        <StaffPageHeader
            title="Subscription packages"
            description="Manage subscription packages."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="packages"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
