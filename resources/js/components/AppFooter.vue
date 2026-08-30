<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useUserPortalNav } from '@/composables/useUserPortalNav';

const page = usePage<{ name: string }>();
const appName = computed(() => page.props.name);
const year = new Date().getFullYear();
const { footerNavItems } = useUserPortalNav();
</script>

<template>
    <footer class="mt-auto border-t border-sidebar-border/80">
        <div
            class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-4 py-6 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-meta text-muted-foreground">
                &copy; {{ year }} {{ appName }}
            </p>
            <nav class="flex flex-wrap gap-x-6 gap-y-2" aria-label="Legal">
                <Link
                    v-for="item in footerNavItems"
                    :key="item.title"
                    :href="item.href"
                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    {{ item.title }}
                </Link>
            </nav>
        </div>
    </footer>
</template>
