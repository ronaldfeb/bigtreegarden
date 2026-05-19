<script setup lang="ts">
import { computed } from 'vue';
import { marketingSampleBanner } from '@/constants/marketingHeroBanners';

const backgroundImageStyle = computed(() => ({
    backgroundImage: `url('${marketingSampleBanner.src}')`,
}));
</script>

<template>
    <div
        class="relative mx-auto aspect-[3/4] max-h-[32rem] w-full max-w-sm overflow-hidden rounded-2xl bg-center bg-cover bg-no-repeat shadow-warm-lg ring-1 ring-border lg:max-w-none"
        :style="backgroundImageStyle"
        role="img"
        :aria-label="marketingSampleBanner.alt"
    >
        <div
            class="relative flex h-full flex-col justify-between bg-black/35 p-5 text-center text-white sm:p-6"
        >
            <div class="space-y-2">
                <p class="text-balance font-display text-2xl leading-tight sm:text-3xl">
                    Celebrating Life
                </p>
                <p class="font-serif text-lg sm:text-xl">Margaret Ndlovu</p>
                <p class="text-white/90 text-xs sm:text-sm">12 Mar 1942 – 18 Apr 2026</p>
            </div>

            <div class="mx-auto w-full max-w-[14rem] space-y-3 rounded-xl bg-white/55 p-3 text-foreground sm:max-w-xs sm:p-4">
                <div
                    class="mx-auto h-24 w-24 overflow-hidden rounded-full border border-border bg-white/85 sm:h-28 sm:w-28"
                >
                    <div
                        class="flex h-full w-full items-center justify-center bg-muted text-muted-foreground text-xs"
                    >
                        Memorial photo
                    </div>
                </div>
                <p class="text-pretty text-xs leading-relaxed sm:text-sm">
                    Beloved mother, grandmother, and friend — remembered with love and gratitude.
                </p>
            </div>

            <div class="flex flex-col items-center gap-1.5">
                <div class="rounded-xl bg-white p-2">
                    <img
                        src="/assets/marketing/sample-qr.svg"
                        alt="Sample memorial page QR code"
                        class="h-20 w-20 sm:h-24 sm:w-24"
                        width="96"
                        height="96"
                    />
                </div>
                <p class="text-[10px] uppercase tracking-wide sm:text-xs">Scan to view memorial page</p>
                <p class="max-w-[12rem] text-[10px] text-white/80 sm:text-[11px]">
                    bigtreegarden.com/memorial/example
                </p>
            </div>
        </div>
    </div>
</template>
