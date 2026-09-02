<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = withDefaults(
    defineProps<{
        pamphletId: string;
        continueUrl?: string;
    }>(),
    {
        continueUrl: undefined,
    },
);

const paymentUrl = props.continueUrl ?? `/pamphlets/${props.pamphletId}/continue`;
</script>

<template>
    <div
        class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-center dark:border-amber-900/60 dark:bg-amber-950/30"
        role="status"
    >
        <p class="font-semibold text-amber-950 text-lg dark:text-amber-100">Pending payment</p>
        <p class="mx-auto mt-2 max-w-md text-amber-900/80 text-sm dark:text-amber-100/80">
            Complete payment to unlock printing, the memorial QR code, and your public memorial page.
        </p>
        <Button as-child class="mt-4 bg-brand hover:bg-brand-strong">
            <Link :href="paymentUrl">Continue to payment</Link>
        </Button>
    </div>
</template>
