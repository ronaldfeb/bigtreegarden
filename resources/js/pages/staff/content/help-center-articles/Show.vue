<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import RichTextContent from '@/components/RichTextContent.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { toDatetimeLocalInput } from '@/lib/utils';
import { index, edit, destroy } from '@/routes/staff/content/help-center-articles';

const props = defineProps<{
    article: {
        id: string;
        title: string;
        excerpt?: string | null;
        body: string;
        status?: string;
        published_at?: string | null;
        sort_order?: number | null;
        view_count?: number;
        slug?: string;
        help_center_topic_id?: string;
        topic?: { id: string; name: string } | null;
        author?: { user?: { name?: string } } | null;
        categories?: Array<{ id: string; name: string }>;
    };
}>();

function formatPublishedAt(value: string | null | undefined): string {
    const formatted = toDatetimeLocalInput(value);

    return formatted ? formatted.replace('T', ' ') : '—';
}
</script>

<template>
    <StaffLayout>
        <Head :title="props.article.title" />

        <StaffPageHeader
            :title="props.article.title"
            :actions="[{ label: 'Edit', href: edit(props.article.id) }]"
        />

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent class="space-y-6">
                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Status</dt>
                        <dd class="mt-1">
                            <Badge variant="outline" class="capitalize">
                                {{ props.article.status ?? '—' }}
                            </Badge>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Topic</dt>
                        <dd class="mt-1 text-sm">{{ props.article.topic?.name ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Published at</dt>
                        <dd class="mt-1 text-sm">{{ formatPublishedAt(props.article.published_at) }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Sort order</dt>
                        <dd class="mt-1 text-sm">{{ props.article.sort_order ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Author</dt>
                        <dd class="mt-1 text-sm">{{ props.article.author?.user?.name ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-border p-3">
                        <dt class="text-muted-foreground text-xs uppercase">Views</dt>
                        <dd class="mt-1 text-sm">{{ props.article.view_count ?? 0 }}</dd>
                    </div>
                    <div
                        v-if="props.article.categories?.length"
                        class="rounded-lg border border-border p-3 sm:col-span-2"
                    >
                        <dt class="text-muted-foreground text-xs uppercase">Categories</dt>
                        <dd class="mt-2 flex flex-wrap gap-2">
                            <Badge
                                v-for="category in props.article.categories"
                                :key="category.id"
                                variant="secondary"
                            >
                                {{ category.name }}
                            </Badge>
                        </dd>
                    </div>
                </dl>

                <div v-if="props.article.excerpt" class="space-y-2">
                    <h3 class="font-medium text-sm">Excerpt</h3>
                    <RichTextContent
                        :content="props.article.excerpt"
                        class="rounded-lg border border-border p-4 text-sm"
                    />
                </div>

                <div class="space-y-2">
                    <h3 class="font-medium text-sm">Body</h3>
                    <RichTextContent
                        :content="props.article.body"
                        class="rounded-lg border border-border p-4"
                    />
                </div>
            </CardContent>
        </Card>

        <div class="flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to list</Link>
            </Button>
            <Form v-bind="destroy.form(props.article.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
