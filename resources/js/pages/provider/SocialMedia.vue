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
import { destroy, store, update } from '@/routes/provider/social-media';

type SocialMediaLink = {
    id: string;
    platform: string;
    url: string;
};

defineProps<{ socialMedia: SocialMediaLink[] }>();
</script>

<template>
    <ProviderLayout>
        <Head title="Social media" />

        <Heading
            title="Social media"
            description="Links to your social media profiles"
        />

        <Card>
            <CardHeader>
                <CardTitle>Add a link</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    reset-on-success
                    class="flex flex-wrap items-end gap-4"
                    #default="{ errors, processing }"
                >
                    <div class="grid min-w-48 gap-2">
                        <Label for="new-social-platform">Platform</Label>
                        <Input
                            id="new-social-platform"
                            name="platform"
                            type="text"
                            placeholder="e.g. Facebook"
                            required
                        />
                        <InputError :message="errors.platform" />
                    </div>
                    <div class="grid min-w-64 flex-1 gap-2">
                        <Label for="new-social-url">URL</Label>
                        <Input
                            id="new-social-url"
                            name="url"
                            type="url"
                            placeholder="https://"
                            required
                        />
                        <InputError :message="errors.url" />
                    </div>
                    <Button :disabled="processing">Add link</Button>
                </Form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Your links</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div
                    v-for="link in socialMedia"
                    :key="link.id"
                    class="flex flex-wrap items-end gap-4 border-b pb-4 last:border-b-0 last:pb-0"
                >
                    <Form
                        v-bind="update.form(link.id)"
                        class="flex flex-1 flex-wrap items-end gap-4"
                        #default="{ errors, processing }"
                    >
                        <div class="grid min-w-48 gap-2">
                            <Label :for="`social-platform-${link.id}`">Platform</Label>
                            <Input
                                :id="`social-platform-${link.id}`"
                                name="platform"
                                type="text"
                                :default-value="link.platform"
                                required
                            />
                            <InputError :message="errors.platform" />
                        </div>
                        <div class="grid min-w-64 flex-1 gap-2">
                            <Label :for="`social-url-${link.id}`">URL</Label>
                            <Input
                                :id="`social-url-${link.id}`"
                                name="url"
                                type="url"
                                :default-value="link.url"
                                required
                            />
                            <InputError :message="errors.url" />
                        </div>
                        <Button variant="outline" :disabled="processing">Save</Button>
                    </Form>

                    <Form v-bind="destroy.form(link.id)" #default="{ processing }">
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
                    v-if="socialMedia.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No social media links yet.
                </p>
            </CardContent>
        </Card>
    </ProviderLayout>
</template>
