<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import CheckoutDiscountCode, { type CheckoutDiscount } from '@/components/checkout/CheckoutDiscountCode.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import {
    apply as applyDiscount,
    remove as removeDiscount,
} from '@/routes/provider/credits/discount';
import { index, proof } from '@/routes/provider/credits';

withDefaults(
    defineProps<{
        purchase: {
            id: string;
            package_name: string;
            page_count: number;
            price_cents: number;
            currency: string;
            payment_reference: string;
            status: string;
            proof_of_payment_path: string | null;
        };
        bankDetails: {
            bank_name: string;
            account_name: string;
            account_number: string;
            branch_code: string | null;
            reference_note: string | null;
        } | null;
        amount_cents?: number;
        original_amount_cents?: number;
        discount?: CheckoutDiscount;
    }>(),
    {
        amount_cents: undefined,
        original_amount_cents: undefined,
        discount: null,
    },
);
</script>

<template>
    <ProviderLayout>
        <Head title="Bank transfer" />
        <div class="mx-auto max-w-2xl space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Pay by bank transfer</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <p>
                        Transfer
                        <strong>R{{ ((amount_cents ?? purchase.price_cents) / 100).toFixed(2) }}</strong>
                        for {{ purchase.page_count }} memorial pages.
                        <span
                            v-if="
                                discount &&
                                (original_amount_cents ?? purchase.price_cents) !==
                                    (amount_cents ?? purchase.price_cents)
                            "
                            class="text-muted-foreground line-through"
                        >
                            R{{ ((original_amount_cents ?? purchase.price_cents) / 100).toFixed(2) }}
                        </span>
                    </p>

                    <CheckoutDiscountCode
                        :discount="discount"
                        :apply-url="applyDiscount.url(purchase.id)"
                        :remove-url="removeDiscount.url(purchase.id)"
                        :currency="purchase.currency"
                    />

                    <template v-if="bankDetails">
                        <p><span class="text-muted-foreground">Bank:</span> {{ bankDetails.bank_name }}</p>
                        <p><span class="text-muted-foreground">Account name:</span> {{ bankDetails.account_name }}</p>
                        <p><span class="text-muted-foreground">Account number:</span> {{ bankDetails.account_number }}</p>
                        <p v-if="bankDetails.branch_code">
                            <span class="text-muted-foreground">Branch code:</span> {{ bankDetails.branch_code }}
                        </p>
                        <p v-if="bankDetails.reference_note" class="text-muted-foreground">
                            {{ bankDetails.reference_note }}
                        </p>
                    </template>
                    <p v-else class="text-amber-700">Bank details are not configured yet. Please contact support.</p>
                    <p class="rounded-md bg-muted p-3 font-mono text-xs">
                        Payment reference: {{ purchase.payment_reference }}
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Upload proof of payment (PDF)</CardTitle>
                </CardHeader>
                <CardContent>
                    <Form
                        v-bind="proof.form(purchase.id)"
                        enctype="multipart/form-data"
                        class="space-y-4"
                        #default="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label for="proof_of_payment">PDF document</Label>
                            <Input
                                id="proof_of_payment"
                                name="proof_of_payment"
                                type="file"
                                accept="application/pdf,.pdf"
                                required
                            />
                            <InputError :message="errors.proof_of_payment" />
                        </div>
                        <div class="flex gap-2">
                            <Button type="submit" :disabled="processing">Upload</Button>
                            <Button as-child variant="outline">
                                <Link :href="index()">Back</Link>
                            </Button>
                        </div>
                    </Form>
                </CardContent>
            </Card>
        </div>
    </ProviderLayout>
</template>
