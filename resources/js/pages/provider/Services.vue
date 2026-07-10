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
import { Textarea } from '@/components/ui/textarea';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import { destroy, store, update } from '@/routes/provider/services';

type Service = {
    id: string;
    name: string;
    description: string | null;
    price_from_cents: number | null;
    sort_order: number;
};

defineProps<{ services: Service[] }>();
</script>

<template>
    <ProviderLayout>
        <Head title="Services" />

        <Heading
            title="Services"
            description="The services you offer, shown on your public listing"
        />

        <Card>
            <CardHeader>
                <CardTitle>Add a service</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    reset-on-success
                    class="space-y-4"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="new-service-name">Name</Label>
                        <Input id="new-service-name" name="name" type="text" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="new-service-description">Description</Label>
                        <Textarea
                            id="new-service-description"
                            name="description"
                            rows="3"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="new-service-price">Price from (cents)</Label>
                        <Input
                            id="new-service-price"
                            name="price_from_cents"
                            type="number"
                            min="0"
                        />
                        <InputError :message="errors.price_from_cents" />
                    </div>

                    <Button :disabled="processing">Add service</Button>
                </Form>
            </CardContent>
        </Card>

        <Card v-for="service in services" :key="service.id">
            <CardHeader>
                <CardTitle>{{ service.name }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <Form
                    v-bind="update.form(service.id)"
                    class="space-y-4"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label :for="`service-name-${service.id}`">Name</Label>
                        <Input
                            :id="`service-name-${service.id}`"
                            name="name"
                            type="text"
                            :default-value="service.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label :for="`service-description-${service.id}`">
                            Description
                        </Label>
                        <Textarea
                            :id="`service-description-${service.id}`"
                            name="description"
                            rows="3"
                            :default-value="service.description"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label :for="`service-price-${service.id}`">
                                Price from (cents)
                            </Label>
                            <Input
                                :id="`service-price-${service.id}`"
                                name="price_from_cents"
                                type="number"
                                min="0"
                                :default-value="service.price_from_cents"
                            />
                            <InputError :message="errors.price_from_cents" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`service-sort-${service.id}`">Sort order</Label>
                            <Input
                                :id="`service-sort-${service.id}`"
                                name="sort_order"
                                type="number"
                                min="0"
                                :default-value="service.sort_order"
                            />
                            <InputError :message="errors.sort_order" />
                        </div>
                    </div>

                    <Button :disabled="processing">Save</Button>
                </Form>

                <Form v-bind="destroy.form(service.id)" #default="{ processing }">
                    <Button variant="destructive" size="sm" :disabled="processing">
                        Delete service
                    </Button>
                </Form>
            </CardContent>
        </Card>

        <p v-if="services.length === 0" class="text-sm text-muted-foreground">
            No services yet. Add your first service above.
        </p>
    </ProviderLayout>
</template>
