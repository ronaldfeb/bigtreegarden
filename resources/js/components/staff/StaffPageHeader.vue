<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { Component } from 'vue';
import { Button } from '@/components/ui/button';

type Action = {
    label: string;
    href?: NonNullable<InertiaLinkProps['href']>;
    icon?: Component;
};

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        createHref?: NonNullable<InertiaLinkProps['href']>;
        createLabel?: string;
        actions?: Action[];
    }>(),
    {
        description: undefined,
        createHref: undefined,
        createLabel: 'Create',
        actions: () => [],
    },
);
</script>

<template>
    <div
        class="flex flex-col gap-4 border-sidebar-border/70 border-b pb-6 sm:flex-row sm:items-start sm:justify-between"
    >
        <div class="space-y-1">
            <h1 class="font-display text-2xl text-foreground tracking-tight md:text-3xl">
                {{ title }}
            </h1>
            <p v-if="description" class="max-w-2xl text-muted-foreground text-sm">
                {{ description }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Button
                v-for="action in actions"
                :key="action.label"
                variant="outline"
                as-child
            >
                <Link v-if="action.href" :href="action.href">
                    <component :is="action.icon" v-if="action.icon" class="size-4" />
                    {{ action.label }}
                </Link>
            </Button>

            <Button v-if="createHref" as-child class="bg-brand hover:bg-brand-strong">
                <Link :href="createHref">
                    {{ createLabel }}
                </Link>
            </Button>
        </div>
    </div>
</template>
