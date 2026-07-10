<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, CreditCard, HelpCircle, LayoutGrid, Shield, Vault } from 'lucide-vue-next';
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
import { dashboard } from '@/routes';
import help from '@/routes/help';
import policies from '@/routes/policies';
import { show as subscriptionShow } from '@/routes/subscriptions';
import { index as vaultIndex } from '@/routes/vault';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
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
