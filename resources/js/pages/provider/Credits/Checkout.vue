<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import CheckoutDiscountCode, { type CheckoutDiscount } from '@/components/checkout/CheckoutDiscountCode.vue';
import { Button } from '@/components/ui/button';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import {
    apply as applyDiscount,
    remove as removeDiscount,
} from '@/routes/provider/credits/discount';
import { index } from '@/routes/provider/credits';

const props = withDefaults(
    defineProps<{
        checkoutUrl: string;
        payload: Record<string, string | number | null>;
        purchase: {
            id: string;
            package_name: string;
            page_count: number;
            price_cents: number;
            currency: string;
            payment_reference: string;
        };
        amount_cents?: number;
        original_amount_cents?: number;
        autoSubmit?: boolean;
        discount?: CheckoutDiscount;
    }>(),
    {
        autoSubmit: false,
        discount: null,
        amount_cents: undefined,
        original_amount_cents: undefined,
    },
);

const payfastForm = ref<HTMLFormElement | null>(null);

const payableCents = computed(() => props.amount_cents ?? props.purchase.price_cents);
const listCents = computed(() => props.original_amount_cents ?? props.purchase.price_cents);

onMounted(() => {
    if (props.autoSubmit) {
        payfastForm.value?.submit();
    }
});
</script>

<template>
    <ProviderLayout>
        <Head title="PayFast checkout" />
        <div class="mx-auto max-w-lg space-y-4 rounded-xl border border-border bg-card p-6">
            <h1 class="text-xl font-semibold">Complete payment</h1>
            <p class="text-sm text-muted-foreground">
                {{ purchase.package_name }} · {{ purchase.page_count }} pages
            </p>
            <div class="text-right">
                <p
                    v-if="discount && listCents !== payableCents"
                    class="text-muted-foreground text-xs line-through"
                >
                    R{{ (listCents / 100).toFixed(2) }}
                </p>
                <p class="font-semibold">R{{ (payableCents / 100).toFixed(2) }}</p>
            </div>
            <p class="font-mono text-xs">Ref: {{ purchase.payment_reference }}</p>

            <CheckoutDiscountCode
                :discount="discount"
                :apply-url="applyDiscount.url(purchase.id)"
                :remove-url="removeDiscount.url(purchase.id)"
                :currency="purchase.currency"
            />

            <form ref="payfastForm" :action="checkoutUrl" method="post" class="space-y-3">
                <input
                    v-for="(value, key) in payload"
                    :key="key"
                    type="hidden"
                    :name="key"
                    :value="value ?? ''"
                />
                <Button type="submit" class="w-full">
                    {{ autoSubmit ? 'Redirecting to PayFast...' : 'Pay with PayFast' }}
                </Button>
            </form>
            <Button as-child variant="outline" class="w-full">
                <Link :href="index()">Cancel</Link>
            </Button>
        </div>
    </ProviderLayout>
</template>
