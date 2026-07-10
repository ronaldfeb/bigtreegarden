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
    };
    counts: {
        services: number;
        specialities: number;
        social_media: number;
        images: number;
    };
    profileCompleteness: number;
};

const props = defineProps<Props>();

const statusVariant = {
    pending: 'secondary',
    active: 'default',
    suspended: 'destructive',
} as const;

const countCards = [
    { label: 'Services', value: props.counts.services },
    { label: 'Specialities', value: props.counts.specialities },
    { label: 'Social media links', value: props.counts.social_media },
    { label: 'Gallery images', value: props.counts.images },
];
</script>

<template>
    <ProviderLayout>
        <Head title="Provider dashboard" />

        <div class="flex flex-wrap items-center justify-between gap-4">
            <Heading
                :title="serviceProvider.name"
                description="Manage your public service provider listing"
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
            profile in the meantime.
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

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="card in countCards" :key="card.label">
                <CardHeader>
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ card.label }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-semibold">{{ card.value }}</p>
                </CardContent>
            </Card>
        </div>
    </ProviderLayout>
</template>
