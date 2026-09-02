<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import RichTextContent from '@/components/RichTextContent.vue';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { toDatetimeLocalInput } from '@/lib/utils';
import { create, show, edit } from '@/routes/staff/content/help-center-articles';
import type { Paginated, StaffTableColumn } from '@/types/staff';

type HelpCenterArticleRow = Record<string, unknown> & {
    title?: string;
    excerpt?: string | null;
    status?: string;
    published_at?: string | null;
    topic?: { name?: string } | null;
};

defineProps<{
    articles: Paginated<HelpCenterArticleRow>;
}>();

const columns: StaffTableColumn<HelpCenterArticleRow>[] = [
    {
        key: 'title',
        label: 'Title',
    },
    {
        key: 'topic',
        label: 'Topic',
        format: (row) => row.topic?.name ?? null,
    },
    {
        key: 'excerpt',
        label: 'Excerpt',
        class: 'max-w-md',
    },
    {
        key: 'status',
        label: 'Status',
    },
    {
        key: 'published_at',
        label: 'Published',
        format: (row) => toDatetimeLocalInput(row.published_at)?.replace('T', ' ') || null,
    },
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
        >
            <template #cell-excerpt="{ row }">
                <RichTextContent
                    v-if="row.excerpt"
                    :content="row.excerpt"
                    class="line-clamp-2 text-muted-foreground text-sm [&_*]:inline [&_*]:text-sm"
                />
                <span v-else class="text-muted-foreground">—</span>
            </template>
        </StaffDataTable>
    </StaffLayout>
</template>
