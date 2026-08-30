<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    CalendarClock,
    Contact,
    CreditCard,
    FileText,
    Handshake,
    HelpCircle,
    Image,
    LayoutGrid,
    Megaphone,
    MessageCircle,
    MessageSquare,
    Package,
    Quote,
    Repeat,
    Shield,
    Store,
    TrendingUp,
    UserCircle,
    Users,
    Vault,
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
import { dashboard } from '@/routes/staff';
import { edit as bankDetailsEdit } from '@/routes/staff/commerce/bank-details';
import { index as creditPackagesIndex } from '@/routes/staff/commerce/credit-packages';
import { index as creditPurchasesIndex } from '@/routes/staff/commerce/credit-purchases';
import { index as discountCodesIndex } from '@/routes/staff/commerce/discount-codes';
import { index as subscriptionPackagesIndex } from '@/routes/staff/commerce/subscription-packages';
import { index as subscriptionsIndex } from '@/routes/staff/commerce/subscriptions';
import { index as transactionsIndex } from '@/routes/staff/commerce/transactions';
import { index as ambassadorsIndex } from '@/routes/staff/content/ambassadors';
import { index as blogCategoriesIndex } from '@/routes/staff/content/blog-categories';
import { index as blogsIndex } from '@/routes/staff/content/blogs';
import { index as helpArticlesIndex } from '@/routes/staff/content/help-center-articles';
import { index as helpCategoriesIndex } from '@/routes/staff/content/help-center-categories';
import { index as helpTopicsIndex } from '@/routes/staff/content/help-center-topics';
import { index as pamphletCollectionsIndex } from '@/routes/staff/content/pamphlet-background-collections';
import { index as pamphletBackgroundsIndex } from '@/routes/staff/content/pamphlet-backgrounds';
import { index as partnersIndex } from '@/routes/staff/content/partners';
import { index as policiesIndex } from '@/routes/staff/content/policies';
import { index as testimonialsIndex } from '@/routes/staff/content/testimonials';
import { dashboard as crmDashboard } from '@/routes/staff/crm';
import { index as crmContactsIndex } from '@/routes/staff/crm/contacts';
import { index as crmFollowUpsIndex } from '@/routes/staff/crm/follow-ups';
import { index as crmOrganisationsIndex } from '@/routes/staff/crm/organisations';
import { index as memorialPageMessagesIndex } from '@/routes/staff/directory/memorial-page-messages';
import { index as personsIndex } from '@/routes/staff/directory/persons-of-interest';
import { index as serviceProvidersIndex } from '@/routes/staff/directory/service-providers';
import { index as usersIndex } from '@/routes/staff/directory/users';
import { index as vaultsIndex } from '@/routes/staff/directory/vaults';
import { index as advertsIndex } from '@/routes/staff/marketing/adverts';
import { index as leadsIndex } from '@/routes/staff/marketing/leads';
import type { NavItem } from '@/types';
import type { StaffRole, StaffUser } from '@/types/staff';

type StaffNavItem = NavItem & {
    external?: boolean;
};

type NavSection = {
    title: string;
    roles: StaffRole[];
    items: StaffNavItem[];
};

const props = defineProps<{
    staffUser: StaffUser;
}>();

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const TAWK_DASHBOARD_URL = 'https://dashboard.tawk.to/#/dashboard';

const sections = computed((): NavSection[] => {
    const role = props.staffUser.role;

    const allSections: NavSection[] = [
        {
            title: 'Overview',
            roles: ['admin', 'marketing', 'content', 'support'],
            items: [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                    icon: LayoutGrid,
                },
                {
                    title: 'Live chat',
                    href: TAWK_DASHBOARD_URL,
                    icon: MessageCircle,
                    external: true,
                },
            ],
        },
        {
            title: 'Marketing',
            roles: ['admin', 'marketing'],
            items: [
                { title: 'Adverts', href: advertsIndex(), icon: Megaphone },
                { title: 'Leads', href: leadsIndex(), icon: Users },
            ],
        },
        {
            title: 'CRM',
            roles: ['admin', 'marketing'],
            items: [
                { title: 'CRM dashboard', href: crmDashboard(), icon: TrendingUp },
                { title: 'Organisations', href: crmOrganisationsIndex(), icon: Building2 },
                { title: 'Contacts', href: crmContactsIndex(), icon: Contact },
                { title: 'Follow-ups', href: crmFollowUpsIndex(), icon: CalendarClock },
            ],
        },
        {
            title: 'Content',
            roles: ['admin', 'content'],
            items: [
                { title: 'Blogs', href: blogsIndex(), icon: FileText },
                { title: 'Blog categories', href: blogCategoriesIndex(), icon: BookOpen },
                { title: 'Help topics', href: helpTopicsIndex(), icon: HelpCircle },
                { title: 'Help categories', href: helpCategoriesIndex(), icon: HelpCircle },
                { title: 'Help articles', href: helpArticlesIndex(), icon: FileText },
                { title: 'Policies', href: policiesIndex(), icon: Shield },
                { title: 'Testimonials', href: testimonialsIndex(), icon: Quote },
                { title: 'Partners', href: partnersIndex(), icon: Handshake },
                { title: 'Ambassadors', href: ambassadorsIndex(), icon: UserCircle },
                {
                    title: 'Pamphlet collections',
                    href: pamphletCollectionsIndex(),
                    icon: Image,
                },
                {
                    title: 'Pamphlet backgrounds',
                    href: pamphletBackgroundsIndex(),
                    icon: Image,
                },
            ],
        },
        {
            title: 'Commerce',
            roles: ['admin'],
            items: [
                {
                    title: 'Subscription packages',
                    href: subscriptionPackagesIndex(),
                    icon: Package,
                },
                {
                    title: 'Discount codes',
                    href: discountCodesIndex(),
                    icon: CreditCard,
                },
                {
                    title: 'Credit packages',
                    href: creditPackagesIndex(),
                    icon: Package,
                },
                {
                    title: 'Bank details',
                    href: bankDetailsEdit(),
                    icon: CreditCard,
                },
                { title: 'Transactions', href: transactionsIndex(), icon: CreditCard },
                { title: 'Subscriptions', href: subscriptionsIndex(), icon: Repeat },
            ],
        },
        {
            title: 'Directory',
            roles: ['admin', 'support'],
            items: [
                {
                    title: 'Service providers',
                    href: serviceProvidersIndex(),
                    icon: Store,
                },
                {
                    title: 'Credit purchases',
                    href: creditPurchasesIndex(),
                    icon: CreditCard,
                },
                { title: 'Users', href: usersIndex(), icon: Users },
                {
                    title: 'Persons of interest',
                    href: personsIndex(),
                    icon: UserCircle,
                },
                {
                    title: 'Memorial messages',
                    href: memorialPageMessagesIndex(),
                    icon: MessageSquare,
                },
                { title: 'Vaults', href: vaultsIndex(), icon: Vault },
            ],
        },
    ];

    return allSections
        .filter((section) => section.roles.includes(role))
        .map((section) => ({
            ...section,
            items: section.items.map((item) => ({
                ...item,
                isActive: item.external ? false : isCurrentOrParentUrl(item.href),
            })),
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
            <SidebarGroup
                v-for="section in sections"
                :key="section.title"
                class="px-2 py-0"
            >
                <SidebarGroupLabel class="text-brand-strong">
                    {{ section.title }}
                </SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in section.items" :key="item.title">
                        <SidebarMenuButton
                            as-child
                            :is-active="item.isActive ?? isCurrentUrl(item.href)"
                            :tooltip="item.title"
                        >
                            <a
                                v-if="item.external"
                                :href="typeof item.href === 'string' ? item.href : item.href.url"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </a>
                            <Link v-else :href="item.href">
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
