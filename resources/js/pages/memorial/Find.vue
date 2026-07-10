<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { find } from '@/routes/memorial';

const props = defineProps<{
    memorialPages: {
        data: Array<{
            id: string;
            title: string | null;
            person_full_name: string | null;
            date_of_birth: string | null;
            date_of_passing: string | null;
            url: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    search: string;
}>();

const searchTerm = ref(props.search);

function submitSearch(): void {
    router.get(find().url, searchTerm.value ? { search: searchTerm.value } : {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function paginationLabel(label: string): string {
    return label.replace('&laquo;', '«').replace('&raquo;', '»').replace(/&hellip;/g, '…');
}

function formatDates(birth: string | null, passing: string | null): string {
    const format = (value: string | null): string | null => {
        if (!value) {
            return null;
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return null;
        }

        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    };

    const from = format(birth);
    const to = format(passing);

    if (from && to) {
        return `${from} – ${to}`;
    }

    return to ?? from ?? '';
}
</script>

<template>
    <Head title="Find a memorial" />

    <MarketingLayout>
        <section class="mx-auto min-w-0 max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="space-y-4 text-center">
                <p class="text-eyebrow text-gold">Memorials</p>
                <h1 class="text-display">Find a memorial</h1>
                <p class="mx-auto max-w-2xl text-body text-pretty text-muted-foreground">
                    Search for a loved one's memorial page to leave a tribute, join the live
                    remembrance feed, or lay flowers at the memorial site.
                </p>
            </div>

            <form
                class="mx-auto mt-8 flex w-full max-w-xl items-center gap-2"
                @submit.prevent="submitSearch"
            >
                <Input
                    v-model="searchTerm"
                    type="search"
                    name="search"
                    placeholder="Search by name…"
                    aria-label="Search memorials by name"
                />
                <Button type="submit">
                    <Search class="size-4" />
                    Search
                </Button>
            </form>

            <div
                v-if="memorialPages.data.length > 0"
                class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="memorial in memorialPages.data"
                    :key="memorial.id"
                    :href="memorial.url"
                    class="group"
                >
                    <Card class="h-full transition-shadow group-hover:shadow-warm-lg">
                        <CardHeader>
                            <CardTitle class="text-heading text-lg">
                                {{ memorial.person_full_name ?? memorial.title }}
                            </CardTitle>
                            <CardDescription>
                                {{ formatDates(memorial.date_of_birth, memorial.date_of_passing) }}
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>
            </div>

            <p v-else class="mt-10 text-center text-muted-foreground">
                <template v-if="search">
                    No memorials found for "{{ search }}". Try a different name.
                </template>
                <template v-else>No published memorials yet.</template>
            </p>

            <nav
                v-if="memorialPages.links.length > 3"
                class="mt-10 flex flex-wrap items-center justify-center gap-1"
                aria-label="Pagination"
            >
                <template v-for="(link, index) in memorialPages.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="rounded-md px-3 py-1.5 text-sm transition-colors"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'"
                    >
                        {{ paginationLabel(link.label) }}
                    </Link>
                    <span v-else class="px-3 py-1.5 text-muted-foreground/50 text-sm">
                        {{ paginationLabel(link.label) }}
                    </span>
                </template>
            </nav>
        </section>
    </MarketingLayout>
</template>
