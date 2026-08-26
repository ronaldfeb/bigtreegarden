<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { update } from '@/routes/staff/commerce/bank-details';

defineProps<{
    bankDetail: {
        id?: string;
        bank_name: string;
        account_name: string;
        account_number: string;
        branch_code: string | null;
        reference_note: string | null;
    };
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Bank details" />
        <StaffPageHeader
            title="Platform bank details"
            description="Shown to service providers when paying by bank transfer."
        />
        <Card>
            <CardHeader><CardTitle>EFT instructions</CardTitle></CardHeader>
            <CardContent>
                <Form v-bind="update.form()" class="space-y-6" #default="{ errors, processing, recentlySuccessful }">
                    <div class="grid gap-2">
                        <Label for="bank_name">Bank name</Label>
                        <Input id="bank_name" name="bank_name" type="text" :default-value="bankDetail.bank_name" required />
                        <InputError :message="errors.bank_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="account_name">Account name</Label>
                        <Input id="account_name" name="account_name" type="text" :default-value="bankDetail.account_name" required />
                        <InputError :message="errors.account_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="account_number">Account number</Label>
                        <Input id="account_number" name="account_number" type="text" :default-value="bankDetail.account_number" required />
                        <InputError :message="errors.account_number" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="branch_code">Branch code</Label>
                        <Input id="branch_code" name="branch_code" type="text" :default-value="bankDetail.branch_code ?? ''" />
                        <InputError :message="errors.branch_code" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="reference_note">Reference note</Label>
                        <Textarea id="reference_note" name="reference_note" rows="3" :default-value="bankDetail.reference_note ?? ''" />
                        <InputError :message="errors.reference_note" />
                    </div>
                    <div class="flex items-center gap-3">
                        <Button type="submit" :disabled="processing">Save</Button>
                        <p v-if="recentlySuccessful" class="text-sm text-muted-foreground">Saved.</p>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
