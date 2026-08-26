<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import { reactive } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import { index, show } from '@/routes/providers';

type Provider = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    logo_path: string | null;
    city: string | null;
    province: string | null;
    specialities: Array<{
        id: string;
        name: string;
    }>;
};

type PaginatedProviders = {
    data: Provider[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = withDefaults(
    defineProps<{
        canRegister?: boolean;
        providers?: PaginatedProviders;
        filters?: {
            name: string;
            city: string;
            province: string;
        };
        provinces?: string[];
        registerUrl?: string;
    }>(),
    {
        canRegister: true,
        providers: () => ({
            data: [],
            links: [],
            current_page: 1,
            last_page: 1,
        }),
        filters: () => ({ name: '', city: '', province: '' }),
        provinces: () => [],
        registerUrl: '/providers/register',
    },
);

const form = reactive({
    name: props.filters.name,
    city: props.filters.city,
    province: props.filters.province,
});

function search(): void {
    router.get(
        index.url({
            query: {
                name: form.name || undefined,
                city: form.city || undefined,
                province: form.province || undefined,
            },
        }),
        {},
        { preserveState: true, replace: true },
    );
}

function clearFilters(): void {
    form.name = '';
    form.city = '';
    form.province = '';
    search();
}
</script>

<template>
    <Head title="Service Providers" />

    <MarketingLayout :can-register="canRegister">
        <section class="mx-auto min-w-0 max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="space-y-4">
                    <p class="text-eyebrow text-gold">Directory</p>
                    <h1 class="text-display">Funeral service providers</h1>
                    <p class="max-w-2xl text-body text-pretty text-muted-foreground">
                        Find trusted professionals who can help your family plan a meaningful farewell.
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="registerUrl">Are you a funeral service provider? Register</Link>
                </Button>
            </div>

            <form
                class="mt-8 grid gap-4 rounded-xl border border-border bg-card p-4 sm:grid-cols-4"
                @submit.prevent="search"
            >
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" type="text" placeholder="Search by name" />
                </div>
                <div class="grid gap-2">
                    <Label for="city">City</Label>
                    <Input id="city" v-model="form.city" type="text" placeholder="City" />
                </div>
                <div class="grid gap-2">
                    <Label for="province">Province</Label>
                    <select
                        id="province"
                        v-model="form.province"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                    >
                        <option value="">All provinces</option>
                        <option v-for="province in provinces" :key="province" :value="province">
                            {{ province }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <Button type="submit">Search</Button>
                    <Button type="button" variant="outline" @click="clearFilters">Clear</Button>
                </div>
            </form>

            <div
                v-if="providers.data.length > 0"
                class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="provider in providers.data"
                    :key="provider.id"
                    :href="show.url(provider.slug)"
                    class="group"
                >
                    <Card class="h-full transition-shadow group-hover:shadow-warm-lg">
                        <CardHeader class="flex flex-row items-start gap-4">
                            <div
                                v-if="provider.logo_path"
                                class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border bg-background"
                            >
                                <img
                                    :src="provider.logo_path"
                                    :alt="provider.name"
                                    class="max-h-10 max-w-10 object-contain"
                                />
                            </div>
                            <div class="min-w-0">
                                <CardTitle class="text-heading text-lg group-hover:text-brand-strong">
                                    {{ provider.name }}
                                </CardTitle>
                                <CardDescription
                                    v-if="provider.city || provider.province"
                                    class="mt-1 flex items-center gap-1"
                                >
                                    <MapPin class="size-3.5 shrink-0" />
                                    <span>
                                        {{ [provider.city, provider.province].filter(Boolean).join(', ') }}
                                    </span>
                                </CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p
                                v-if="provider.description"
                                class="line-clamp-3 text-muted-foreground text-sm"
                            >
                                {{ provider.description }}
                            </p>
                            <div
                                v-if="provider.specialities.length > 0"
                                class="mt-3 flex flex-wrap gap-1.5"
                            >
                                <span
                                    v-for="speciality in provider.specialities.slice(0, 3)"
                                    :key="speciality.id"
                                    class="rounded-full bg-brand-soft px-2.5 py-0.5 text-brand-strong text-xs"
                                >
                                    {{ speciality.name }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <p v-else class="mt-10 text-muted-foreground">No providers match your search.</p>

            <div
                v-if="providers.last_page > 1"
                class="mt-8 flex flex-wrap justify-center gap-2"
            >
                <template v-for="(link, index) in providers.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-md border px-3 py-1 text-sm"
                        :class="link.active ? 'border-brand bg-brand-soft text-brand-strong' : 'border-border'"
                    >
                        <span v-html="link.label" />
                    </Link>
                    <span
                        v-else
                        class="rounded-md border border-border px-3 py-1 text-sm text-muted-foreground"
                        v-html="link.label"
                    />
                </template>
            </div>
        </section>
    </MarketingLayout>
</template>
