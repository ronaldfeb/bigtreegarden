<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import { bankTransfer, checkout, store } from '@/routes/provider/credits';

type Package = {
    id: string;
    name: string;
    description: string | null;
    page_count: number;
    price_cents: number;
    currency: string;
};

type Purchase = {
    id: string;
    package_name: string;
    page_count: number;
    price_cents: number;
    currency: string;
    payment_method: string;
    status: string;
    payment_reference: string;
    created_at: string | null;
};

defineProps<{
    creditsRemaining: number;
    packages: Package[];
    purchases: {
        data: Purchase[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    bankDetails: Record<string, string | null> | null;
}>();

function formatMoney(cents: number, currency: string): string {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency,
    }).format(cents / 100);
}
</script>

<template>
    <ProviderLayout>
        <Head title="Memorial credits" />
        <Heading
            title="Memorial page credits"
            description="Buy memorial pages in bulk at a discount, then create pages for your clients"
        />

        <Card>
            <CardHeader>
                <CardTitle>Balance</CardTitle>
            </CardHeader>
            <CardContent>
                <p class="text-4xl font-semibold">{{ creditsRemaining }}</p>
                <p class="text-sm text-muted-foreground">credits remaining</p>
            </CardContent>
        </Card>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card v-for="pack in packages" :key="pack.id">
                <CardHeader>
                    <CardTitle>{{ pack.name }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm text-muted-foreground">{{ pack.description }}</p>
                    <p class="text-lg font-semibold">
                        {{ pack.page_count }} pages · {{ formatMoney(pack.price_cents, pack.currency) }}
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <Form v-bind="store.form()">
                            <input type="hidden" name="service_provider_credit_package_id" :value="pack.id" />
                            <input type="hidden" name="payment_method" value="payfast" />
                            <Button type="submit">Pay with PayFast</Button>
                        </Form>
                        <Form v-bind="store.form()">
                            <input type="hidden" name="service_provider_credit_package_id" :value="pack.id" />
                            <input type="hidden" name="payment_method" value="bank_transfer" />
                            <Button type="submit" variant="outline">Bank transfer</Button>
                        </Form>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Purchase history</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div
                    v-for="purchase in purchases.data"
                    :key="purchase.id"
                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border p-3"
                >
                    <div>
                        <p class="font-medium">{{ purchase.package_name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ purchase.page_count }} pages · {{ purchase.payment_reference }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="secondary" class="capitalize">
                            {{ purchase.status.replaceAll('_', ' ') }}
                        </Badge>
                        <Button
                            v-if="purchase.payment_method === 'bank_transfer' && purchase.status !== 'released'"
                            as-child
                            variant="outline"
                            size="sm"
                        >
                            <Link :href="bankTransfer.url(purchase.id)">Upload POP</Link>
                        </Button>
                        <Button
                            v-if="purchase.payment_method === 'payfast' && purchase.status === 'pending_payment'"
                            as-child
                            variant="outline"
                            size="sm"
                        >
                            <Link :href="checkout.url(purchase.id)">Continue payment</Link>
                        </Button>
                    </div>
                </div>
                <p v-if="purchases.data.length === 0" class="text-sm text-muted-foreground">
                    No purchases yet.
                </p>
            </CardContent>
        </Card>
    </ProviderLayout>
</template>
