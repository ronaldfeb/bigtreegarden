<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PamphletPreview from '@/components/pamphlets/PamphletPreview.vue';
import type {PamphletPreviewData} from '@/components/pamphlets/PamphletPreview.vue';
import { Button } from '@/components/ui/button';
defineProps<{
    pamphlet: PamphletPreviewData & { id: string };
}>();

const handlePrint = (): void => {
    window.print();
};
</script>

<template>
    <Head title="Print Pamphlet" />

    <div class="min-h-screen min-w-0 overflow-x-hidden bg-background px-4 py-6 text-foreground print:bg-white print:p-0">
        <div class="print-hidden mx-auto flex w-full min-w-0 max-w-xl items-center justify-between pb-4">
            <Link
                :href="`/pamphlets/${pamphlet.id}`"
                class="text-sm text-muted-foreground hover:text-foreground"
            >
                Back to pamphlet
            </Link>
            <Button type="button" @click="handlePrint">Print</Button>
        </div>

        <main class="mx-auto w-full min-w-0 max-w-xl overflow-x-hidden print:max-w-none">
            <PamphletPreview :pamphlet="pamphlet" printable />
        </main>
    </div>
</template>

<style scoped>
@media print {
    .print-hidden {
        display: none !important;
    }
}
</style>
