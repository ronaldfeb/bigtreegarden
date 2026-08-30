<script setup lang="ts">
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNavbar from '@/components/marketing/MarketingNavbar.vue';
import TawkToWidget from '@/components/TawkToWidget.vue';

withDefaults(
    defineProps<{
        canRegister?: boolean;
        hideFooter?: boolean;
    }>(),
    {
        canRegister: true,
        hideFooter: false,
    },
);
</script>

<template>
    <div class="flex min-h-screen min-w-0 flex-col overflow-x-hidden bg-background text-foreground">
        <MarketingNavbar :can-register="canRegister" />
        <main class="min-w-0 flex-1">
            <slot />
        </main>
        <MarketingFooter v-if="!hideFooter" />
        <TawkToWidget />
    </div>
</template>
