<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show, store } from '@/routes/vault';
import type { BreadcrumbItem } from '@/types';

type VaultSummary = {
    id: string;
    name: string | null;
    status: string;
    released_at: string | null;
    storage_limit_mb: number;
    storage_used_bytes: number;
    beneficiaries_count: number;
    media_count: number;
    posts_count: number;
};

defineProps<{
    personsOfInterest: Array<{
        id: string;
        display_name: string | null;
        vault: VaultSummary | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Vault',
        href: index().url,
    },
];

function createVault(personOfInterestId: string): void {
    router.post(store().url, { person_of_interest_id: personOfInterestId });
}

function formatStorage(usedBytes: number, limitMb: number): string {
    const usedMb = usedBytes / (1024 * 1024);

    return `${usedMb < 0.1 && usedBytes > 0 ? '<0.1' : usedMb.toFixed(1)} MB of ${limitMb} MB used`;
}
</script>

<template>
    <Head title="Vault" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Digital vaults</h1>
                <p class="text-sm text-muted-foreground">
                    Preserve messages, memories, and files for the people who matter,
                    released to your chosen beneficiaries when the time comes.
                </p>
            </div>

            <Card v-if="personsOfInterest.length === 0">
                <CardHeader>
                    <CardTitle>No loved ones yet</CardTitle>
                    <CardDescription>
                        Once you have added a loved one, you can create a digital vault
                        for them here.
                    </CardDescription>
                </CardHeader>
            </Card>

            <div class="grid gap-4 md:grid-cols-2">
                <Card v-for="person in personsOfInterest" :key="person.id">
                    <CardHeader>
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle>{{ person.display_name }}</CardTitle>
                            <Badge
                                v-if="person.vault"
                                :variant="person.vault.status === 'released' ? 'secondary' : 'default'"
                            >
                                {{ person.vault.status }}
                            </Badge>
                        </div>
                        <CardDescription v-if="person.vault">
                            {{ person.vault.name ?? 'Vault' }}
                        </CardDescription>
                        <CardDescription v-else>
                            No vault created yet.
                        </CardDescription>
                    </CardHeader>

                    <CardContent v-if="person.vault" class="space-y-1 text-sm">
                        <p>
                            {{ formatStorage(person.vault.storage_used_bytes, person.vault.storage_limit_mb) }}
                        </p>
                        <p class="text-muted-foreground">
                            {{ person.vault.beneficiaries_count }} beneficiaries ·
                            {{ person.vault.media_count }} files ·
                            {{ person.vault.posts_count }} posts
                        </p>
                        <p v-if="person.vault.released_at" class="text-muted-foreground">
                            Released on {{ person.vault.released_at }}
                        </p>
                    </CardContent>

                    <CardFooter>
                        <Button v-if="person.vault" as-child variant="outline">
                            <Link :href="show(person.vault.id)">Open vault</Link>
                        </Button>
                        <Button v-else @click="createVault(person.id)">
                            Create vault
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
