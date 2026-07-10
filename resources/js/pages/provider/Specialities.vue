<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import { destroy, store, update } from '@/routes/provider/specialities';

type Speciality = {
    id: string;
    name: string;
    sort_order: number;
};

defineProps<{ specialities: Speciality[] }>();
</script>

<template>
    <ProviderLayout>
        <Head title="Specialities" />

        <Heading
            title="Specialities"
            description="Highlight what your business specialises in"
        />

        <Card>
            <CardHeader>
                <CardTitle>Add a speciality</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    reset-on-success
                    class="flex flex-wrap items-end gap-4"
                    #default="{ errors, processing }"
                >
                    <div class="grid min-w-64 flex-1 gap-2">
                        <Label for="new-speciality-name">Name</Label>
                        <Input
                            id="new-speciality-name"
                            name="name"
                            type="text"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <Button :disabled="processing">Add speciality</Button>
                </Form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Your specialities</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div
                    v-for="speciality in specialities"
                    :key="speciality.id"
                    class="flex flex-wrap items-end gap-4 border-b pb-4 last:border-b-0 last:pb-0"
                >
                    <Form
                        v-bind="update.form(speciality.id)"
                        class="flex flex-1 flex-wrap items-end gap-4"
                        #default="{ errors, processing }"
                    >
                        <div class="grid min-w-64 flex-1 gap-2">
                            <Label :for="`speciality-name-${speciality.id}`">Name</Label>
                            <Input
                                :id="`speciality-name-${speciality.id}`"
                                name="name"
                                type="text"
                                :default-value="speciality.name"
                                required
                            />
                            <InputError :message="errors.name" />
                        </div>
                        <Button variant="outline" :disabled="processing">Save</Button>
                    </Form>

                    <Form
                        v-bind="destroy.form(speciality.id)"
                        #default="{ processing }"
                    >
                        <Button
                            variant="destructive"
                            size="sm"
                            :disabled="processing"
                        >
                            Delete
                        </Button>
                    </Form>
                </div>

                <p
                    v-if="specialities.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No specialities yet.
                </p>
            </CardContent>
        </Card>
    </ProviderLayout>
</template>
