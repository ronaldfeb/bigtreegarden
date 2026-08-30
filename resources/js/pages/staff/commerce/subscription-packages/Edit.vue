<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { centsToRandInput, randInputToCents } from '@/lib/utils';
import { show, update } from '@/routes/staff/commerce/subscription-packages';

type PackageFeature = {
    id?: string;
    label: string;
    description: string | null;
    is_included: boolean;
    sort_order: number;
};

type SubscriptionPackageRecord = {
    id: string;
    name: string;
    description: string | null;
    price_cents: number;
    currency: string;
    billing_interval: string;
    is_featured: boolean;
    is_active: boolean;
    sort_order: number;
    features: PackageFeature[];
};

type FeatureRow = {
    key: string;
    id?: string;
    label: string;
    description: string;
    is_included: boolean;
};

const props = defineProps<{
    package: SubscriptionPackageRecord;
}>();

const priceRand = ref(centsToRandInput(props.package.price_cents));

const features = ref<FeatureRow[]>(
    props.package.features.map((feature) => ({
        key: feature.id ?? crypto.randomUUID(),
        id: feature.id,
        label: feature.label,
        description: feature.description ?? '',
        is_included: feature.is_included,
    })),
);

const addFeature = (): void => {
    features.value.push({
        key: crypto.randomUUID(),
        label: '',
        description: '',
        is_included: true,
    });
};

const removeFeature = (index: number): void => {
    features.value.splice(index, 1);
};
</script>

<template>
    <StaffLayout>
        <Head title="Edit subscription package" />

        <StaffPageHeader title="Edit subscription package" />

        <Form
            v-bind="update.form(package.id)"
            class="space-y-6"
            #default="{ errors, processing }"
        >
            <input type="hidden" name="sync_features" value="1" />

            <Card>
                <CardHeader>
                    <CardTitle>Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" type="text" :default-value="package.name" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" name="description" rows="8" :default-value="package.description" />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="price_rand">Price (R)</Label>
                        <input type="hidden" name="price_cents" :value="randInputToCents(priceRand)" />
                        <Input
                            id="price_rand"
                            v-model="priceRand"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                        />
                        <InputError :message="errors.price_cents" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="currency">Currency</Label>
                        <Input id="currency" name="currency" type="text" :default-value="package.currency" />
                        <InputError :message="errors.currency" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="billing_interval">Billing interval</Label>
                        <select id="billing_interval" name="billing_interval" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
                            <option value="once_off" :selected="package.billing_interval === 'once_off'">Once off</option>
                            <option value="monthly" :selected="package.billing_interval === 'monthly'">Monthly</option>
                            <option value="annual" :selected="package.billing_interval === 'annual'">Annual</option>
                        </select>
                        <InputError :message="errors.billing_interval" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_featured" value="0" />
                        <input id="is_featured" name="is_featured" type="checkbox" value="1" class="size-4 rounded border-input" :checked="Boolean(package.is_featured)" />
                        <Label for="is_featured">Featured</Label>
                        <InputError :message="errors.is_featured" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0" />
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="size-4 rounded border-input" :checked="Boolean(package.is_active)" />
                        <Label for="is_active">Active</Label>
                        <InputError :message="errors.is_active" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="sort_order">Sort order</Label>
                        <Input id="sort_order" name="sort_order" type="number" :default-value="package.sort_order" />
                        <InputError :message="errors.sort_order" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between gap-4 space-y-0">
                    <CardTitle>Features</CardTitle>
                    <Button type="button" variant="outline" size="sm" @click="addFeature">
                        <Plus class="size-4" />
                        Add feature
                    </Button>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p v-if="features.length === 0" class="text-muted-foreground text-sm">
                        No features yet. Add the benefits shown on the pricing page.
                    </p>

                    <div
                        v-for="(feature, index) in features"
                        :key="feature.key"
                        class="space-y-4 rounded-lg border border-border p-4"
                    >
                        <input v-if="feature.id" type="hidden" :name="`features[${index}][id]`" :value="feature.id" />

                        <div class="flex items-start justify-between gap-3">
                            <p class="font-medium text-sm">Feature {{ index + 1 }}</p>
                            <Button type="button" variant="ghost" size="icon" :aria-label="`Remove feature ${index + 1}`" @click="removeFeature(index)">
                                <Trash2 class="size-4" />
                            </Button>
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`feature-label-${index}`">Label</Label>
                            <Input
                                :id="`feature-label-${index}`"
                                v-model="feature.label"
                                :name="`features[${index}][label]`"
                                type="text"
                                required
                            />
                            <InputError :message="errors[`features.${index}.label`]" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`feature-description-${index}`">Description</Label>
                            <Input
                                :id="`feature-description-${index}`"
                                v-model="feature.description"
                                :name="`features[${index}][description]`"
                                type="text"
                            />
                            <InputError :message="errors[`features.${index}.description`]" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="hidden" :name="`features[${index}][is_included]`" value="0" />
                            <input
                                :id="`feature-included-${index}`"
                                :name="`features[${index}][is_included]`"
                                type="checkbox"
                                value="1"
                                class="size-4 rounded border-input"
                                :checked="feature.is_included"
                            />
                            <Label :for="`feature-included-${index}`">Included in this package</Label>
                            <InputError :message="errors[`features.${index}.is_included`]" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <StaffFormActions :cancel-href="show(package.id)" :processing="processing" />
        </Form>
    </StaffLayout>
</template>
