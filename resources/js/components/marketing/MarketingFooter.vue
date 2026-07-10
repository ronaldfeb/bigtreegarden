<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { home, pricing } from '@/routes';
import blog from '@/routes/blog';
import help from '@/routes/help';
import { create } from '@/routes/pamphlets';
import policies from '@/routes/policies';
import providers from '@/routes/providers';

const page = usePage<{ name: string }>();
const appName = computed(() => page.props.name);
const year = new Date().getFullYear();

const columns = [
    {
        heading: 'Platform',
        links: [
            { label: 'Home', href: home() },
            { label: 'Create a memorial', href: create() },
            { label: 'Pricing', href: pricing() },
            { label: 'Service providers', href: providers.index() },
        ],
    },
    {
        heading: 'Resources',
        links: [
            { label: 'Blog', href: blog.index() },
            { label: 'Help Center', href: help.index() },
        ],
    },
    {
        heading: 'Company',
        links: [
            { label: 'About us', href: policies.about() },
            { label: 'Terms of service', href: policies.terms() },
            { label: 'Privacy policy', href: policies.privacy() },
        ],
    },
];
</script>

<template>
    <footer class="gold-rule">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <Link :href="home()">
                        <img src="/assets/logo/logo_rect.webp" :alt="appName" class="h-9 w-auto object-contain"
                            width="180" height="45" />
                    </Link>
                    <p class="mt-4 max-w-xs text-sm text-muted-foreground">
                        Honouring lives, preserving legacies. Memorial pages your family can visit forever.
                    </p>
                </div>

                <div v-for="column in columns" :key="column.heading">
                    <h3 class="text-sm font-semibold text-foreground">{{ column.heading }}</h3>
                    <ul class="mt-4 space-y-2.5">
                        <li v-for="link in column.links" :key="link.label">
                            <Link :href="link.href"
                                class="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 border-t border-border/60 pt-6 text-center">
                <p class="text-meta text-muted-foreground">
                    &copy; {{ year }} {{ appName }}
                </p>
            </div>
        </div>
    </footer>
</template>
