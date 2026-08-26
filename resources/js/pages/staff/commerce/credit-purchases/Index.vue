<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, show } from '@/routes/staff/commerce/credit-purchases';
import type { Paginated } from '@/types/staff';

const props = defineProps<{
    purchases: Paginated<Record<string, unknown>>;
    filters: { status: string };
    statuses: Array<{ value: string; label: string }>;
}>();

function filterByStatus(event: Event): void {
    const value = (event.target as HTMLSelectElement).value;
    router.get(index.url({ query: { status: value || undefined } }), {}, { preserveState: true });
}
</script>

<template>
    <StaffLayout>
        <Head title="Credit purchases" />
        <StaffPageHeader
            title="Provider credit purchases"
            description="Review bank transfers and monitor credit pack purchases."
        />
        <div class="mb-4">
            <select
                class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                :value="filters.status"
                @change="filterByStatus"
            >
                <option v-for="status in statuses" :key="status.value" :value="status.value">
                    {{ status.label }}
                </option>
            </select>
        </div>
        <StaffDataTable
            :columns="[
                { key: 'package_name', label: 'Package' },
                { key: 'payment_method', label: 'Method' },
                { key: 'status', label: 'Status' },
                { key: 'payment_reference', label: 'Reference' },
            ]"
            :paginator="purchases"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }]"
        />
    </StaffLayout>
</template>
