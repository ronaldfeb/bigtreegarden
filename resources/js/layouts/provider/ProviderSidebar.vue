<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Image,
    LayoutGrid,
    Share2,
    Sparkles,
    Store,
    Wrench,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes/provider';
import { index as imagesIndex } from '@/routes/provider/images';
import { edit as profileEdit } from '@/routes/provider/profile';
import { index as servicesIndex } from '@/routes/provider/services';
import { index as socialMediaIndex } from '@/routes/provider/social-media';
import { index as specialitiesIndex } from '@/routes/provider/specialities';
import type { NavItem } from '@/types';

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const page = usePage();
const userName = computed(() => page.props.auth.user.name);

const items = computed((): NavItem[] =>
    [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid, exact: true },
        { title: 'Profile', href: profileEdit(), icon: Store },
        { title: 'Services', href: servicesIndex(), icon: Wrench },
        { title: 'Specialities', href: specialitiesIndex(), icon: Sparkles },
        { title: 'Social media', href: socialMediaIndex(), icon: Share2 },
        { title: 'Gallery', href: imagesIndex(), icon: Image },
    ].map((item) => ({
        ...item,
        isActive: item.exact
            ? isCurrentUrl(item.href)
            : isCurrentOrParentUrl(item.href),
    })),
);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-sidebar-border bg-sidebar">
        <SidebarHeader class="border-sidebar-border border-b">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()" class="text-sidebar-foreground">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel class="text-brand-strong">
                    Provider portal
                </SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in items" :key="item.title">
                        <SidebarMenuButton
                            as-child
                            :is-active="item.isActive"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="border-sidebar-border border-t">
            <div class="px-2 py-2 text-sidebar-foreground text-xs">
                <p class="truncate font-medium">{{ userName }}</p>
                <p class="truncate text-muted-foreground">Service provider member</p>
            </div>
        </SidebarFooter>
    </Sidebar>
</template>
