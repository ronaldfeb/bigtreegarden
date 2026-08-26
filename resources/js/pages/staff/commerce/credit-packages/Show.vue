<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { formatCentsAsRand } from '@/lib/utils';
import { destroy, edit, index } from '@/routes/staff/commerce/credit-packages';

const props = defineProps<{
    package: Record<string, any>;
}>();

const moneyKeys = new Set(['price_cents', 'amount_cents']);

function fieldLabel(key: string): string {
    if (key === 'price_cents') {
        return 'price';
    }

    if (key === 'amount_cents') {
        return 'amount';
    }

    return key;
}

function fieldValue(key: string, value: unknown): string {
    if (moneyKeys.has(key)) {
        return formatCentsAsRand(value as number, String(props.package.currency ?? 'ZAR'));
    }

    return String(value);
}
</script>

<template>
    <StaffLayout>
        <Head title="Credit package" />
        <StaffPageHeader
            :title="String(props.package.name ?? 'Credit package')"
            :actions="[{ label: 'Edit', href: edit(props.package.id) }]"
        />
        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2">
                    <template v-for="(value, key) in props.package" :key="key">
                        <div
                            v-if="value !== null && typeof value !== 'object'"
                            class="rounded-lg border border-border p-3"
                        >
                            <dt class="text-muted-foreground text-xs uppercase">{{ fieldLabel(String(key)) }}</dt>
                            <dd class="mt-1 break-words text-sm">{{ fieldValue(String(key), value) }}</dd>
                        </div>
                    </template>
                </dl>
            </CardContent>
        </Card>
        <div class="flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to list</Link>
            </Button>
            <Form v-bind="destroy.form(props.package.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
