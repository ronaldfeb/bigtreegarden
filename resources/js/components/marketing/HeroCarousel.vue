<script setup lang="ts">
import { ChevronLeft, ChevronRight, Pause, Play } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { marketingHeroBanners } from '@/constants/marketingHeroBanners';

const banners = marketingHeroBanners;
const initialActiveIndex = Math.floor((banners.length - 1) / 2);
const activeIndex = ref(initialActiveIndex);
const isPaused = ref(false);
const prefersReducedMotion = ref(false);

const slideWidth = ref(280);
const slideGap = 20;
const slideStep = computed(() => slideWidth.value + slideGap);
const trackRef = ref<HTMLElement | null>(null);

let resizeObserver: ResizeObserver | null = null;
const autoPlayIntervalMs = 5000;

let autoPlayTimer: ReturnType<typeof setInterval> | null = null;
let motionMediaQuery: MediaQueryList | null = null;
let handleMotionPreference: ((event: MediaQueryListEvent) => void) | null = null;

const trackOffset = computed(() => {
    const centerOffset = activeIndex.value * slideStep.value + slideWidth.value / 2;

    return `translateX(calc(50% - ${centerOffset}px))`;
});

const measureSlideWidth = (): void => {
    const slide = trackRef.value?.querySelector<HTMLElement>('[data-hero-slide]');

    if (slide === undefined || slide === null) {
        return;
    }

    const measuredWidth = slide.getBoundingClientRect().width;

    if (measuredWidth > 0) {
        slideWidth.value = measuredWidth;
    }
};

const slideStyle = (index: number): Record<string, string | number> => {
    const distance = Math.abs(index - activeIndex.value);
    const scale = distance === 0 ? 1 : distance === 1 ? 0.88 : 0.76;
    const opacity = distance === 0 ? 1 : distance === 1 ? 0.92 : 0.8;
    const zIndex = distance === 0 ? 10 : Math.max(1, 8 - distance);

    return {
        transform: `scale(${scale})`,
        opacity,
        zIndex,
    };
};

const goTo = (index: number): void => {
    const total = banners.length;

    if (total === 0) {
        return;
    }

    activeIndex.value = ((index % total) + total) % total;
};

const goNext = (): void => {
    goTo(activeIndex.value + 1);
};

const goPrev = (): void => {
    goTo(activeIndex.value - 1);
};

const togglePause = (): void => {
    isPaused.value = !isPaused.value;

    if (isPaused.value) {
        stopAutoPlay();
    } else {
        startAutoPlay();
    }
};

const startAutoPlay = (): void => {
    stopAutoPlay();

    if (prefersReducedMotion.value || isPaused.value || banners.length <= 1) {
        return;
    }

    autoPlayTimer = setInterval(goNext, autoPlayIntervalMs);
};

const stopAutoPlay = (): void => {
    if (autoPlayTimer !== null) {
        clearInterval(autoPlayTimer);
        autoPlayTimer = null;
    }
};

onMounted(async () => {
    await nextTick();
    measureSlideWidth();

    if (trackRef.value !== null) {
        resizeObserver = new ResizeObserver(() => {
            measureSlideWidth();
        });
        resizeObserver.observe(trackRef.value);
    }

    motionMediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    prefersReducedMotion.value = motionMediaQuery.matches;

    handleMotionPreference = (event: MediaQueryListEvent): void => {
        prefersReducedMotion.value = event.matches;

        if (event.matches) {
            stopAutoPlay();
        } else {
            startAutoPlay();
        }
    };

    motionMediaQuery.addEventListener('change', handleMotionPreference);
    startAutoPlay();
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    resizeObserver = null;

    if (motionMediaQuery !== null && handleMotionPreference !== null) {
        motionMediaQuery.removeEventListener('change', handleMotionPreference);
    }

    stopAutoPlay();
});
</script>

<template>
    <section class="relative w-full overflow-hidden py-10 md:py-16" aria-roledescription="carousel"
        aria-label="Memorial portrait backgrounds">
        <div
            class="relative mx-auto h-[min(34rem,68vh)] max-w-7xl px-4 sm:px-6 md:h-[min(40rem,78vh)] lg:px-8"
            aria-live="polite"
        >
            <div class="flex h-full items-end justify-center">
                <div
                    ref="trackRef"
                    class="flex items-end transition-transform duration-500 ease-out motion-reduce:transition-none"
                    :style="{ transform: trackOffset }"
                >
                    <div
                        v-for="(banner, index) in banners"
                        :key="banner.src"
                        data-hero-slide
                        class="w-[15.5rem] shrink-0 origin-bottom transition-[transform,opacity] duration-500 ease-out motion-reduce:transition-none sm:w-[17.5rem] md:w-[19.5rem] lg:w-[21rem]"
                        :style="{
                            marginRight: index < banners.length - 1 ? `${slideGap}px` : '0',
                            ...slideStyle(index),
                        }"
                        :aria-hidden="index !== activeIndex"
                    >
                        <div
                            class="h-[22rem] overflow-hidden rounded-xl shadow-warm-md ring-1 ring-border/40 sm:h-[26rem] md:h-[32rem] lg:h-[36rem]"
                        >
                            <img
                                :src="banner.src"
                                :alt="banner.alt"
                                class="h-full w-full object-cover"
                                width="440"
                                height="620"
                                loading="lazy"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute right-4 bottom-2 flex gap-2 sm:right-6 lg:right-8" aria-label="Carousel controls">
                <Button type="button" variant="outline" size="icon"
                    class="size-9 rounded-full bg-background/90 shadow-sm" aria-label="Previous slide" @click="goPrev">
                    <ChevronLeft class="size-4" />
                </Button>
                <Button type="button" variant="outline" size="icon"
                    class="size-9 rounded-full bg-background/90 shadow-sm"
                    :aria-label="isPaused ? 'Play carousel' : 'Pause carousel'" @click="togglePause">
                    <Play v-if="isPaused" class="size-4" />
                    <Pause v-else class="size-4" />
                </Button>
                <Button type="button" variant="outline" size="icon"
                    class="size-9 rounded-full bg-background/90 shadow-sm" aria-label="Next slide" @click="goNext">
                    <ChevronRight class="size-4" />
                </Button>
            </div>
        </div>
    </section>
</template>
