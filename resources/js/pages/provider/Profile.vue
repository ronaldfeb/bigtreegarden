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
import { update } from '@/routes/provider/profile';

type Props = {
    serviceProvider: {
        id: string;
        name: string;
        slug: string;
        status: string;
        registration_number: string | null;
        description: string | null;
        logo_path: string | null;
        cover_image_path: string | null;
        email: string;
        phone: string | null;
        website_url: string | null;
        physical_address: string | null;
        city: string | null;
        province: string | null;
    };
};

defineProps<Props>();
</script>

<template>
    <ProviderLayout>
        <Head title="Provider profile" />

        <Heading
            title="Profile"
            description="This information is shown on your public directory listing"
        />

        <Card>
            <CardHeader>
                <CardTitle>Business details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form()"
                    class="space-y-6"
                    #default="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Business name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            :default-value="serviceProvider.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="registration_number">Registration number</Label>
                        <Input
                            id="registration_number"
                            name="registration_number"
                            type="text"
                            :default-value="serviceProvider.registration_number"
                        />
                        <InputError :message="errors.registration_number" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            name="description"
                            rows="8"
                            :default-value="serviceProvider.description"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="logo">Logo</Label>
                            <img
                                v-if="serviceProvider.logo_path"
                                :src="`/storage/${serviceProvider.logo_path}`"
                                alt="Current logo"
                                class="h-20 w-20 rounded-lg border object-cover"
                            />
                            <Input id="logo" name="logo" type="file" accept="image/*" />
                            <InputError :message="errors.logo" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="cover_image">Cover image</Label>
                            <img
                                v-if="serviceProvider.cover_image_path"
                                :src="`/storage/${serviceProvider.cover_image_path}`"
                                alt="Current cover image"
                                class="h-20 w-full rounded-lg border object-cover"
                            />
                            <Input
                                id="cover_image"
                                name="cover_image"
                                type="file"
                                accept="image/*"
                            />
                            <InputError :message="errors.cover_image" />
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                :default-value="serviceProvider.email"
                                required
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input
                                id="phone"
                                name="phone"
                                type="text"
                                :default-value="serviceProvider.phone"
                            />
                            <InputError :message="errors.phone" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="website_url">Website URL</Label>
                        <Input
                            id="website_url"
                            name="website_url"
                            type="url"
                            :default-value="serviceProvider.website_url"
                        />
                        <InputError :message="errors.website_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="physical_address">Physical address</Label>
                        <Textarea
                            id="physical_address"
                            name="physical_address"
                            rows="3"
                            :default-value="serviceProvider.physical_address"
                        />
                        <InputError :message="errors.physical_address" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="city">City</Label>
                            <Input
                                id="city"
                                name="city"
                                type="text"
                                :default-value="serviceProvider.city"
                            />
                            <InputError :message="errors.city" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="province">Province</Label>
                            <Input
                                id="province"
                                name="province"
                                type="text"
                                :default-value="serviceProvider.province"
                            />
                            <InputError :message="errors.province" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Save changes</Button>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </ProviderLayout>
</template>
