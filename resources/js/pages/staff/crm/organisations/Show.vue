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
import { show as showContact } from '@/routes/staff/crm/contacts';
import { destroy, edit, index } from '@/routes/staff/crm/organisations';

type Option = { value: string; label: string };

const props = defineProps<{
    organisation: Record<string, any>;
    partnerStages: Option[];
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

const currentStageOrder = computed(() =>
    props.partnerStages.findIndex((stage) => stage.value === props.organisation.partner_stage),
);

const details = computed(() => [
    { label: 'Type', value: humanize(props.organisation.type) },
    { label: 'Relationship kind', value: humanize(props.organisation.relationship_kind) },
    { label: 'Status', value: humanize(props.organisation.relationship_status) },
    { label: 'Owner', value: props.organisation.relationship_owner?.user?.name ?? 'Unassigned' },
    { label: 'Email', value: props.organisation.email ?? '—' },
    { label: 'Phone', value: props.organisation.phone ?? '—' },
    { label: 'Coverage', value: props.organisation.geographic_coverage ?? '—' },
    { label: 'Last contacted', value: formatDate(props.organisation.last_contacted_at) },
    { label: 'Next review', value: formatDate(props.organisation.next_review_at) },
]);
</script>

<template>
    <StaffLayout>
        <Head :title="props.organisation.name" />

        <StaffPageHeader
            :title="props.organisation.name"
            :description="humanize(props.organisation.relationship_kind)"
            :actions="[{ label: 'Edit', href: edit(props.organisation.id) }]"
        />

        <Card v-if="props.organisation.partner_stage">
            <CardHeader><CardTitle>Partner lifecycle</CardTitle></CardHeader>
            <CardContent>
                <ol class="flex flex-wrap gap-2">
                    <li
                        v-for="(stage, i) in partnerStages"
                        :key="stage.value"
                        class="flex items-center gap-2 rounded-full border px-3 py-1 text-xs"
                        :class="i <= currentStageOrder ? 'border-brand bg-brand/10 text-brand-strong' : 'border-border text-muted-foreground'"
                    >
                        {{ stage.label }}
                    </li>
                </ol>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="item in details" :key="item.label" class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">{{ item.label }}</dt>
                        <dd class="mt-1 text-sm break-words">{{ item.value }}</dd>
                    </div>
                </dl>
                <p v-if="props.organisation.notes" class="mt-4 text-sm whitespace-pre-wrap">{{ props.organisation.notes }}</p>
            </CardContent>
        </Card>

        <Card v-if="props.organisation.service_provider">
            <CardHeader><CardTitle>Linked service provider</CardTitle></CardHeader>
            <CardContent>
                <p class="font-medium text-sm">{{ props.organisation.service_provider.name }}</p>
                <p class="mt-1 text-muted-foreground text-sm">
                    Directory status:
                    <Badge variant="outline" class="capitalize">{{ props.organisation.service_provider.status }}</Badge>
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle>Contacts</CardTitle></CardHeader>
            <CardContent>
                <div v-if="!props.organisation.contacts?.length" class="text-muted-foreground text-sm">
                    No contacts linked to this organisation.
                </div>
                <ul v-else class="divide-y divide-border">
                    <li v-for="contact in props.organisation.contacts" :key="contact.id" class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-sm">{{ contact.name }}</p>
                            <p class="text-muted-foreground text-xs">{{ contact.role_title ?? contact.email }}</p>
                        </div>
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="showContact(contact.id)">View</Link>
                        </Button>
                    </li>
                </ul>
            </CardContent>
        </Card>

        <CrmInteractionTimeline
            :interactions="props.organisation.interactions ?? []"
            subject-type="organisation"
            :subject-id="props.organisation.id"
            :interaction-types="interactionTypes"
        />

        <CrmFollowUpPanel
            :follow-ups="props.organisation.follow_ups ?? []"
            subject-type="organisation"
            :subject-id="props.organisation.id"
            :follow-up-types="followUpTypes"
            :staff-users="staffUsers"
        />

        <div class="flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to organisations</Link>
            </Button>
            <Form v-bind="destroy.form(props.organisation.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
