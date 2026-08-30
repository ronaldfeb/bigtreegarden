<script setup lang="ts">
import { Form, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type CheckoutDiscount = {
    code: string;
    formatted_code: string;
    discount_cents: number;
    amount_cents: number;
    original_amount_cents: number;
} | null;

const props = defineProps<{
    discount: CheckoutDiscount;
    applyUrl: string;
    removeUrl: string;
    currency?: string;
}>();

const page = usePage();
const code = ref(props.discount?.formatted_code ?? '');

watch(
    () => props.discount?.formatted_code,
    (value) => {
        if (value) {
            code.value = value;
        }
    },
);

const codeError = computed(() => {
    const errors = page.props.errors as Record<string, string> | undefined;

    return errors?.code;
});

function formatCodeInput(value: string): string {
    const normalized = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 8);
    const parts = [normalized.slice(0, 2), normalized.slice(2, 6), normalized.slice(6, 8)].filter(Boolean);

    return parts.join('-');
}

function onCodeInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    code.value = formatCodeInput(target.value);
}

const money = (cents: number): string =>
    new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: props.currency || 'ZAR',
    }).format(cents / 100);
</script>

<template>
    <div class="space-y-3 rounded-xl border border-border bg-muted/30 p-4">
        <div class="flex items-center justify-between gap-3">
            <Label for="discount_code" class="font-medium text-sm">Discount code</Label>
            <p v-if="discount" class="text-muted-foreground text-xs">{{ discount.formatted_code }} applied</p>
        </div>

        <Form
            v-if="!discount"
            :action="applyUrl"
            method="post"
            class="flex flex-col gap-2 sm:flex-row sm:items-start"
            #default="{ processing }"
        >
            <div class="grid flex-1 gap-1">
                <Input
                    id="discount_code"
                    v-model="code"
                    name="code"
                    type="text"
                    maxlength="10"
                    placeholder="0A-YO1B-88"
                    class="font-mono uppercase"
                    autocomplete="off"
                    @input="onCodeInput"
                />
                <InputError :message="codeError" />
            </div>
            <Button type="submit" variant="outline" :disabled="processing || code.length < 10">
                Apply
            </Button>
        </Form>

        <Form v-else :action="removeUrl" method="delete" #default="{ processing }">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm">
                    <p>
                        List price
                        <span class="font-medium">{{ money(discount.original_amount_cents) }}</span>
                    </p>
                    <p class="text-muted-foreground">
                        Discount
                        <span class="font-medium text-foreground">-{{ money(discount.discount_cents) }}</span>
                    </p>
                </div>
                <Button type="submit" variant="ghost" size="sm" :disabled="processing">Remove</Button>
            </div>
        </Form>
    </div>
</template>
