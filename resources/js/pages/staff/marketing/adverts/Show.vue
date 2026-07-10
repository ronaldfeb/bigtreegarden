<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, edit, destroy, show } from '@/routes/staff/marketing/adverts';

const props = defineProps<{
    advert: Record<string, any>, analytics: { total_visits: number; unique_visits: number }
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Advert" />

        <StaffPageHeader
            :title="String(props.advert.title ?? props.advert.name ?? props.advert.client_name ?? 'Advert')"
            :actions="[{ label: 'Edit', href: edit(props.advert.id) }]"
        />
        
        <div class="grid gap-4 sm:grid-cols-2">
            <Card>
                <CardHeader><CardTitle>Total visits</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl">{{ analytics.total_visits }}</p></CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Unique visits</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl">{{ analytics.unique_visits }}</p></CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2">
                    <template v-for="(value, key) in props.advert" :key="key">
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
            <Form v-if="!undefined" v-bind="destroy.form(props.advert.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
