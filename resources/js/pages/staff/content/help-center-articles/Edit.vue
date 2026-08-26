<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { show, update } from '@/routes/staff/content/help-center-articles';
defineProps<{
    article: Record<string, unknown>;
    topics: Array<{ id: string; name: string }>;
    categories: Array<{ id: string; name: string }>;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Edit help article" />

        <StaffPageHeader title="Edit help article" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(article.id)"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="help_center_topic_id">Topic</Label>
    <select id="help_center_topic_id" name="help_center_topic_id" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="">Select topic</option>
        <option
            v-for="topic in topics"
            :key="topic.id"
            :value="topic.id"
            :selected="topic.id === article.help_center_topic_id"
        >
            {{ topic.name }}
        </option>
    </select>
    <InputError :message="errors.help_center_topic_id" />
</div>

                <div class="grid gap-2">
    <Label for="title">Title</Label>
    <Input id="title" name="title" type="text" :default-value="article.title" required />
    <InputError :message="errors.title" />
</div>

                <div class="grid gap-2">
    <Label for="excerpt">Excerpt</Label>
    <Textarea id="excerpt" name="excerpt" rows="8" :default-value="article.excerpt"  />
    <InputError :message="errors.excerpt" />
</div>

                <div class="grid gap-2">
    <Label for="body">Body</Label>
    <Textarea id="body" name="body" rows="8" :default-value="article.body" required />
    <InputError :message="errors.body" />
</div>

                <div class="grid gap-2">
    <Label for="status">Status</Label>
    <select id="status" name="status" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" required>
        <option value="draft">Draft</option>
            <option value="published">Published</option>
    </select>
    <InputError :message="errors.status" />
</div>

                <div class="grid gap-2">
    <Label for="published_at">Published at</Label>
    <Input id="published_at" name="published_at" type="datetime-local" :default-value="article.published_at"  />
    <InputError :message="errors.published_at" />
</div>

                <div class="grid gap-2">
    <Label for="sort_order">Sort order</Label>
    <Input id="sort_order" name="sort_order" type="number" :default-value="article.sort_order"  />
    <InputError :message="errors.sort_order" />
</div>

                    <StaffFormActions :cancel-href="show(article.id)" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
