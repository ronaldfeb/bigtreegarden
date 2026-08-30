<script setup lang="ts">
import { Clock, FileText, Image as ImageIcon, Video } from 'lucide-vue-next';

const items = [
    { icon: FileText, label: 'Letters and messages' },
    { icon: ImageIcon, label: 'Photos and albums' },
    { icon: Video, label: 'Videos and voice notes' },
    { icon: Clock, label: 'Released after passing' },
] as const;
</script>

<template>
    <div class="rounded-2xl border border-border/60 bg-background p-6 shadow-warm-lg sm:p-8">
        <p class="text-eyebrow text-gold">Timeboxed vault</p>
        <h3 class="mt-3 text-heading text-xl">Leave what matters, until it is time</h3>
        <p class="mt-2 text-body text-pretty text-muted-foreground">
            Store tributes, files, and memories for the people you love. They receive them when
            you are gone.
        </p>
        <ul class="mt-6 grid gap-4 sm:grid-cols-2" role="list">
            <li
                v-for="item in items"
                :key="item.label"
                class="flex items-center gap-3 rounded-xl bg-brand-soft/50 p-3"
            >
                <span
                    class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-soft text-brand-strong"
                    aria-hidden="true"
                >
                    <component :is="item.icon" class="size-4" />
                </span>
                <span class="text-sm font-medium">{{ item.label }}</span>
            </li>
        </ul>
    </div>
</template>
