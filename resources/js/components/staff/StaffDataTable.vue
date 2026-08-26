<script setup lang="ts" generic="T extends Record<string, unknown>">
import { Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { Paginated, StaffTableColumn } from '@/types/staff';

type RowAction<T> = {
    label: string;
    href: (row: T) => NonNullable<InertiaLinkProps['href']>;
};

const props = withDefaults(
    defineProps<{
        columns: StaffTableColumn<T>[];
        paginator: Paginated<T>;
        rowKey?: string;
        emptyTitle?: string;
        emptyDescription?: string;
        rowActions?: RowAction<T>[];
    }>(),
    {
        rowKey: 'id',
        emptyTitle: 'No records found',
        emptyDescription: 'Create a new record to get started.',
        rowActions: () => [],
    },
);

const rows = computed(() => props.paginator.data);

function cellValue(row: T, column: StaffTableColumn<T>): string {
    const raw = column.format ? column.format(row) : row[column.key];

    if (raw === null || raw === undefined || raw === '') {
        return '—';
    }

    return String(raw);
}

function rowId(row: T): string {
    return String(row[props.rowKey]);
}
</script>

<template>
    <Card class="overflow-hidden py-0">
        <CardContent class="p-0">
            <div v-if="rows.length === 0" class="px-6 py-12 text-center">
                <p class="font-medium text-sm">{{ emptyTitle }}</p>
                <p class="mt-1 text-muted-foreground text-sm">{{ emptyDescription }}</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead class="border-border border-b bg-muted/40">
                        <tr>
                            <th
                                v-for="column in columns"
                                :key="column.key"
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                                :class="column.class"
                            >
                                {{ column.label }}
                            </th>
                            <th
                                v-if="rowActions.length > 0"
                                class="px-4 py-3 text-right font-medium text-muted-foreground"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in rows"
                            :key="rowId(row)"
                            class="border-border border-b last:border-b-0"
                        >
                            <td
                                v-for="column in columns"
                                :key="column.key"
                                class="px-4 py-3 align-middle"
                                :class="column.class"
                            >
                                <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                    <Badge
                                        v-if="column.key === 'status'"
                                        variant="outline"
                                        class="capitalize"
                                    >
                                        {{ cellValue(row, column) }}
                                    </Badge>
                                    <span v-else>{{ cellValue(row, column) }}</span>
                                </slot>
                            </td>
                            <td v-if="rowActions.length > 0" class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        v-for="action in rowActions"
                                        :key="action.label"
                                        variant="ghost"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="action.href(row)">
                                            {{ action.label }}
                                        </Link>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="paginator.last_page > 1"
                class="flex flex-col gap-3 border-border border-t px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-muted-foreground text-xs">
                    Showing {{ paginator.from ?? 0 }}–{{ paginator.to ?? 0 }} of
                    {{ paginator.total }}
                </p>
                <div class="flex flex-wrap gap-1">
                    <Button
                        v-for="link in paginator.links"
                        :key="`${link.label}-${link.url}`"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        :disabled="!link.url"
                        as-child
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                        >
                            <span v-html="link.label" />
                        </Link>
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
