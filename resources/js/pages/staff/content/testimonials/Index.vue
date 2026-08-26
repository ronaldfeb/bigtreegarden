<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { create, show, edit } from '@/routes/staff/content/testimonials';
import type { Paginated } from '@/types/staff';

defineProps<{
    testimonials: Paginated<Record<string, unknown>>;
}>();

const columns = [
        {
            "key": "name",
            "label": "Name"
        },
        {
            "key": "rating",
            "label": "Rating"
        },
        {
            "key": "status",
            "label": "Status"
        }
    ];
</script>

<template>
    <StaffLayout>
        <Head title="Testimonials" />

        <StaffPageHeader
            title="Testimonials"
            description="Manage testimonials."
            :create-href="create()" create-label="Create"
        />

        <StaffDataTable
            :columns="columns"
            :paginator="testimonials"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }, { label: 'Edit', href: (row) => edit(row.id) }]"
        />
    </StaffLayout>
</template>
