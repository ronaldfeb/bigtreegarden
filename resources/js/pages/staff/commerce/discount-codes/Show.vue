<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { destroy, index } from '@/routes/staff/commerce/discount-codes';

const props = defineProps<{
    code: {
        id: string;
        formatted_code: string;
        discount_type: string;
        value_label: string;
        target_label: string;
        status: string;
        starts_at: string | null;
        ends_at: string | null;
        reserved_at: string | null;
        used_at: string | null;
        used_by: { id: string; name: string; email: string } | null;
        created_by: { id: string; name: string; email: string } | null;
        created_at: string | null;
    };
}>();

const copied = ref(false);

async function copyCode(): Promise<void> {
    try {
        await navigator.clipboard.writeText(props.code.formatted_code);
        copied.value = true;
        window.setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        copied.value = false;
    }
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString();
}
</script>

<template>
    <StaffLayout>
        <Head title="Discount code" />
        <StaffPageHeader :title="props.code.formatted_code" />

        <Card>
            <CardHeader class="flex flex-row items-center justify-between gap-4">
                <CardTitle>Code</CardTitle>
                <Button type="button" variant="outline" size="sm" @click="copyCode">
                    {{ copied ? 'Copied' : 'Copy' }}
                </Button>
            </CardHeader>
            <CardContent>
                <p class="font-mono text-2xl tracking-wider">{{ props.code.formatted_code }}</p>
            </CardContent>
        </Card>

        <Card class="mt-4">
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Discount</dt>
                        <dd class="mt-1 text-sm">{{ props.code.value_label }} ({{ props.code.discount_type }})</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Target</dt>
                        <dd class="mt-1 text-sm">{{ props.code.target_label }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Status</dt>
                        <dd class="mt-1 text-sm capitalize">{{ props.code.status }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Window</dt>
                        <dd class="mt-1 text-sm">
                            {{ formatDate(props.code.starts_at) }} → {{ formatDate(props.code.ends_at) }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Reserved at</dt>
                        <dd class="mt-1 text-sm">{{ formatDate(props.code.reserved_at) }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Used at</dt>
                        <dd class="mt-1 text-sm">{{ formatDate(props.code.used_at) }}</dd>
                    </div>
                    <div v-if="props.code.used_by" class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Used by</dt>
                        <dd class="mt-1 text-sm">{{ props.code.used_by.name }} ({{ props.code.used_by.email }})</dd>
                    </div>
                    <div v-if="props.code.created_by" class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Created by</dt>
                        <dd class="mt-1 text-sm">
                            {{ props.code.created_by.name }} ({{ props.code.created_by.email }})
                        </dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Created at</dt>
                        <dd class="mt-1 text-sm">{{ formatDate(props.code.created_at) }}</dd>
                    </div>
                </dl>
            </CardContent>
        </Card>

        <div class="mt-4 flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to list</Link>
            </Button>
            <Form v-if="!props.code.used_at" v-bind="destroy.form(props.code.id)">
                <Button type="submit" variant="destructive">Void code</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
