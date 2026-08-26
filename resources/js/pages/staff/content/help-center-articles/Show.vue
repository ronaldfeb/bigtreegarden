<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, edit, destroy } from '@/routes/staff/content/help-center-articles';
const props = defineProps<{
    article: Record<string, any>
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Help article" />

        <StaffPageHeader
            :title="String(props.article.title ?? props.article.name ?? props.article.client_name ?? 'Help article')"
            :actions="[{ label: 'Edit', href: edit(props.article.id) }]"
        />
        

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2">
                    <template v-for="(value, key) in props.article" :key="key">
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
        

        <div class="flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to list</Link>
            </Button>
            <Form v-if="!undefined" v-bind="destroy.form(props.article.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
