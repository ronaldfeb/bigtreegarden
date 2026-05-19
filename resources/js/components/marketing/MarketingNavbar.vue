<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import MarketingAuthLinks from '@/components/marketing/MarketingAuthLinks.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import { create } from '@/routes/pamphlets';

withDefaults(
    defineProps<{
        canRegister?: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage<{ name: string }>();
const appName = computed(() => page.props.name);
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b border-border/80 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
        <div class="mx-auto flex h-16 min-w-0 max-w-7xl items-center justify-between gap-2 px-4 sm:gap-4 sm:px-6 lg:px-8">
            <Link :href="home()" class="min-w-0 shrink">
                <img src="/assets/logo/logo_rect.webp" :alt="appName" class="h-8 max-w-[9rem] w-auto object-contain sm:h-9 sm:max-w-none"
                    width="180" height="45" />
            </Link>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3 md:gap-4">
                <MarketingAuthLinks :can-register="canRegister" />
                <Button as-child size="sm" class="hidden sm:inline-flex">
                    <Link :href="create()">Create memorial</Link>
                </Button>
                <Button as-child size="sm" class="sm:hidden">
                    <Link :href="create()">Begin</Link>
                </Button>
            </div>
        </div>
    </header>
</template>
