<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, edit, destroy, show } from '@/routes/staff/marketing/leads';
import { store as storeNote, destroy as destroyNote } from '@/routes/staff/marketing/leads/notes';

const props = defineProps<{
    lead: Record<string, any>
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Lead" />

        <StaffPageHeader
            :title="String(props.lead.title ?? props.lead.name ?? props.lead.client_name ?? 'Lead')"
            :actions="[{ label: 'Edit', href: edit(props.lead.id) }]"
        />
        

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2">
                    <template v-for="(value, key) in props.lead" :key="key">
                        <div
                            v-if="value !== null && typeof value !== 'object'"
                            class="rounded-lg border border-border p-3"
                        >
                            <dt class="text-muted-foreground text-xs uppercase">{{ key }}</dt>
                            <dd class="mt-1 text-sm break-words">{{ value }}</dd>
                        </div>
                    </template>
                </dl>
            </CardContent>
        </Card>
        
        <Card v-if="Array.isArray(lead.notes) && lead.notes.length">
            <CardHeader><CardTitle>Notes</CardTitle></CardHeader>
            <CardContent class="space-y-4">
                <div v-for="note in lead.notes" :key="note.id" class="rounded-lg border border-border p-4">
                    <p class="text-sm whitespace-pre-wrap">{{ note.body }}</p>
                    <p class="mt-2 text-muted-foreground text-xs">{{ note.staff_user?.user?.name }}</p>
                    <Form v-bind="destroyNote.form(lead.id, note.id)" class="mt-2">
                        <Button type="submit" variant="destructive" size="sm">Delete note</Button>
                    </Form>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle>Add note</CardTitle></CardHeader>
            <CardContent>
                <Form v-bind="storeNote.form(lead.id)" class="space-y-4" #default="{ errors, processing }">
                    <div class="grid gap-2">
                        <Label for="body">Note</Label>
                        <Textarea id="body" name="body" rows="4" required />
                        <InputError :message="errors.body" />
                    </div>
                    <StaffFormActions :cancel-href="show(lead.id)" :processing="processing" submit-label="Add note" />
                </Form>
            </CardContent>
        </Card>

        <div class="flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to list</Link>
            </Button>
            <Form v-if="!undefined" v-bind="destroy.form(props.lead.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
