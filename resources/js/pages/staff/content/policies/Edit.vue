<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { policyTypeLabel, toDatetimeLocalInput } from '@/lib/utils';
import { show, update } from '@/routes/staff/content/policies';

const props = defineProps<{
    policy: {
        id: string;
        type: string;
        title: string;
        body: string;
        version: string | null;
        published_at: string | null;
    };
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit policy" />

        <StaffPageHeader title="Edit policy" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(props.policy.id)"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label>Type</Label>
                        <p class="text-sm text-muted-foreground">
                            {{ policyTypeLabel(props.policy.type) }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Policy type is fixed because it maps to a public page URL.
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            name="title"
                            type="text"
                            :default-value="props.policy.title"
                            required
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="body">Body</Label>
                        <RichTextEditor
                            id="body"
                            name="body"
                            :default-value="props.policy.body"
                        />
                        <InputError :message="errors.body" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="version">Version</Label>
                        <Input
                            id="version"
                            name="version"
                            type="text"
                            :default-value="props.policy.version ?? ''"
                        />
                        <InputError :message="errors.version" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="published_at">Published at</Label>
                        <Input
                            id="published_at"
                            name="published_at"
                            type="datetime-local"
                            :default-value="toDatetimeLocalInput(props.policy.published_at)"
                        />
                        <InputError :message="errors.published_at" />
                    </div>

                    <StaffFormActions
                        :cancel-href="show(props.policy.id)"
                        :processing="processing"
                    />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
