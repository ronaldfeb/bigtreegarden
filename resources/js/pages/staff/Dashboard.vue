<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index as transactionsIndex } from '@/routes/staff/commerce/transactions';
import { index as usersIndex } from '@/routes/staff/directory/users';
import { index as leadsIndex } from '@/routes/staff/marketing/leads';
import type { StaffUser } from '@/types/staff';

defineProps<{
    counts: {
        users: number;
        persons_of_interest: number;
        memorial_pages: number;
        marketing_leads: number;
        transactions: number;
        service_providers: number;
    };
    staffUser: StaffUser;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Staff dashboard" />

        <StaffPageHeader
            title="Staff dashboard"
            :description="'Welcome back, ' + staffUser.user.name + '.'"
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Card>
                <CardHeader><CardTitle>Users</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl text-brand">{{ counts.users }}</p></CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Persons of interest</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl text-brand">{{ counts.persons_of_interest }}</p></CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Memorial pages</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl text-brand">{{ counts.memorial_pages }}</p></CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle>New marketing leads</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-gold">{{ counts.marketing_leads }}</p>
                    <Link :href="leadsIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View leads</Link>
                </CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Completed transactions</CardTitle></CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-gold">{{ counts.transactions }}</p>
                    <Link :href="transactionsIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View transactions</Link>
                </CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Service providers</CardTitle></CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-brand">{{ counts.service_providers }}</p>
                    <Link :href="usersIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View users</Link>
                </CardContent>
            </Card>
        </div>
    </StaffLayout>
</template>
