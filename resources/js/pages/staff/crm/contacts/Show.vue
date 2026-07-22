<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import CrmFollowUpPanel from '@/components/staff/crm/CrmFollowUpPanel.vue';
import CrmInteractionTimeline from '@/components/staff/crm/CrmInteractionTimeline.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { destroy, edit, index } from '@/routes/staff/crm/contacts';
import { show as showOrganisation } from '@/routes/staff/crm/organisations';

type Option = { value: string; label: string };

const props = defineProps<{
    contact: Record<string, any>;
    linkedActivity: Record<string, any> | null;
    interactionTypes: Option[];
    followUpTypes: Option[];
    staffUsers: Array<{ id: string; name: string }>;
}>();

function humanize(value: unknown): string {
    if (!value) {
        return '—';
    }

    return String(value)
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

const details = computed(() => [
    { label: 'Email', value: props.contact.email ?? '—' },
    { label: 'Phone', value: props.contact.phone ?? '—' },
    { label: 'Role', value: props.contact.role_title ?? '—' },
    { label: 'Owner', value: props.contact.relationship_owner?.user?.name ?? 'Unassigned' },
    { label: 'Last contacted', value: formatDate(props.contact.last_contacted_at) },
]);
</script>

<template>
    <StaffLayout>
        <Head :title="props.contact.name" />

        <StaffPageHeader
            :title="props.contact.name"
            :actions="[{ label: 'Edit', href: edit(props.contact.id) }]"
        />

        <div class="flex flex-wrap gap-2">
            <Badge variant="outline" class="capitalize">{{ humanize(props.contact.lifecycle_stage) }}</Badge>
            <Badge v-if="props.contact.journey" variant="secondary">{{ humanize(props.contact.journey) }}</Badge>
            <Badge v-if="props.contact.marketing_lead_id" variant="secondary">Converted lead</Badge>
        </div>

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="item in details" :key="item.label" class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">{{ item.label }}</dt>
                        <dd class="mt-1 text-sm break-words">{{ item.value }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Organisation</dt>
                        <dd class="mt-1 text-sm">
                            <Link
                                v-if="props.contact.organisation"
                                :href="showOrganisation(props.contact.organisation.id)"
                                class="text-brand hover:underline"
                            >
                                {{ props.contact.organisation.name }}
                            </Link>
                            <span v-else>Individual</span>
                        </dd>
                    </div>
                </dl>
                <p v-if="props.contact.notes" class="mt-4 text-sm whitespace-pre-wrap">{{ props.contact.notes }}</p>
            </CardContent>
        </Card>

        <Card v-if="linkedActivity">
            <CardHeader><CardTitle>Platform activity</CardTitle></CardHeader>
            <CardContent>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-border p-3">
                        <p class="text-muted-foreground text-xs uppercase">Memorials</p>
                        <p class="font-display text-2xl text-brand">{{ linkedActivity.persons_of_interest }}</p>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <p class="text-muted-foreground text-xs uppercase">Transactions</p>
                        <p class="font-display text-2xl text-brand">{{ linkedActivity.transactions }}</p>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <p class="text-muted-foreground text-xs uppercase">Subscriptions</p>
                        <p class="font-display text-2xl text-brand">{{ linkedActivity.subscriptions }}</p>
                    </div>
                </div>
                <p class="mt-3 text-sm">
                    <Badge :variant="linkedActivity.has_active_subscription ? 'default' : 'outline'">
                        {{ linkedActivity.has_active_subscription ? 'Active subscriber' : 'No active subscription' }}
                    </Badge>
                </p>
            </CardContent>
        </Card>

        <CrmInteractionTimeline
            :interactions="props.contact.interactions ?? []"
            subject-type="contact"
            :subject-id="props.contact.id"
            :interaction-types="interactionTypes"
        />

        <CrmFollowUpPanel
            :follow-ups="props.contact.follow_ups ?? []"
            subject-type="contact"
            :subject-id="props.contact.id"
            :follow-up-types="followUpTypes"
            :staff-users="staffUsers"
        />

        <div class="flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to contacts</Link>
            </Button>
            <Form v-bind="destroy.form(props.contact.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
