<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, create, show, edit } from '@/routes/staff/content/blogs';
import type { Paginated } from '@/types/staff';

defineProps<{
    blogs: Paginated<Record<string, unknown>>;
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
        <Head title="Blogs" />

        <StaffPageHeader
            title="Blogs"
            description="Manage blogs."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="blogs"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
