<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import { index as creditsIndex } from '@/routes/provider/credits';
import { index as memorialsIndex } from '@/routes/provider/memorials';
import { edit as profileEdit } from '@/routes/provider/profile';

type Props = {
    serviceProvider: {
        id: string;
        name: string;
        slug: string;
        status: 'pending' | 'active' | 'suspended';
        logo_path: string | null;
        city: string | null;
        province: string | null;
        credits_remaining: number;
    };
    counts: {
        services: number;
        specialities: number;
        social_media: number;
        images: number;
        memorials: number;
        backgrounds: number;
        pending_bank_transfers: number;
    };
    profileCompleteness: number;
    isOwner: boolean;
};

const props = defineProps<Props>();

const statusVariant = {
    pending: 'secondary',
    active: 'default',
    suspended: 'destructive',
} as const;

const countCards = [
    { label: 'Credits remaining', value: props.serviceProvider.credits_remaining },
    { label: 'Memorials', value: props.counts.memorials, href: memorialsIndex() },
    { label: 'Pending bank transfers', value: props.counts.pending_bank_transfers },
    { label: 'Backgrounds', value: props.counts.backgrounds },
    { label: 'Services', value: props.counts.services },
    { label: 'Gallery images', value: props.counts.images },
];
</script>

<template>
    <ProviderLayout>
        <Head title="Provider dashboard" />

        <div class="flex flex-wrap items-center justify-between gap-4">
            <Heading
                :title="serviceProvider.name"
                description="Manage your public listing, team, credits, and client memorials"
            />
            <Badge :variant="statusVariant[serviceProvider.status]" class="capitalize">
                {{ serviceProvider.status }}
            </Badge>
        </div>

        <div
            v-if="serviceProvider.status === 'pending'"
            class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-100"
        >
            Your listing is awaiting approval. It will only appear in the public
            directory once our team has activated it. You can complete your
            profile and invite team members in the meantime. Purchasing credits
            and creating memorials unlock after approval.
        </div>

        <div
            v-else-if="serviceProvider.status === 'suspended'"
            class="rounded-lg border border-destructive/40 bg-destructive/10 p-4 text-sm"
        >
            Your listing is suspended. Contact BigTreeGarden support for assistance.
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Profile completeness</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full bg-primary transition-all"
                        :style="{ width: `${profileCompleteness}%` }"
                    />
                </div>
                <p class="text-sm text-muted-foreground">
                    {{ profileCompleteness }}% complete.
                    <Link
                        v-if="profileCompleteness < 100"
                        :href="profileEdit()"
                        class="text-foreground underline underline-offset-4"
                    >
                        Finish your profile
                    </Link>
                </p>
            </CardContent>
        </Card>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="card in countCards" :key="card.label">
                <CardHeader>
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ card.label }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-semibold">{{ card.value }}</p>
                    <Link
                        v-if="card.href"
                        :href="card.href"
                        class="text-sm underline underline-offset-4"
                    >
                        View
                    </Link>
                </CardContent>
            </Card>
        </div>

        <div v-if="isOwner && serviceProvider.status === 'active'" class="flex gap-2">
            <Link
                :href="creditsIndex()"
                class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground"
            >
                Buy memorial credits
            </Link>
        </div>
    </ProviderLayout>
</template>
