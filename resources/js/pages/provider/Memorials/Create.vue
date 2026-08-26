<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import ProviderLayout from '@/layouts/provider/ProviderLayout.vue';
import { index, store } from '@/routes/provider/memorials';

type BackgroundOption = {
    id: string;
    name: string;
    asset_path: string | null;
    collection_slug: string;
    source: 'catalogue' | 'provider';
};

const props = defineProps<{
    creditsRemaining: number;
    catalogueBackgrounds: BackgroundOption[];
    providerBackgrounds: BackgroundOption[];
}>();

const backgroundSource = ref<'catalogue' | 'provider'>(
    props.providerBackgrounds.length > 0 ? 'provider' : 'catalogue',
);
const selectedBackgroundId = ref<string | null>(
    (backgroundSource.value === 'provider'
        ? props.providerBackgrounds[0]?.id
        : props.catalogueBackgrounds[0]?.id) ?? null,
);

const availableBackgrounds = computed(() =>
    backgroundSource.value === 'provider' ? props.providerBackgrounds : props.catalogueBackgrounds,
);

function selectSource(source: 'catalogue' | 'provider'): void {
    backgroundSource.value = source;
    selectedBackgroundId.value = availableBackgrounds.value[0]?.id ?? null;
}
</script>

<template>
    <ProviderLayout>
        <Head title="Create memorial" />
        <Heading
            title="Create memorial for a client"
            :description="`This will use 1 of your ${creditsRemaining} remaining credits`"
        />

        <Form
            v-bind="store.form()"
            enctype="multipart/form-data"
            class="mx-auto max-w-3xl space-y-6"
            #default="{ errors, processing }"
        >
            <input type="hidden" name="background_source" :value="backgroundSource" />
            <input
                v-if="backgroundSource === 'catalogue'"
                type="hidden"
                name="background_id"
                :value="selectedBackgroundId ?? ''"
            />
            <input
                v-else
                type="hidden"
                name="service_provider_background_id"
                :value="selectedBackgroundId ?? ''"
            />
            <input type="hidden" name="date_format" value="d M Y" />
            <input type="hidden" name="image_shape" value="square" />
            <input type="hidden" name="image_crop_mode" value="cover" />
            <input type="hidden" name="font_family" value="Georgia" />
            <input type="hidden" name="heading_color" value="#000000" />
            <input type="hidden" name="name_color" value="#000000" />
            <input type="hidden" name="short_text_color" value="#000000" />
            <input type="hidden" name="dates_color" value="#000000" />

            <div class="grid gap-4 rounded-xl border border-border p-4 sm:grid-cols-2">
                <p class="sm:col-span-2 text-sm font-medium">Family contact (becomes memorial owner)</p>
                <div class="grid gap-2">
                    <Label for="family_contact_name">Family contact name</Label>
                    <Input id="family_contact_name" name="family_contact_name" type="text" required />
                    <InputError :message="errors.family_contact_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="family_contact_email">Family contact email</Label>
                    <Input id="family_contact_email" name="family_contact_email" type="email" required />
                    <InputError :message="errors.family_contact_email" />
                </div>
            </div>

            <div class="grid gap-4 rounded-xl border border-border p-4">
                <div class="grid gap-2">
                    <Label for="heading">Heading</Label>
                    <Input id="heading" name="heading" type="text" required />
                    <InputError :message="errors.heading" />
                </div>
                <div class="grid gap-2">
                    <Label for="person_full_name">Person's full name</Label>
                    <Input id="person_full_name" name="person_full_name" type="text" required />
                    <InputError :message="errors.person_full_name" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="date_of_birth">Date of birth</Label>
                        <Input id="date_of_birth" name="date_of_birth" type="date" required />
                        <InputError :message="errors.date_of_birth" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="date_of_passing">Date of passing</Label>
                        <Input id="date_of_passing" name="date_of_passing" type="date" required />
                        <InputError :message="errors.date_of_passing" />
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="short_text">Short text</Label>
                    <Textarea id="short_text" name="short_text" rows="4" required />
                    <InputError :message="errors.short_text" />
                </div>
                <div class="grid gap-2">
                    <Label for="image">Portrait image</Label>
                    <Input id="image" name="image" type="file" accept="image/*" required />
                    <InputError :message="errors.image" />
                </div>
            </div>

            <div class="space-y-3 rounded-xl border border-border p-4">
                <p class="text-sm font-medium">Background</p>
                <div class="flex gap-2">
                    <Button
                        type="button"
                        size="sm"
                        :variant="backgroundSource === 'catalogue' ? 'default' : 'outline'"
                        @click="selectSource('catalogue')"
                    >
                        Catalogue
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        :variant="backgroundSource === 'provider' ? 'default' : 'outline'"
                        :disabled="providerBackgrounds.length === 0"
                        @click="selectSource('provider')"
                    >
                        Your library
                    </Button>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <button
                        v-for="background in availableBackgrounds"
                        :key="background.id"
                        type="button"
                        class="overflow-hidden rounded-lg border text-left"
                        :class="selectedBackgroundId === background.id ? 'border-brand ring-2 ring-brand' : 'border-border'"
                        @click="selectedBackgroundId = background.id"
                    >
                        <img
                            v-if="background.asset_path"
                            :src="background.asset_path"
                            :alt="background.name"
                            class="h-24 w-full object-cover"
                        />
                        <span class="block p-2 text-xs">{{ background.name }}</span>
                    </button>
                </div>
                <InputError :message="errors.background_id || errors.service_provider_background_id" />
                <InputError :message="errors.credits" />
            </div>

            <div class="flex gap-2">
                <Button type="submit" :disabled="processing || !selectedBackgroundId">
                    Create memorial (use 1 credit)
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </ProviderLayout>
</template>
