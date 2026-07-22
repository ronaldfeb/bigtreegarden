<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index as contactsIndex } from '@/routes/staff/crm/contacts';
import { index as followUpsIndex } from '@/routes/staff/crm/follow-ups';
import { index as organisationsIndex } from '@/routes/staff/crm/organisations';

type Bucket = { value: string; label: string; total: number };

const props = defineProps<{
    counts: {
        organisations: number;
        contacts: number;
        due_follow_ups: number;
        my_due_follow_ups: number;
    };
    relationshipStatuses: Bucket[];
    partnerStages: Bucket[];
    lifecycleStages: Bucket[];
    dueFollowUps: Array<Record<string, any>>;
}>();

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

function maxTotal(buckets: Bucket[]): number {
    return Math.max(1, ...buckets.map((bucket) => bucket.total));
}

const partnerMax = computed(() => maxTotal(props.partnerStages));
const lifecycleMax = computed(() => maxTotal(props.lifecycleStages));
</script>

<template>
    <StaffLayout>
        <Head title="CRM dashboard" />

        <StaffPageHeader
            title="CRM dashboard"
            description="Relationship health, pipeline progression and outstanding follow-ups."
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardHeader><CardTitle>Organisations</CardTitle></CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-brand">{{ counts.organisations }}</p>
                    <Link :href="organisationsIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View organisations</Link>
                </CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Contacts</CardTitle></CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-brand">{{ counts.contacts }}</p>
                    <Link :href="contactsIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View contacts</Link>
                </CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Follow-ups due</CardTitle></CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-gold">{{ counts.due_follow_ups }}</p>
                    <Link :href="followUpsIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View follow-ups</Link>
                </CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Assigned to me</CardTitle></CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-gold">{{ counts.my_due_follow_ups }}</p>
                    <p class="mt-2 text-muted-foreground text-sm">Due or overdue</p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader><CardTitle>Your due follow-ups</CardTitle></CardHeader>
            <CardContent>
                <div v-if="dueFollowUps.length === 0" class="text-muted-foreground text-sm">
                    Nothing due. You're all caught up.
                </div>
                <ul v-else class="divide-y divide-border">
                    <li v-for="followUp in dueFollowUps" :key="followUp.id" class="flex items-center justify-between py-3">
                        <div>
                            <component
                                :is="followUp.subject_href ? Link : 'span'"
                                :href="followUp.subject_href"
                                class="font-medium text-sm"
                                :class="followUp.subject_href ? 'text-brand hover:underline' : ''"
                            >
                                {{ followUp.title }}
                            </component>
                            <p class="text-muted-foreground text-xs">{{ followUp.type_label }} · {{ followUp.subject_label }}</p>
                        </div>
                        <span class="text-muted-foreground text-xs">Due {{ formatDate(followUp.due_at) }}</span>
                    </li>
                </ul>
            </CardContent>
        </Card>

        <div class="grid gap-4 lg:grid-cols-3">
            <Card>
                <CardHeader><CardTitle>Relationship status</CardTitle></CardHeader>
                <CardContent class="space-y-2">
                    <div v-for="bucket in relationshipStatuses" :key="bucket.value" class="flex items-center justify-between text-sm">
                        <span>{{ bucket.label }}</span>
                        <span class="font-medium">{{ bucket.total }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Partner pipeline</CardTitle></CardHeader>
                <CardContent class="space-y-3">
                    <div v-for="bucket in partnerStages" :key="bucket.value">
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ bucket.label }}</span>
                            <span class="font-medium">{{ bucket.total }}</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-muted">
                            <div class="h-2 rounded-full bg-brand" :style="{ width: `${(bucket.total / partnerMax) * 100}%` }" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Customer lifecycle</CardTitle></CardHeader>
                <CardContent class="space-y-3">
                    <div v-for="bucket in lifecycleStages" :key="bucket.value">
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ bucket.label }}</span>
                            <span class="font-medium">{{ bucket.total }}</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-muted">
                            <div class="h-2 rounded-full bg-gold" :style="{ width: `${(bucket.total / lifecycleMax) * 100}%` }" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </StaffLayout>
</template>
