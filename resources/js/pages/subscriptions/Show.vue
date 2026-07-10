<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { pricing } from '@/routes';
import { cancel } from '@/routes/subscriptions';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subscription: {
        id: string;
        status: string;
        package_name: string;
        billing_interval: string;
        price_cents: number;
        currency: string;
        next_billing_at: string | null;
        activated_at: string | null;
        cancelled_at: string | null;
    } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Subscription',
        href: '#',
    },
];

function formatPrice(cents: number, currency: string): string {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency,
        minimumFractionDigits: 0,
    }).format(cents / 100);
}

function cancelSubscription(): void {
    if (props.subscription === null) {
        return;
    }

    if (!confirm('Cancel your subscription? Vault access ends at the end of the paid period.')) {
        return;
    }

    router.delete(cancel(props.subscription.id).url);
}
</script>

<template>
    <Head title="Subscription" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-2xl space-y-6 p-4">
            <h1 class="font-semibold text-2xl">Your subscription</h1>

            <Card v-if="subscription">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>{{ subscription.package_name }}</CardTitle>
                        <Badge :variant="subscription.status === 'active' ? 'default' : 'secondary'">
                            {{ subscription.status }}
                        </Badge>
                    </div>
                    <CardDescription>
                        {{ formatPrice(subscription.price_cents, subscription.currency) }}
                        {{ subscription.billing_interval === 'annual' ? 'per year' : 'per month' }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p v-if="subscription.activated_at">
                        Active since <span class="font-medium">{{ subscription.activated_at }}</span>
                    </p>
                    <p v-if="subscription.status === 'active' && subscription.next_billing_at">
                        Next billing date
                        <span class="font-medium">{{ subscription.next_billing_at }}</span>
                    </p>
                    <p v-if="subscription.cancelled_at" class="text-muted-foreground">
                        Cancelled on {{ subscription.cancelled_at }}. Vault access ends at the end of the
                        paid period.
                    </p>
                </CardContent>
                <CardFooter>
                    <Button
                        v-if="subscription.status === 'active'"
                        variant="destructive"
                        @click="cancelSubscription"
                    >
                        Cancel subscription
                    </Button>
                </CardFooter>
            </Card>

            <Card v-else>
                <CardHeader>
                    <CardTitle>No active subscription</CardTitle>
                    <CardDescription>
                        Subscribe to a plan to unlock the digital vault for your loved ones.
                    </CardDescription>
                </CardHeader>
                <CardFooter>
                    <Button as-child>
                        <Link :href="pricing()">View plans</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
