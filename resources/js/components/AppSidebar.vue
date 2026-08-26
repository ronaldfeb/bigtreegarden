<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CreditCard, HelpCircle, LayoutGrid, Shield, ShieldCheck, Store, Vault } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import help from '@/routes/help';
import policies from '@/routes/policies';
import { dashboard as providerDashboard } from '@/routes/provider';
import { dashboard as staffDashboard } from '@/routes/staff';
import { show as subscriptionShow } from '@/routes/subscriptions';
import { index as vaultIndex } from '@/routes/vault';
import type { NavItem } from '@/types';
import type { StaffUser } from '@/types/staff';

type ServiceProviderMembership = {
    role: string;
    service_provider?: {
        id: string;
        name: string;
        slug: string;
        status: string;
        credits_remaining: number;
    } | null;
};

const page = usePage<{
    staffUser?: StaffUser | null;
    serviceProviderMembership?: ServiceProviderMembership | null;
}>();
const isStaffUser = computed(
    () => page.props.staffUser?.is_active === true,
);
const isProviderMember = computed(
    () => page.props.serviceProviderMembership != null,
);

const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Vault',
            href: vaultIndex(),
            icon: Vault,
        },
        {
            title: 'Subscription',
            href: subscriptionShow(),
            icon: CreditCard,
        },
    ];

    if (isProviderMember.value) {
        items.push({
            title: 'Provider Portal',
            href: providerDashboard(),
            icon: Store,
        });
    }

    if (isStaffUser.value) {
        items.push({
            title: 'Staff Portal',
            href: staffDashboard(),
            icon: ShieldCheck,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Help Center',
        href: help.index(),
        icon: HelpCircle,
    },
    {
        title: 'Terms of Service',
        href: policies.terms(),
        icon: BookOpen,
    },
    {
        title: 'Privacy Policy',
        href: policies.privacy(),
        icon: Shield,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
