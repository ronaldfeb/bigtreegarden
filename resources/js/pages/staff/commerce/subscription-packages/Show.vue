<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { formatCentsAsRand } from '@/lib/utils';
import { destroy, edit, index } from '@/routes/staff/commerce/subscription-packages';

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
        <Head title="Subscription package" />

        <StaffPageHeader
            :title="String(props.package.title ?? props.package.name ?? props.package.client_name ?? 'Subscription package')"
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

        <Card v-if="Array.isArray(props.package.features)">
            <CardHeader><CardTitle>Features</CardTitle></CardHeader>
            <CardContent>
                <p v-if="props.package.features.length === 0" class="text-muted-foreground text-sm">
                    No features listed for this package.
                </p>
                <ul v-else class="space-y-2" role="list">
                    <li
                        v-for="feature in props.package.features"
                        :key="feature.id"
                        class="rounded-lg border border-border p-3"
                    >
                        <p class="font-medium text-sm">{{ feature.label }}</p>
                        <p v-if="feature.description" class="mt-1 text-muted-foreground text-sm">{{ feature.description }}</p>
                        <p class="mt-1 text-muted-foreground text-xs">
                            {{ feature.is_included ? 'Included' : 'Not included' }}
                        </p>
                    </li>
                </ul>
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
