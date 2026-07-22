<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cancel as cancelFollowUp, complete as completeFollowUp, store as storeFollowUp } from '@/routes/staff/crm/follow-ups';

type Option = { value: string; label: string };

const props = defineProps<{
    followUps: Array<Record<string, any>>;
    subjectType: 'organisation' | 'contact';
    subjectId: string;
    followUpTypes: Option[];
    staffUsers: Array<{ id: string; name: string }>;
}>();

const selectClass = 'border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm';

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

function isOverdue(followUp: Record<string, any>): boolean {
    return followUp.status === 'pending' && new Date(followUp.due_at).getTime() < Date.now();
}

function labelFor(value: string): string {
    return props.followUpTypes.find((option) => option.value === value)?.label ?? value;
}
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Follow-ups</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
            <Form v-bind="storeFollowUp.form()" class="space-y-4 rounded-lg border border-border p-4" #default="{ errors, processing }">
                <input type="hidden" name="subject_type" :value="subjectType" />
                <input type="hidden" name="subject_id" :value="subjectId" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="followup_title">Title</Label>
                        <Input id="followup_title" name="title" type="text" required />
                        <InputError :message="errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="followup_type">Type</Label>
                        <select id="followup_type" name="type" :class="selectClass" required>
                            <option v-for="option in followUpTypes" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                        <InputError :message="errors.type" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="followup_due">Due date</Label>
                        <Input id="followup_due" name="due_at" type="date" required />
                        <InputError :message="errors.due_at" />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="followup_assignee">Assign to</Label>
                        <select id="followup_assignee" name="assigned_staff_user_id" :class="selectClass">
                            <option value="">Me</option>
                            <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
                        </select>
                        <InputError :message="errors.assigned_staff_user_id" />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="followup_notes">Notes</Label>
                        <Textarea id="followup_notes" name="notes" rows="2" />
                        <InputError :message="errors.notes" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="processing" class="bg-brand hover:bg-brand-strong">Schedule follow-up</Button>
                </div>
            </Form>

            <div v-if="followUps.length === 0" class="text-muted-foreground text-sm">
                No follow-ups scheduled.
            </div>

            <ul v-else class="space-y-3">
                <li v-for="followUp in followUps" :key="followUp.id" class="rounded-lg border border-border p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <Badge variant="outline">{{ labelFor(followUp.type) }}</Badge>
                            <span class="font-medium text-sm">{{ followUp.title }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Badge v-if="isOverdue(followUp)" variant="destructive">Overdue</Badge>
                            <Badge v-else-if="followUp.status !== 'pending'" variant="secondary" class="capitalize">{{ followUp.status }}</Badge>
                            <span class="text-muted-foreground text-xs">Due {{ formatDate(followUp.due_at) }}</span>
                        </div>
                    </div>
                    <p v-if="followUp.notes" class="mt-2 text-sm whitespace-pre-wrap">{{ followUp.notes }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-muted-foreground text-xs">{{ followUp.assigned_staff_user?.user?.name ?? 'Unassigned' }}</span>
                        <div v-if="followUp.status === 'pending'" class="flex gap-2">
                            <Form v-bind="completeFollowUp.form(followUp.id)">
                                <Button type="submit" variant="ghost" size="sm">Complete</Button>
                            </Form>
                            <Form v-bind="cancelFollowUp.form(followUp.id)">
                                <Button type="submit" variant="ghost" size="sm">Cancel</Button>
                            </Form>
                        </div>
                    </div>
                </li>
            </ul>
        </CardContent>
    </Card>
</template>
