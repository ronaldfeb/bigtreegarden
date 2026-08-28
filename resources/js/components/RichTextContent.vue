<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{
    content: string;
    class?: string;
}>();
</script>

<template>
    <div :class="cn('rich-text', props.class)" v-html="props.content" />
</template>
