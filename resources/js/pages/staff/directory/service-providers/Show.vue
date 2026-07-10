<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, edit, destroy, show, approve, suspend } from '@/routes/staff/directory/service-providers';

const props = defineProps<{
    serviceProvider: Record<string, any>
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Service provider" />

        <StaffPageHeader
            :title="String(props.serviceProvider.title ?? props.serviceProvider.name ?? props.serviceProvider.client_name ?? 'Service provider')"
            :actions="[{ label: 'Edit', href: edit(props.serviceProvider.id) }]"
        />
        

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2">
                    <template v-for="(value, key) in props.serviceProvider" :key="key">
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
            <Form
                v-if="props.serviceProvider.status === 'pending'"
                v-bind="approve.form(String(props.serviceProvider.slug))"
            >
                <Button type="submit">Approve</Button>
            </Form>
            <Form
                v-if="props.serviceProvider.status !== 'suspended'"
                v-bind="suspend.form(String(props.serviceProvider.slug))"
            >
                <Button type="submit" variant="outline">Suspend</Button>
            </Form>
            <Form v-if="!undefined" v-bind="destroy.form(props.serviceProvider.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
