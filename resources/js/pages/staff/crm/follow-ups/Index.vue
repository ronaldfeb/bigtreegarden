<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Badge } from '@/components/ui/badge';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index } from '@/routes/staff/crm/follow-ups';
import type { Paginated } from '@/types/staff';

type Option = { value: string; label: string };

const props = defineProps<{
    followUps: Paginated<Record<string, any>>;
    filters: { status: string; type: string; assigned_staff_user_id: string };
    types: Option[];
    statuses: Option[];
    staffUsers: Array<{ id: string; name: string }>;
}>();

const selectClass = 'border-input h-10 rounded-[var(--radius)] border bg-transparent px-3 text-sm';

const form = reactive({
    status: props.filters.status ?? 'pending',
    type: props.filters.type ?? '',
    assigned_staff_user_id: props.filters.assigned_staff_user_id ?? '',
});

watch(form, (value) => {
    router.get(index().url, { ...value }, { preserveState: true, preserveScroll: true, replace: true });
});

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'subject_label', label: 'Linked to' },
    { key: 'type_label', label: 'Type' },
    { key: 'due_at', label: 'Due', format: (row: Record<string, any>) => formatDate(row.due_at) },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <StaffLayout>
        <Head title="CRM follow-ups" />

        <StaffPageHeader
            title="Follow-ups"
            description="Scheduled follow-ups, renewals, anniversaries and review reminders."
        />

        <div class="flex flex-wrap gap-3">
            <select v-model="form.status" :class="selectClass">
                <option value="pending">Pending</option>
                <option value="all">All statuses</option>
                <option v-for="option in statuses" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
            <select v-model="form.type" :class="selectClass">
                <option value="">All types</option>
                <option v-for="option in types" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
            <select v-model="form.assigned_staff_user_id" :class="selectClass">
                <option value="">All assignees</option>
                <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
            </select>
        </div>

        <StaffDataTable
            :columns="columns"
            :paginator="followUps"
            empty-title="No follow-ups"
            empty-description="Nothing matches the current filters."
        >
            <template #cell-title="{ row }">
                <Link v-if="row.subject_href" :href="row.subject_href" class="font-medium text-brand hover:underline">
                    {{ row.title }}
                </Link>
                <span v-else class="font-medium">{{ row.title }}</span>
            </template>
            <template #cell-status="{ row }">
                <Badge v-if="row.is_overdue" variant="destructive">Overdue</Badge>
                <Badge v-else variant="outline" class="capitalize">{{ row.status }}</Badge>
            </template>
        </StaffDataTable>
    </StaffLayout>
</template>
