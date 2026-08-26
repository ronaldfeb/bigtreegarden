<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import { index as creditsIndex } from '@/routes/provider/credits';
import { create, destroy, show } from '@/routes/provider/memorials';

type Memorial = {
    id: string;
    heading: string;
    person_full_name: string | null;
    status: string;
    public_slug: string | null;
    created_at: string | null;
    edit_url: string;
    public_url: string;
};

defineProps<{
    memorials: {
        data: Memorial[];
    };
    creditsRemaining: number;
}>();

function deleteMemorial(id: string): void {
    if (!confirm('Delete this memorial page? Credits are not refunded.')) {
        return;
    }

    router.delete(destroy.url(id));
}
</script>

<template>
    <ProviderLayout>
        <Head title="Memorials" />
        <div class="flex flex-wrap items-center justify-between gap-4">
            <Heading
                title="Client memorials"
                description="Create and manage memorial pages for your clients using your credits"
            />
            <div class="flex items-center gap-2">
                <Badge variant="secondary">{{ creditsRemaining }} credits</Badge>
                <Button as-child variant="outline">
                    <Link :href="creditsIndex()">Buy credits</Link>
                </Button>
                <Button as-child :disabled="creditsRemaining < 1">
                    <Link :href="create()">Create memorial</Link>
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Memorial pages</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div
                    v-for="memorial in memorials.data"
                    :key="memorial.id"
                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border p-3"
                >
                    <div>
                        <p class="font-medium">{{ memorial.heading }}</p>
                        <p class="text-sm text-muted-foreground">{{ memorial.person_full_name }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Button as-child variant="outline" size="sm">
                            <Link :href="show.url(memorial.id)">View</Link>
                        </Button>
                        <Button as-child variant="outline" size="sm">
                            <a :href="memorial.edit_url">Edit</a>
                        </Button>
                        <Button as-child variant="outline" size="sm">
                            <a :href="memorial.public_url" target="_blank" rel="noopener">Public</a>
                        </Button>
                        <Button type="button" variant="destructive" size="sm" @click="deleteMemorial(memorial.id)">
                            Delete
                        </Button>
                    </div>
                </div>
                <p v-if="memorials.data.length === 0" class="text-sm text-muted-foreground">
                    No memorials created yet.
                </p>
            </CardContent>
        </Card>
    </ProviderLayout>
</template>
