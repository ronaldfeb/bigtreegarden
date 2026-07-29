<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import PamphletPreview from '@/components/pamphlets/PamphletPreview.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';

const props = withDefaults(
    defineProps<{
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
        canRegister: true,
        pricing: null,
    },
);

const isUnpaid = computed(
    () => props.pamphlet.status === 'draft' || props.pamphlet.status === 'pending_payment',
);

const isPaid = computed(
    () => props.pamphlet.status === 'paid' || props.pamphlet.status === 'published',
);

const formattedPrice = computed(() => {
    if (props.pricing === null) {
        return null;
    }

    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: props.pricing.currency || 'ZAR',
    }).format(props.pricing.price_cents / 100);
});

const statusLabel = computed(() => props.pamphlet.status.replaceAll('_', ' '));
</script>

<template>

    <Head title="Review pamphlet" />

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
                            <p v-if="formattedPrice" class="shrink-0 font-semibold text-base">{{ formattedPrice }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-muted-foreground">Status</span>
                            <span class="rounded-full border border-border px-2.5 py-0.5 capitalize">{{ statusLabel
                                }}</span>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3">
                        <template v-if="isUnpaid">
                            <Button as-child variant="outline" class="w-1/3">
                                <Link :href="`/pamphlets/${pamphlet.id}/edit`">Back to edit</Link>
                            </Button>
                            <Button as-child class="w-full bg-brand hover:bg-brand-strong">
                                <Link :href="`/pamphlets/${pamphlet.id}/continue`">Pay with PayFast</Link>
                            </Button>
                        </template>

                        <Button v-if="isPaid" as-child class="w-full bg-brand hover:bg-brand-strong">
                            <Link :href="`/pamphlets/${pamphlet.id}/memorial/edit`">Edit memorial page</Link>
                        </Button>
                    </div>

                    <div v-if="isUnpaid" class="mt-6 space-y-3 border-t border-border pt-5">
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
