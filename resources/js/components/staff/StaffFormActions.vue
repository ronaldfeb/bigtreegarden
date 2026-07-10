<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

withDefaults(
    defineProps<{
        cancelHref: NonNullable<InertiaLinkProps['href']>;
        processing?: boolean;
        submitLabel?: string;
        processingLabel?: string;
        destructive?: boolean;
    }>(),
    {
        processing: false,
        submitLabel: 'Save',
        processingLabel: 'Saving...',
        destructive: false,
    },
);
</script>

<template>
    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
        <Button variant="outline" as-child>
            <Link :href="cancelHref">Cancel</Link>
        </Button>
        <Button
            type="submit"
            :disabled="processing"
            :variant="destructive ? 'destructive' : 'default'"
            :class="destructive ? undefined : 'bg-brand hover:bg-brand-strong'"
        >
            {{ processing ? processingLabel : submitLabel }}
        </Button>
    </div>
</template>
