<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { destroy as destroyInteraction, store as storeInteraction } from '@/routes/staff/crm/interactions';

type Option = { value: string; label: string };

const props = defineProps<{
    interactions: Array<Record<string, any>>;
    subjectType: 'organisation' | 'contact';
    subjectId: string;
    interactionTypes: Option[];
}>();

const selectClass = 'border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm';

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}

function labelFor(value: string): string {
    return props.interactionTypes.find((option) => option.value === value)?.label ?? value;
}
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Engagement history</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
            <Form v-bind="storeInteraction.form()" class="space-y-4 rounded-lg border border-border p-4" #default="{ errors, processing }">
                <input type="hidden" name="subject_type" :value="subjectType" />
                <input type="hidden" name="subject_id" :value="subjectId" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="interaction_type">Type</Label>
                        <select id="interaction_type" name="type" :class="selectClass" required>
                            <option v-for="option in interactionTypes" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                        <InputError :message="errors.type" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="occurred_at">When</Label>
                        <Input id="occurred_at" name="occurred_at" type="datetime-local" />
                        <InputError :message="errors.occurred_at" />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="summary">Summary</Label>
                        <Input id="summary" name="summary" type="text" required />
                        <InputError :message="errors.summary" />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="interaction_body">Details</Label>
                        <Textarea id="interaction_body" name="body" rows="2" />
                        <InputError :message="errors.body" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="processing" class="bg-brand hover:bg-brand-strong">Log interaction</Button>
                </div>
            </Form>

            <div v-if="interactions.length === 0" class="text-muted-foreground text-sm">
                No interactions logged yet.
            </div>

            <ol v-else class="space-y-4">
                <li v-for="interaction in interactions" :key="interaction.id" class="rounded-lg border border-border p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <Badge variant="outline">{{ labelFor(interaction.type) }}</Badge>
                            <span class="font-medium text-sm">{{ interaction.summary }}</span>
                        </div>
                        <span class="text-muted-foreground text-xs">{{ formatDate(interaction.occurred_at) }}</span>
                    </div>
                    <p v-if="interaction.body" class="mt-2 text-sm whitespace-pre-wrap">{{ interaction.body }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-muted-foreground text-xs">{{ interaction.staff_user?.user?.name ?? 'System' }}</span>
                        <Form v-bind="destroyInteraction.form(interaction.id)">
                            <Button type="submit" variant="ghost" size="sm">Delete</Button>
                        </Form>
                    </div>
                </li>
            </ol>
        </CardContent>
    </Card>
</template>
