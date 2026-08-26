<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    CreditCard,
    FileHeart,
    Image,
    Images,
    LayoutGrid,
    Share2,
    Sparkles,
    Store,
    Users,
    Wrench,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
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
import { index as backgroundsIndex } from '@/routes/provider/backgrounds';
import { index as creditsIndex } from '@/routes/provider/credits';
import { index as imagesIndex } from '@/routes/provider/images';
import { index as memorialsIndex } from '@/routes/provider/memorials';
import { edit as profileEdit } from '@/routes/provider/profile';
import { index as servicesIndex } from '@/routes/provider/services';
import { index as socialMediaIndex } from '@/routes/provider/social-media';
import { index as specialitiesIndex } from '@/routes/provider/specialities';
import { index as teamIndex } from '@/routes/provider/team';
import type { NavItem } from '@/types';

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const page = usePage();
const membership = computed(() => page.props.serviceProviderMembership as {
    role?: string;
    service_provider?: { status?: string };
} | null);
const isOwner = computed(() => membership.value?.role === 'owner');
const isActive = computed(() => membership.value?.service_provider?.status === 'active');

const items = computed((): NavItem[] => {
    const nav: Array<NavItem & { exact?: boolean; ownerOnly?: boolean; activeOnly?: boolean }> = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid, exact: true },
        { title: 'Profile', href: profileEdit(), icon: Store },
        { title: 'Team', href: teamIndex(), icon: Users, ownerOnly: true },
        { title: 'Credits', href: creditsIndex(), icon: CreditCard, ownerOnly: true, activeOnly: true },
        { title: 'Memorials', href: memorialsIndex(), icon: FileHeart, activeOnly: true },
        { title: 'Backgrounds', href: backgroundsIndex(), icon: Images },
        { title: 'Services', href: servicesIndex(), icon: Wrench },
        { title: 'Specialities', href: specialitiesIndex(), icon: Sparkles },
        { title: 'Social media', href: socialMediaIndex(), icon: Share2 },
        { title: 'Gallery', href: imagesIndex(), icon: Image },
    ];

    return nav
        .filter((item) => !item.ownerOnly || isOwner.value)
        .filter((item) => !item.activeOnly || isActive.value)
        .map((item) => ({
            title: item.title,
            href: item.href,
            icon: item.icon,
            isActive: item.exact
                ? isCurrentUrl(item.href)
                : isCurrentOrParentUrl(item.href),
        }));
});
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
