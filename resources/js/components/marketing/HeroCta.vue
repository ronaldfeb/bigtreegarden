<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { create } from '@/routes/pamphlets';
</script>

<template>
    <div class="mx-auto w-full rounded-2xl border border-border/60 bg-card p-6 shadow-warm-lg sm:p-8">
        <p class="text-eyebrow text-gold">Eternal memories</p>

        <div class="mt-4 space-y-4">
            <h1 class="text-balance text-display text-3xl sm:text-4xl">
                A garden of memory, rooted in your story.
            </h1>
            <p class="text-body text-pretty text-muted-foreground">
                Create a private, lasting place for the people you love - and the moments
                that shaped them.
            </p>
        </div>

        <div class="mt-6 flex justify-end">
            <Button as-child size="lg">
                <Link :href="create()">Create memorial</Link>
            </Button>
        </div>
    </div>
</template>
