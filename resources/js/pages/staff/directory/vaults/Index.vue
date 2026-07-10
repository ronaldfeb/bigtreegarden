<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import type { Paginated } from '@/types/staff';

defineProps<{
    vaults: Paginated<Record<string, unknown>>;
}>();

const columns = [
    { key: 'person_of_interest_name', label: 'Person of interest' },
    { key: 'status', label: 'Status' },
    { key: 'beneficiaries_count', label: 'Beneficiaries' },
    { key: 'media_count', label: 'Media' },
    { key: 'posts_count', label: 'Posts' },
    { key: 'storage_used_mb', label: 'Storage used (MB)' },
    { key: 'released_at', label: 'Released' },
];
</script>

<template>
    <StaffLayout>
        <Head title="Vaults" />

        <StaffPageHeader
            title="Vaults"
            description="Read-only oversight of vault usage. Vault contents are never accessible to staff."
        />

        <StaffDataTable
            :columns="columns"
            :paginator="vaults"
            empty-title="No vaults found"
            empty-description="Vaults created by users will appear here."
        />
    </StaffLayout>
</template>
