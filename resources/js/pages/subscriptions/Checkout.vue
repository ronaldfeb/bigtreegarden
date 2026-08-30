<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import CheckoutDiscountCode, { type CheckoutDiscount } from '@/components/checkout/CheckoutDiscountCode.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { pricing } from '@/routes';
import {
    apply as applyDiscount,
    remove as removeDiscount,
} from '@/routes/subscriptions/discount';

const props = withDefaults(
    defineProps<{
        checkoutUrl: string;
        payload: Record<string, string | number | null>;
        subscriptionId: string;
        packageName: string;
        amount_cents: number;
        original_amount_cents?: number;
        recurring_amount_cents?: number;
        currency: string;
        billing_interval: string;
        autoSubmit?: boolean;
        discount?: CheckoutDiscount;
        canRegister?: boolean;
    }>(),
    {
        autoSubmit: false,
        canRegister: true,
        discount: null,
        original_amount_cents: undefined,
        recurring_amount_cents: undefined,
    },
);

const payfastForm = ref<HTMLFormElement | null>(null);

const billingLabels: Record<string, string> = {
    monthly: 'per month',
    annual: 'per year',
};

const formattedPrice = computed(() =>
    new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: props.currency || 'ZAR',
    }).format(props.amount_cents / 100),
);

const formattedListPrice = computed(() =>
    new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: props.currency || 'ZAR',
    }).format((props.original_amount_cents ?? props.amount_cents) / 100),
);

const formattedRecurring = computed(() =>
    new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: props.currency || 'ZAR',
    }).format((props.recurring_amount_cents ?? props.amount_cents) / 100),
);

const intervalLabel = computed(
    () => billingLabels[props.billing_interval] ?? props.billing_interval,
);

onMounted(() => {
    if (!props.autoSubmit) {
        return;
    }

    payfastForm.value?.submit();
});
</script>

<template>
    <Head title="Subscription checkout" />

    <MarketingLayout :can-register="canRegister">
        <div class="mx-auto w-full min-w-0 max-w-2xl overflow-x-hidden px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="mb-2 font-semibold text-2xl">Subscribe to {{ packageName }}</h1>
            <p class="mb-8 text-muted-foreground text-sm">
                Confirm your plan, then continue to secure payment with PayFast.
            </p>

            <aside class="rounded-2xl border border-border bg-card p-5 shadow-sm sm:p-6">
                <h2 class="font-medium text-lg">Order summary</h2>
                <p class="mt-1 text-muted-foreground text-sm">Vault subscription</p>

                <div class="mt-6 space-y-4 border-t border-border pt-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium text-sm">{{ packageName }}</p>
                            <p class="text-muted-foreground text-xs capitalize">{{ intervalLabel }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p
                                v-if="discount && original_amount_cents && original_amount_cents !== amount_cents"
                                class="text-muted-foreground text-xs line-through"
                            >
                                {{ formattedListPrice }}
                            </p>
                            <p class="font-semibold text-base">{{ formattedPrice }}</p>
                            <p class="text-muted-foreground text-xs">
                                Then {{ formattedRecurring }} {{ intervalLabel }}
                            </p>
                        </div>
                    </div>

                    <CheckoutDiscountCode
                        :discount="discount"
                        :apply-url="applyDiscount.url(subscriptionId)"
                        :remove-url="removeDiscount.url(subscriptionId)"
                        :currency="currency"
                    />
                </div>

                <form ref="payfastForm" :action="checkoutUrl" method="post" class="mt-8 flex flex-col gap-3">
                    <input
                        v-for="(value, key) in payload"
                        :key="key"
                        type="hidden"
                        :name="key"
                        :value="value ?? ''"
                    />

                    <Button as-child variant="outline" class="w-full sm:w-1/3">
                        <Link :href="pricing()">Back to pricing</Link>
                    </Button>

                    <Button type="submit" class="w-full bg-brand hover:bg-brand-strong">
                        {{ autoSubmit ? 'Redirecting to PayFast...' : 'Subscribe with PayFast' }}
                    </Button>
                </form>

                <div class="mt-6 space-y-3 border-t border-border pt-5">
                    <img src="/assets/logo/Payfast-logo.svg" alt="PayFast" class="h-10 w-auto" />
                    <p class="text-muted-foreground text-sm leading-relaxed">
                        You will be redirected to our payment processor PayFast to set up your recurring subscription.
                        Any discount applies to the first payment only.
                    </p>
                </div>
            </aside>
        </div>
    </MarketingLayout>
</template>
