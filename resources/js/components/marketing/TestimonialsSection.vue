<script setup lang="ts">
defineProps<{
    testimonials: Array<{
        id: string;
        name: string;
        photo_path: string;
        role_or_location: string | null;
        body: string;
        rating: number | null;
    }>;
}>();
</script>

<template>
    <section v-if="testimonials.length > 0" class="overflow-x-hidden bg-background">
        <div class="mx-auto min-w-0 max-w-6xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
            <div class="space-y-4 text-center">
                <p class="text-eyebrow text-gold">Kind words</p>
                <h2 class="text-balance text-heading">What families are saying</h2>
                <p class="mx-auto max-w-2xl text-body text-pretty text-muted-foreground">
                    Stories from people who found comfort in creating a lasting memorial.
                </p>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="testimonial in testimonials"
                    :key="testimonial.id"
                    class="flex flex-col rounded-xl border border-border bg-card p-6 shadow-warm-sm"
                >
                    <div class="flex items-center gap-4">
                        <img
                            :src="testimonial.photo_path"
                            :alt="testimonial.name"
                            class="size-14 rounded-full object-cover ring-2 ring-gold-soft"
                        />
                        <div class="min-w-0 text-left">
                            <h3 class="font-semibold text-foreground">{{ testimonial.name }}</h3>
                            <p
                                v-if="testimonial.role_or_location"
                                class="text-muted-foreground text-sm"
                            >
                                {{ testimonial.role_or_location }}
                            </p>
                        </div>
                    </div>

                    <p class="mt-4 flex-1 text-body text-pretty text-muted-foreground">
                        "{{ testimonial.body }}"
                    </p>

                    <div
                        v-if="testimonial.rating"
                        class="mt-4 flex gap-0.5"
                        :aria-label="`${testimonial.rating} out of 5 stars`"
                    >
                        <span
                            v-for="star in 5"
                            :key="star"
                            class="text-sm"
                            :class="star <= (testimonial.rating ?? 0) ? 'text-gold' : 'text-muted-foreground/40'"
                        >
                            ★
                        </span>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
