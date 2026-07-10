<script setup lang="ts">
defineProps<{
    partners: Array<{
        id: string;
        name: string;
        logo_path: string;
        website_url: string | null;
    }>;
}>();
</script>

<template>
    <section v-if="partners.length > 0" class="gold-rule overflow-x-hidden bg-card">
        <div class="mx-auto min-w-0 max-w-6xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
            <div class="space-y-4 text-center">
                <p class="text-eyebrow text-gold">Trusted by</p>
                <h2 class="text-balance text-heading">Our partners</h2>
            </div>

            <div
                class="mt-10 flex flex-wrap items-center justify-center gap-8 sm:gap-12"
                role="list"
            >
                <component
                    :is="partner.website_url ? 'a' : 'div'"
                    v-for="partner in partners"
                    :key="partner.id"
                    role="listitem"
                    :href="partner.website_url ?? undefined"
                    :target="partner.website_url ? '_blank' : undefined"
                    :rel="partner.website_url ? 'noopener noreferrer' : undefined"
                    class="flex h-16 w-36 items-center justify-center grayscale transition-all hover:grayscale-0"
                >
                    <img
                        :src="partner.logo_path"
                        :alt="partner.name"
                        class="max-h-12 max-w-full object-contain"
                    />
                </component>
            </div>
        </div>
    </section>
</template>
