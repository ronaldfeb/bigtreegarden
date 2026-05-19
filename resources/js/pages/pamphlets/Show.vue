<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    pamphlet: {
        id: string;
        heading: string;
        person_full_name: string;
        status: string;
        public_slug: string;
        pamphlet_qr_code?: { image_path: string } | null;
    };
}>();

</script>

<template>
    <Head title="Pamphlet" />
    <div class="min-h-screen bg-background px-4 py-8 text-foreground">
        <div class="mx-auto w-full max-w-3xl space-y-4 p-4">
            <h1 class="font-semibold text-2xl">{{ pamphlet.heading }}</h1>
            <p class="text-muted-foreground text-sm">For {{ pamphlet.person_full_name }}</p>
            <p class="text-sm">Status: {{ pamphlet.status }}</p>

            <img
                v-if="pamphlet.pamphlet_qr_code?.image_path"
                :src="pamphlet.pamphlet_qr_code.image_path"
                alt="Pamphlet QR"
                class="h-40 w-40 rounded-md border border-border"
            />

            <div class="flex items-center gap-3">
                <Link
                    :href="`/pamphlets/${pamphlet.id}/continue`"
                    class="inline-block rounded-md bg-primary px-4 py-2 text-primary-foreground text-sm"
                >
                    Continue to payment
                </Link>
                <Link
                    v-if="pamphlet.status === 'paid' || pamphlet.status === 'published'"
                    :href="`/pamphlets/${pamphlet.id}/memorial/edit`"
                    class="inline-block rounded-md border border-border px-4 py-2 text-sm"
                >
                    Edit memorial page
                </Link>
            </div>
        </div>
    </div>
</template>
