<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { randInputToCents } from '@/lib/utils';
import { index, store } from '@/routes/staff/commerce/discount-codes';

type TargetGroup = {
    group: string;
    options: { value: string; label: string }[];
};

const props = defineProps<{
    targets: TargetGroup[];
}>();

const discountType = ref<'percent' | 'fixed'>('percent');
const appliesToAll = ref(true);
const amountRand = ref('');
const target = ref(props.targets[0]?.options[0]?.value ?? '');

const amountCents = computed(() => randInputToCents(amountRand.value));
</script>

<template>
    <StaffLayout>
        <Head title="Generate discount code" />
        <StaffPageHeader title="Generate discount code" />
        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form v-bind="store.form()" class="space-y-6" #default="{ errors, processing }">
                    <div class="grid gap-2">
                        <Label for="discount_type">Discount type</Label>
                        <select
                            id="discount_type"
                            v-model="discountType"
                            name="discount_type"
                            class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                            required
                        >
                            <option value="percent">Percent</option>
                            <option value="fixed">Fixed amount (ZAR)</option>
                        </select>
                        <InputError :message="errors.discount_type" />
                    </div>

                    <div v-if="discountType === 'percent'" class="grid gap-2">
                        <Label for="percent">Percent off</Label>
                        <Input id="percent" name="percent" type="number" min="1" max="100" required />
                        <InputError :message="errors.percent" />
                    </div>

                    <div v-else class="grid gap-2">
                        <Label for="amount_rand">Amount off (R)</Label>
                        <input type="hidden" name="amount_cents" :value="amountCents" />
                        <Input
                            id="amount_rand"
                            v-model="amountRand"
                            type="number"
                            min="0.01"
                            step="0.01"
                            required
                        />
                        <InputError :message="errors.amount_cents" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="starts_at">Starts at</Label>
                            <Input id="starts_at" name="starts_at" type="datetime-local" required />
                            <InputError :message="errors.starts_at" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="ends_at">Ends at</Label>
                            <Input id="ends_at" name="ends_at" type="datetime-local" required />
                            <InputError :message="errors.ends_at" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="applies_to_all" value="0" />
                        <input
                            id="applies_to_all"
                            v-model="appliesToAll"
                            name="applies_to_all"
                            type="checkbox"
                            value="1"
                            class="size-4 rounded border-input"
                        />
                        <Label for="applies_to_all">Applies to all packages</Label>
                    </div>
                    <InputError :message="errors.applies_to_all" />

                    <div v-if="!appliesToAll" class="grid gap-2">
                        <Label for="target">Target package</Label>
                        <select
                            id="target"
                            v-model="target"
                            name="target"
                            class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                            required
                        >
                            <optgroup v-for="group in targets" :key="group.group" :label="group.group">
                                <option v-for="option in group.options" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </optgroup>
                        </select>
                        <InputError :message="errors.discountable_id || errors.discountable_type || errors.target" />
                    </div>

                    <p class="text-muted-foreground text-sm">
                        A unique code in the format <span class="font-mono">XX-XXXX-XX</span> will be generated when you
                        save.
                    </p>

                    <StaffFormActions :cancel-href="index()" :processing="processing" submit-label="Generate" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
