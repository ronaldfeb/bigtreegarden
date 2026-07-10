<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, show } from '@/routes/staff/commerce/transactions';
import type { Paginated } from '@/types/staff';

defineProps<{
    transactions: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "reference",
            "label": "Reference"
        },
        {
            "key": "amount_cents",
            "label": "Amount (cents)"
        },
        {
            "key": "status",
            "label": "Status"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Transactions" />

        <StaffPageHeader
            title="Transactions"
            description="Manage transactions."
            
        />

        <StaffDataTable
            :columns="columns"
            :paginator="transactions"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }]"
        />
    </StaffLayout>
</template>
