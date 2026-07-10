<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, create, show, edit } from '@/routes/staff/content/help-center-articles';
import type { Paginated } from '@/types/staff';

defineProps<{
    articles: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "title",
            "label": "Title"
        },
        {
            "key": "status",
            "label": "Status"
        },
        {
            "key": "published_at",
            "label": "Published"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Help center articles" />

        <StaffPageHeader
            title="Help center articles"
            description="Manage help center articles."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="articles"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
