<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { isPamphletPaid, isPamphletUnpaid } from '@/lib/pamphletStatus';
import { dashboard } from '@/routes';
import { approve, reject } from '@/routes/messages';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    pamphlets: Array<{
        id: string;
        heading: string;
        person_full_name: string;
        status: string;
        date_of_birth: string | null;
        date_of_passing: string | null;
        background_asset_path: string | null;
    }>;
    pendingMessages: Array<{
        id: string;
        author_name: string | null;
        memorial_page_title: string | null;
        context: string;
        body_excerpt: string;
        created_at: string | null;
        transaction_complete: boolean | null;
    }>;
}>();

const page = usePage<{ name: string }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <h1 class="font-semibold text-2xl">{{ page.props.name }} Dashboard</h1>
                <p class="mt-1 text-muted-foreground text-sm">
                    Manage and review all your memorial pamphlets.
                </p>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <div class="mb-4">
                    <h2 class="font-semibold text-lg">Pending messages</h2>
                    <p class="mt-1 text-muted-foreground text-sm">
                        Review messages awaiting approval on your memorial pages.
                    </p>
                </div>

                <div
                    v-if="pendingMessages.length === 0"
                    class="rounded-lg border border-dashed border-border p-8 text-center"
                >
                    <p class="font-medium text-sm">No pending messages</p>
                    <p class="mt-1 text-muted-foreground text-sm">
                        Messages posted to your memorial pages will appear here for review.
                    </p>
                </div>

                <ul v-else class="space-y-3">
                    <li
                        v-for="message in pendingMessages"
                        :key="message.id"
                        class="rounded-lg border border-border p-4"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium text-sm">{{ message.author_name ?? 'Unknown author' }}</p>
                                    <span class="rounded-full border border-border px-2 py-0.5 text-xs capitalize">
                                        {{ message.context.replace('_', ' ') }}
                                    </span>
                                    <span
                                        v-if="message.transaction_complete === false"
                                        class="rounded-full border border-border px-2 py-0.5 text-muted-foreground text-xs"
                                    >
                                        Payment incomplete
                                    </span>
                                </div>
                                <p class="text-muted-foreground text-sm">{{ message.body_excerpt }}</p>
                                <p class="text-muted-foreground text-xs">
                                    {{ message.memorial_page_title ?? '—' }} · {{ message.created_at ?? '—' }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <Form v-bind="approve.form(message.id)">
                                    <button
                                        type="submit"
                                        class="inline-flex rounded-md bg-primary px-3 py-1.5 text-primary-foreground text-sm"
                                    >
                                        Approve
                                    </button>
                                </Form>
                                <Form v-bind="reject.form(message.id)">
                                    <button
                                        type="submit"
                                        class="inline-flex rounded-md border border-border px-3 py-1.5 text-sm"
                                    >
                                        Reject
                                    </button>
                                </Form>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-semibold text-lg">Your Pamphlets</h2>
                    <Link
                        href="/pamphlets/create"
                        class="inline-flex rounded-md bg-primary px-4 py-2 text-primary-foreground text-sm"
                    >
                        Create pamphlet
                    </Link>
                </div>

                <div v-if="pamphlets.length === 0" class="rounded-lg border border-dashed border-border p-8 text-center">
                    <p class="font-medium text-sm">No pamphlets yet</p>
                    <p class="mt-1 text-muted-foreground text-sm">
                        Create your first memorial pamphlet to get started.
                    </p>
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="pamphlet in pamphlets"
                        :key="pamphlet.id"
                        class="overflow-hidden rounded-xl border border-border"
                    >
                        <div class="h-32 bg-muted">
                            <img
                                v-if="pamphlet.background_asset_path"
                                :src="`/${pamphlet.background_asset_path}`"
                                :alt="pamphlet.heading"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="space-y-2 p-4">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="line-clamp-2 font-semibold text-base">{{ pamphlet.heading }}</h3>
                                <span
                                    class="rounded-full border border-border px-2 py-0.5 text-xs capitalize"
                                    :class="isPamphletUnpaid(pamphlet.status) ? 'border-amber-200 bg-amber-50 text-amber-900' : ''"
                                >
                                    {{ isPamphletUnpaid(pamphlet.status) ? 'Pending payment' : pamphlet.status.replaceAll('_', ' ') }}
                                </span>
                            </div>
                            <p class="text-muted-foreground text-sm">{{ pamphlet.person_full_name }}</p>
                            <p class="text-muted-foreground text-xs">
                                {{ pamphlet.date_of_birth ?? '—' }} - {{ pamphlet.date_of_passing ?? '—' }}
                            </p>
                            <div class="pt-1">
                                <div class="flex items-center gap-4">
                                    <Link :href="`/pamphlets/${pamphlet.id}`" class="text-primary text-sm hover:underline">
                                        View details
                                    </Link>
                                    <Link
                                        v-if="isPamphletPaid(pamphlet.status)"
                                        :href="`/pamphlets/${pamphlet.id}/print`"
                                        class="text-primary text-sm hover:underline"
                                    >
                                        Print
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
