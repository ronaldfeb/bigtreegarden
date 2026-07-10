<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { approve, reject } from '@/routes/messages';
import { index } from '@/routes/staff/directory/memorial-page-messages';
import type { Paginated } from '@/types/staff';

const props = defineProps<{
    messages: Paginated<Record<string, unknown>>;
    filters: { status: string | null; context: string | null };
}>();

const columns = [
    { key: 'memorial_page_title', label: 'Memorial page' },
    { key: 'person_of_interest_name', label: 'Person of interest' },
    { key: 'author_name', label: 'Author' },
    { key: 'context', label: 'Context' },
    { key: 'status', label: 'Status' },
    { key: 'is_gps_verified', label: 'GPS verified' },
    { key: 'created_at', label: 'Posted' },
];

const statusOptions = ['pending', 'approved', 'rejected'];
const contextOptions = ['live_day', 'flowers'];

function applyFilter(key: 'status' | 'context', event: Event): void {
    const value = (event.target as HTMLSelectElement).value;

    router.get(
        index().url,
        {
            status: (key === 'status' ? value : props.filters.status) || undefined,
            context: (key === 'context' ? value : props.filters.context) || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <StaffLayout>
        <Head title="Memorial page messages" />

        <StaffPageHeader
            title="Memorial page messages"
            description="Moderate live day and flower messages posted to memorial pages."
        />

        <div class="flex flex-wrap gap-3">
            <label class="flex items-center gap-2 text-muted-foreground text-sm">
                Status
                <select
                    :value="props.filters.status ?? ''"
                    class="h-9 rounded-md border border-input bg-background px-3 text-foreground text-sm"
                    @change="applyFilter('status', $event)"
                >
                    <option value="">All</option>
                    <option v-for="option in statusOptions" :key="option" :value="option" class="capitalize">
                        {{ option }}
                    </option>
                </select>
            </label>

            <label class="flex items-center gap-2 text-muted-foreground text-sm">
                Context
                <select
                    :value="props.filters.context ?? ''"
                    class="h-9 rounded-md border border-input bg-background px-3 text-foreground text-sm"
                    @change="applyFilter('context', $event)"
                >
                    <option value="">All</option>
                    <option v-for="option in contextOptions" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </label>
        </div>

        <StaffDataTable
            :columns="columns"
            :paginator="messages"
            empty-title="No messages found"
            empty-description="Messages posted to memorial pages will appear here."
            :row-actions="[
                { label: 'Approve', href: (row) => approve(String(row.id)) },
                { label: 'Reject', href: (row) => reject(String(row.id)) },
            ]"
        />
    </StaffLayout>
</template>
