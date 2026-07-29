<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import MarketingAuthLinks from '@/components/marketing/MarketingAuthLinks.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { home, pricing } from '@/routes';
import blog from '@/routes/blog';
import help from '@/routes/help';
import { create } from '@/routes/pamphlets';
import providers from '@/routes/providers';

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
const mobileMenuOpen = ref(false);

const navLinks = [
    { label: 'Pricing', href: pricing() },
    { label: 'Blog', href: blog.index() },
    { label: 'Help Center', href: help.index() },
    { label: 'Service Providers', href: providers.index() },
];

const isActive = (href: { url: string }) => page.url.startsWith(href.url);
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b border-border/80 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
        <div
            class="mx-auto flex h-16 min-w-0 max-w-7xl items-center justify-between gap-2 px-4 sm:gap-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-6">
                <Link :href="home()" class="min-w-0 shrink">
                    <img src="/assets/logo/btg_logo_black.svg" :alt="appName"
                        class="h-8 max-w-[9rem] w-auto object-contain sm:h-9 sm:max-w-none" width="180" height="45" />
                </Link>

                <nav class="hidden items-center gap-1 lg:flex" aria-label="Main navigation">
                    <Link v-for="link in navLinks" :key="link.label" :href="link.href"
                        class="rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                        :class="isActive(link.href) ? 'text-foreground' : 'text-muted-foreground'">
                        {{ link.label }}
                    </Link>
                </nav>
            </div>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3 md:gap-4">
                <MarketingAuthLinks :can-register="canRegister" />
                <Button as-child size="sm" class="hidden sm:inline-flex">
                    <Link :href="create()">Create memorial</Link>
                </Button>
                <Button as-child size="sm" class="sm:hidden">
                    <Link :href="create()">Create memorial</Link>
                </Button>

                <Sheet v-model:open="mobileMenuOpen">
                    <SheetTrigger as-child>
                        <Button variant="ghost" size="icon" class="lg:hidden" aria-label="Open menu">
                            <Menu class="size-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-72">
                        <SheetHeader>
                            <SheetTitle class="text-left">{{ appName }}</SheetTitle>
                        </SheetHeader>
                        <nav class="flex flex-col gap-1 px-4" aria-label="Mobile navigation">
                            <Link v-for="link in navLinks" :key="link.label" :href="link.href"
                                class="rounded-md px-3 py-2.5 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                                :class="isActive(link.href) ? 'bg-accent text-foreground' : 'text-muted-foreground'"
                                @click="mobileMenuOpen = false">
                                {{ link.label }}
                            </Link>
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    </header>
</template>
