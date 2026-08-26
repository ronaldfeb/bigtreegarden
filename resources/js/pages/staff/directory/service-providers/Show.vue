<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { show as creditPurchaseShow } from '@/routes/staff/commerce/credit-purchases';
import { approve, destroy, edit, index, suspend } from '@/routes/staff/directory/service-providers';

const props = defineProps<{
    serviceProvider: Record<string, any>;
    members: Array<{
        user_id: string;
        name: string | null;
        email: string | null;
        role: string;
    }>;
    purchases: Array<{
        id: string;
        package_name: string;
        page_count: number;
        price_cents: number;
        payment_method: string;
        status: string;
        payment_reference: string;
    }>;
    memorials: Array<{
        id: string;
        display_name: string;
        public_slug: string;
        status: string;
    }>;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Service provider" />

        <StaffPageHeader
            :title="String(props.serviceProvider.name ?? 'Service provider')"
            :actions="[{ label: 'Edit', href: edit(String(props.serviceProvider.slug)) }]"
        />

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Status</dt>
                        <dd class="mt-1 capitalize">{{ serviceProvider.status }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Credits remaining</dt>
                        <dd class="mt-1 text-lg font-semibold">{{ serviceProvider.credits_remaining }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Registration</dt>
                        <dd class="mt-1">{{ serviceProvider.registration_number }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">VAT</dt>
                        <dd class="mt-1">{{ serviceProvider.vat_number || '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Email</dt>
                        <dd class="mt-1">{{ serviceProvider.email }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Phone</dt>
                        <dd class="mt-1">{{ serviceProvider.phone }}</dd>
                    </div>
                </dl>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle>Team members</CardTitle></CardHeader>
            <CardContent class="space-y-2">
                <div
                    v-for="member in members"
                    :key="member.user_id"
                    class="flex items-center justify-between rounded-lg border border-border p-3 text-sm"
                >
                    <div>
                        <p class="font-medium">{{ member.name }}</p>
                        <p class="text-muted-foreground">{{ member.email }}</p>
                    </div>
                    <Badge variant="secondary" class="capitalize">{{ member.role }}</Badge>
                </div>
                <p v-if="members.length === 0" class="text-sm text-muted-foreground">No members linked.</p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle>Credit purchases</CardTitle></CardHeader>
            <CardContent class="space-y-2">
                <div
                    v-for="purchase in purchases"
                    :key="purchase.id"
                    class="flex items-center justify-between rounded-lg border border-border p-3 text-sm"
                >
                    <div>
                        <p class="font-medium">{{ purchase.package_name }}</p>
                        <p class="text-muted-foreground">
                            {{ purchase.page_count }} pages · {{ purchase.payment_reference }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline" class="capitalize">{{ purchase.status.replaceAll('_', ' ') }}</Badge>
                        <Button as-child variant="outline" size="sm">
                            <Link :href="creditPurchaseShow(purchase.id)">View</Link>
                        </Button>
                    </div>
                </div>
                <p v-if="purchases.length === 0" class="text-sm text-muted-foreground">No purchases yet.</p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle>Originated memorials</CardTitle></CardHeader>
            <CardContent class="space-y-2">
                <div
                    v-for="memorial in memorials"
                    :key="memorial.id"
                    class="rounded-lg border border-border p-3 text-sm"
                >
                    <p class="font-medium">{{ memorial.display_name }}</p>
                    <p class="text-muted-foreground capitalize">{{ memorial.status }}</p>
                </div>
                <p v-if="memorials.length === 0" class="text-sm text-muted-foreground">No memorials originated yet.</p>
            </CardContent>
        </Card>

        <div class="flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to list</Link>
            </Button>
            <Form
                v-if="props.serviceProvider.status === 'pending'"
                v-bind="approve.form(String(props.serviceProvider.slug))"
            >
                <Button type="submit">Approve</Button>
            </Form>
            <Form
                v-if="props.serviceProvider.status !== 'suspended'"
                v-bind="suspend.form(String(props.serviceProvider.slug))"
            >
                <Button type="submit" variant="outline">Suspend</Button>
            </Form>
            <Form v-bind="destroy.form(String(props.serviceProvider.slug))">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
