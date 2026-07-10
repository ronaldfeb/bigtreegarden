<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    checkoutUrl: string;
    payload: Record<string, string | number | null>;
    packageName: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Subscription checkout',
        href: '#',
    },
];
</script>

<template>
    <Head title="Subscription checkout" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-2xl p-4">
            <h1 class="mb-4 font-semibold text-2xl">Subscribe to {{ packageName }}</h1>
            <p class="mb-6 text-muted-foreground text-sm">
                You will be redirected to PayFast to set up your recurring subscription.
            </p>
            <form :action="checkoutUrl" method="post" class="rounded-xl border border-border p-4">
                <input
                    v-for="(value, key) in payload"
                    :key="key"
                    type="hidden"
                    :name="key"
                    :value="value ?? ''"
                />
                <Button type="submit">Proceed to PayFast</Button>
            </form>
        </div>
    </AppLayout>
</template>
