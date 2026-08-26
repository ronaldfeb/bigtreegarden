<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import { destroy, index } from '@/routes/provider/memorials';

defineProps<{
    pamphlet: {
        id: string;
        heading: string;
        person_full_name: string | null;
        status: string;
        public_slug: string | null;
        short_text: string;
        family_owner: { id: string; name: string; email: string } | null;
        edit_url: string;
        public_url: string;
        background_asset_path: string | null;
    };
}>();
</script>

<template>
    <ProviderLayout>
        <Head :title="pamphlet.heading" />
        <Heading :title="pamphlet.heading" :description="pamphlet.person_full_name ?? ''" />

        <Card>
            <CardHeader>
                <CardTitle>Memorial details</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3 text-sm">
                <p>{{ pamphlet.short_text }}</p>
                <p>
                    <span class="text-muted-foreground">Status:</span>
                    {{ pamphlet.status }}
                </p>
                <p v-if="pamphlet.family_owner">
                    <span class="text-muted-foreground">Family owner:</span>
                    {{ pamphlet.family_owner.name }} ({{ pamphlet.family_owner.email }})
                </p>
                <img
                    v-if="pamphlet.background_asset_path"
                    :src="pamphlet.background_asset_path"
                    alt="Background"
                    class="h-40 w-full rounded-lg object-cover"
                />
            </CardContent>
        </Card>

        <div class="flex flex-wrap gap-2">
            <Button as-child>
                <a :href="pamphlet.edit_url">Edit memorial content</a>
            </Button>
            <Button as-child variant="outline">
                <a :href="pamphlet.public_url" target="_blank" rel="noopener">View public page</a>
            </Button>
            <Form v-bind="destroy.form(pamphlet.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
            <Button as-child variant="outline">
                <Link :href="index()">Back</Link>
            </Button>
        </div>
    </ProviderLayout>
</template>
