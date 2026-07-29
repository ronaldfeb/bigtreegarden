<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import PamphletPreview from '@/components/pamphlets/PamphletPreview.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';

const props = withDefaults(
    defineProps<{
        checkoutUrl: string;
        payload: Record<string, string | number | null>;
        paymentId: string;
        amount_cents: number;
        currency: string;
        autoSubmit?: boolean;
        pamphlet: {
            id: string;
            heading: string;
            person_full_name: string;
            date_of_birth: string | null;
            date_of_passing: string | null;
            date_format: string;
            short_text: string;
            uploaded_image_url: string | null;
            image_shape: string;
            image_crop_mode: string;
            background_asset_path: string | null;
            font_family: string;
            heading_color: string;
            name_color: string;
            short_text_color: string;
            dates_color: string;
            status: string;
            public_slug: string;
            pamphlet_qr_code?: { image_path: string | null; target_url?: string | null } | null;
        };
        pricing: {
            name: string;
            price_cents: number;
            currency: string;
            billing_interval: string;
        } | null;
        canRegister?: boolean;
    }>(),
    {
        autoSubmit: true,
        canRegister: true,
        pricing: null,
    },
);

const payfastForm = ref<HTMLFormElement | null>(null);

const formattedPrice = computed(() =>
    new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: props.currency || props.pricing?.currency || 'ZAR',
    }).format(props.amount_cents / 100),
);

const statusLabel = computed(() => props.pamphlet.status.replaceAll('_', ' '));

onMounted(() => {
    if (!props.autoSubmit) {
        return;
    }

    payfastForm.value?.submit();
});
</script>

<template>

    <Head title="Checkout" />

    <MarketingLayout :can-register="canRegister">
        <div class="mx-auto w-full min-w-0 max-w-6xl overflow-x-hidden px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="mb-2 font-semibold text-2xl">Review your pamphlet</h1>
            <p class="mb-8 text-muted-foreground text-sm">
                Confirm the design, then continue to secure payment with PayFast.
            </p>

            <div class="grid gap-8 lg:grid-cols-2 lg:items-start">
                <div>
                    <PamphletPreview :pamphlet="pamphlet" />
                </div>

                <aside class="rounded-2xl border border-border bg-card p-5 shadow-sm sm:p-6">
                    <h2 class="font-medium text-lg">Order summary</h2>
                    <p class="mt-1 text-muted-foreground text-sm">Memorial pamphlet purchase</p>

                    <div class="mt-6 space-y-4 border-t border-border pt-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-sm">{{ pricing?.name ?? 'Memorial pamphlet' }}</p>
                                <p class="text-muted-foreground text-xs">For {{ pamphlet.person_full_name }}</p>
                            </div>
                            <p class="shrink-0 font-semibold text-base">{{ formattedPrice }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-muted-foreground">Status</span>
                            <span class="rounded-full border border-border px-2.5 py-0.5 capitalize">{{ statusLabel
                                }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-muted-foreground">Payment reference</span>
                            <span class="font-mono text-xs">#{{ paymentId }}</span>
                        </div>
                    </div>

                    <form ref="payfastForm" :action="checkoutUrl" method="post" class="mt-8 flex flex-col gap-3">
                        <input v-for="(value, key) in payload" :key="key" type="hidden" :name="key"
                            :value="value ?? ''" />

                        <Button as-child variant="outline" class="w-full sm:w-1/3">
                            <Link :href="`/pamphlets/${pamphlet.id}/edit`">Back to edit</Link>
                        </Button>

                        <Button type="submit" class="w-full bg-brand hover:bg-brand-strong">
                            {{ autoSubmit ? 'Redirecting to PayFast...' : 'Pay with PayFast' }}
                        </Button>
                    </form>

                    <div class="mt-6 space-y-3 border-t border-border pt-5">
                        <img src="/assets/logo/Payfast-logo.svg" alt="PayFast" class="h-10 w-auto" />
                        <p class="text-muted-foreground text-sm leading-relaxed">
                            You will be redirected to our payment processor PayFast to complete the payment.
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </MarketingLayout>
</template>
