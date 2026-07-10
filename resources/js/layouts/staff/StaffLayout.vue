<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import StaffSidebar from '@/components/staff/StaffSidebar.vue';
import type { BreadcrumbItem } from '@/types';
import type { StaffUser } from '@/types/staff';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<{ staffUser?: StaffUser }>();
const staffUser = computed(() => page.props.staffUser);
</script>

<template>
    <AppShell variant="sidebar">
        <StaffSidebar v-if="staffUser" :staff-user="staffUser" />
        <AppContent variant="sidebar" class="overflow-x-hidden bg-background">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <slot />
            </div>
        </AppContent>
    </AppShell>
</template>
