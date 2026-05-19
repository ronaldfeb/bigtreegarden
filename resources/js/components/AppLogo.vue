<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage<{ name: string }>();
</script>

<template>
    <div class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-md">
        <img src="/assets/logo/logo_tree.webp" :alt="page.props.name"
            class="h-full w-full object-contain group-data-[collapsible=icon]:hidden" />
        <img src="/assets/logo/logo_tree.webp" :alt="page.props.name"
            class="hidden h-full w-full object-contain group-data-[collapsible=icon]:block" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm group-data-[collapsible=icon]:hidden">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ page.props.name }}</span>
    </div>
</template>
