<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, reject, release } from '@/routes/staff/commerce/credit-purchases';

const props = defineProps<{
    purchase: Record<string, any>;
}>();

const canReview = props.purchase.status === 'pending_review';
</script>

<template>
    <StaffLayout>
        <Head title="Credit purchase" />
        <StaffPageHeader :title="String(purchase.package_name ?? 'Credit purchase')" />
        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent class="space-y-3 text-sm">
                <p><span class="text-muted-foreground">Provider:</span> {{ purchase.service_provider?.name }}</p>
                <p><span class="text-muted-foreground">Pages:</span> {{ purchase.page_count }}</p>
                <p><span class="text-muted-foreground">Amount:</span> R{{ (purchase.price_cents / 100).toFixed(2) }}</p>
                <p><span class="text-muted-foreground">Method:</span> {{ purchase.payment_method }}</p>
                <p><span class="text-muted-foreground">Status:</span> {{ purchase.status }}</p>
                <p><span class="text-muted-foreground">Reference:</span> {{ purchase.payment_reference }}</p>
                <p v-if="purchase.proof_of_payment_url">
                    <a :href="purchase.proof_of_payment_url" target="_blank" class="underline" rel="noopener">
                        View proof of payment
                    </a>
                </p>
            </CardContent>
        </Card>
        <div v-if="canReview" class="flex flex-wrap gap-3">
            <Form v-bind="release.form(purchase.id)">
                <Button type="submit">Release credits</Button>
            </Form>
            <Form v-bind="reject.form(purchase.id)" class="flex flex-col gap-2" #default="{ errors, processing }">
                <Label for="review_note">Rejection note (optional)</Label>
                <Textarea id="review_note" name="review_note" rows="2" />
                <InputError :message="errors.review_note" />
                <Button type="submit" variant="destructive" :disabled="processing">Reject</Button>
            </Form>
        </div>
        <Button variant="outline" as-child>
            <Link :href="index()">Back to list</Link>
        </Button>
    </StaffLayout>
</template>
