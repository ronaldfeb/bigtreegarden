<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index } from '@/routes/staff/commerce/subscriptions';
import type { Paginated } from '@/types/staff';

const props = defineProps<{
    subscriptions: Paginated<Record<string, unknown>>;
    filters: { status: string | null };
}>();

const columns = [
    { key: 'user_name', label: 'User' },
    { key: 'user_email', label: 'Email' },
    { key: 'package_name', label: 'Package' },
    { key: 'status', label: 'Status' },
    { key: 'next_billing_at', label: 'Next billing' },
    { key: 'activated_at', label: 'Activated' },
];

const statusOptions = ['pending', 'active', 'cancelled', 'expired'];

function applyStatusFilter(event: Event): void {
    const value = (event.target as HTMLSelectElement).value;

    router.get(
        index().url,
        { status: value || undefined },
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <StaffLayout>
        <Head title="Subscriptions" />

        <StaffPageHeader
            title="Subscriptions"
            description="Review user subscriptions and billing schedules."
        />

        <div class="flex flex-wrap gap-3">
            <label class="flex items-center gap-2 text-muted-foreground text-sm">
                Status
                <select
                    :value="props.filters.status ?? ''"
                    class="h-9 rounded-md border border-input bg-background px-3 text-foreground text-sm"
                    @change="applyStatusFilter"
                >
                    <option value="">All</option>
                    <option v-for="option in statusOptions" :key="option" :value="option" class="capitalize">
                        {{ option }}
                    </option>
                </select>
            </label>
        </div>

        <StaffDataTable
            :columns="columns"
            :paginator="subscriptions"
            empty-title="No subscriptions found"
            empty-description="User subscriptions will appear here."
        />
    </StaffLayout>
</template>
