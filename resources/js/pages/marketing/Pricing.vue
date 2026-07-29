<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Check, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { register } from '@/routes';
import { create } from '@/routes/pamphlets';
import { store } from '@/routes/subscriptions';

type PricingPackage = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    price_cents: number;
    currency: string;
    billing_interval: string;
    is_featured: boolean;
    features: Array<{
        id: string;
        label: string;
        description: string | null;
        is_included: boolean;
    }>;
};

withDefaults(
    defineProps<{
        canRegister?: boolean;
        packages?: PricingPackage[];
    }>(),
    {
        canRegister: true,
        packages: () => [],
    },
);

const billingLabels: Record<string, string> = {
    once_off: 'once-off',
    monthly: 'per month',
    annual: 'per year',
};

const page = usePage();
const isAuthenticated = computed(() => Boolean((page.props.auth as { user: unknown } | undefined)?.user));

function isMemorialPlan(pkg: Pick<PricingPackage, 'billing_interval'>): boolean {
    return pkg.billing_interval === 'once_off';
}

function choosePlan(pkg: Pick<PricingPackage, 'id' | 'billing_interval'>): void {
    if (isMemorialPlan(pkg)) {
        router.get(create().url);

        return;
    }

    if (!isAuthenticated.value) {
        router.get(register({ query: { intent: 'vault' } }).url);

        return;
    }

    router.post(store(pkg.id).url);
}

function formatPrice(cents: number, currency: string): string {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency,
        minimumFractionDigits: 0,
    }).format(cents / 100);
}
</script>

<template>
    <Head title="Pricing" />

    <MarketingLayout :can-register="canRegister">
        <section class="mx-auto min-w-0 max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="space-y-4 text-center">
                <p class="text-eyebrow text-gold">Plans</p>
                <h1 class="text-display">Simple, transparent pricing</h1>
                <p class="mx-auto max-w-2xl text-body text-pretty text-muted-foreground">
                    Choose the memorial package that fits your family's needs.
                </p>
            </div>

            <div
                v-if="packages.length > 0"
                class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="pkg in packages"
                    :key="pkg.id"
                    class="flex flex-col"
                    :class="pkg.is_featured ? 'border-gold shadow-warm-lg ring-1 ring-gold/30' : ''"
                >
                    <CardHeader>
                        <p v-if="pkg.is_featured" class="text-eyebrow text-gold">Most popular</p>
                        <CardTitle class="text-heading text-xl">{{ pkg.name }}</CardTitle>
                        <CardDescription v-if="pkg.description">{{ pkg.description }}</CardDescription>
                        <p class="pt-2">
                            <span class="text-display text-3xl text-brand-strong">
                                {{ formatPrice(pkg.price_cents, pkg.currency) }}
                            </span>
                            <span class="text-muted-foreground text-sm">
                                {{ billingLabels[pkg.billing_interval] ?? pkg.billing_interval }}
                            </span>
                        </p>
                    </CardHeader>

                    <CardContent class="flex-1">
                        <ul class="space-y-3">
                            <li
                                v-for="feature in pkg.features"
                                :key="feature.id"
                                class="flex items-start gap-2 text-sm"
                            >
                                <Check
                                    v-if="feature.is_included"
                                    class="mt-0.5 size-4 shrink-0 text-brand"
                                />
                                <X
                                    v-else
                                    class="mt-0.5 size-4 shrink-0 text-muted-foreground/50"
                                />
                                <span :class="feature.is_included ? '' : 'text-muted-foreground line-through'">
                                    {{ feature.label }}
                                </span>
                            </li>
                        </ul>
                    </CardContent>

                    <CardFooter>
                        <Button
                            v-if="isMemorialPlan(pkg)"
                            as-child
                            class="w-full"
                            :variant="pkg.is_featured ? 'default' : 'outline'"
                        >
                            <Link :href="create()">Get started</Link>
                        </Button>
                        <Button
                            v-else
                            class="w-full"
                            :variant="pkg.is_featured ? 'default' : 'outline'"
                            @click="choosePlan(pkg)"
                        >
                            Choose plan
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <p v-else class="mt-12 text-center text-muted-foreground">
                Pricing plans are coming soon.
            </p>
        </section>
    </MarketingLayout>
</template>
