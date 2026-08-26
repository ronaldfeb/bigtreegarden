<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
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
        autoSubmit?: boolean;
    }>(),
    { autoSubmit: true },
);

const payfastForm = ref<HTMLFormElement | null>(null);

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
            <h1 class="text-xl font-semibold">Redirecting to PayFast</h1>
            <p class="text-sm text-muted-foreground">
                {{ purchase.package_name }} · {{ purchase.page_count }} pages ·
                R{{ (purchase.price_cents / 100).toFixed(2) }}
            </p>
            <p class="font-mono text-xs">Ref: {{ purchase.payment_reference }}</p>
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
