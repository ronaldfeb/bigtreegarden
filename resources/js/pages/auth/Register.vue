<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft, FileHeart, MessagesSquare, Vault } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { Component } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { login, register } from '@/routes';
import { store } from '@/routes/register';

type Intent = 'live' | 'vault' | 'pamphlet';

const props = defineProps<{
    intent?: Intent | null;
}>();

interface IntentOption {
    id: Intent;
    title: string;
    description: string;
    icon: Component;
    panelTitle: string;
    panelDescription: string;
}

const intentOptions: IntentOption[] = [
    {
        id: 'live',
        title: 'Post live comments',
        description: 'Join a memorial page and share tributes in the live remembrance feed.',
        icon: MessagesSquare,
        panelTitle: 'Be part of the remembrance.',
        panelDescription:
            'Share messages of comfort on the day of the service and leave flowers at the memorial site.',
    },
    {
        id: 'vault',
        title: 'Get vault access',
        description: 'Preserve photos, videos, and letters for your loved ones in a digital vault.',
        icon: Vault,
        panelTitle: 'Keep their legacy safe.',
        panelDescription:
            'A secure digital vault with beneficiaries, sealed until the moment it matters most.',
    },
    {
        id: 'pamphlet',
        title: 'Create a memorial pamphlet',
        description: 'Design a beautiful QR-coded pamphlet and memorial page for a loved one.',
        icon: FileHeart,
        panelTitle: 'Honour a life, beautifully.',
        panelDescription:
            'Create a printable pamphlet with a QR code linking to a lasting online memorial page.',
    },
];

const selectedIntent = ref<Intent | null>(props.intent ?? null);
const step = ref<'choose' | 'form'>(props.intent ? 'form' : 'choose');

const selectedOption = computed(
    () => intentOptions.find((option) => option.id === selectedIntent.value) ?? null,
);

function chooseIntent(intent: Intent): void {
    selectedIntent.value = intent;
    step.value = 'form';

    router.replace({
        url: register({ query: { intent } }).url,
        preserveState: true,
        preserveScroll: true,
    });
}

function backToChoice(): void {
    step.value = 'choose';
    selectedIntent.value = null;

    router.replace({
        url: register().url,
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthSplitLayout
        :title="step === 'choose' ? 'How would you like to start?' : 'Create an account'"
        :description="
            step === 'choose'
                ? 'Choose what brings you here and we will guide you through it'
                : (selectedOption?.title ?? 'Enter your details below to create your account')
        "
        :panel-title="selectedOption?.panelTitle"
        :panel-description="selectedOption?.panelDescription"
    >
        <Head title="Register" />

        <div v-if="step === 'choose'" class="flex flex-col gap-6">
            <div class="grid gap-3">
                <button
                    v-for="option in intentOptions"
                    :key="option.id"
                    type="button"
                    class="flex items-start gap-4 rounded-xl border border-border bg-card p-4 text-left transition-colors hover:border-brand hover:bg-brand-soft/50"
                    :data-test="`register-intent-${option.id}`"
                    @click="chooseIntent(option.id)"
                >
                    <span
                        class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-brand-strong"
                    >
                        <component :is="option.icon" class="size-5" />
                    </span>
                    <span class="space-y-1">
                        <span class="block font-medium text-foreground text-sm">
                            {{ option.title }}
                        </span>
                        <span class="block text-muted-foreground text-sm">
                            {{ option.description }}
                        </span>
                    </span>
                </button>
            </div>

            <div class="text-center text-muted-foreground text-sm">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4">Log in</TextLink>
            </div>
        </div>

        <Form
            v-else
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <input type="hidden" name="intent" :value="selectedIntent ?? ''" />

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    Create account
                </Button>
            </div>

            <div class="flex items-center justify-between text-muted-foreground text-sm">
                <button
                    type="button"
                    class="inline-flex items-center gap-1 underline underline-offset-4 hover:text-foreground"
                    data-test="register-change-intent"
                    @click="backToChoice"
                >
                    <ArrowLeft class="size-3.5" />
                    Change choice
                </button>
                <span>
                    Have an account?
                    <TextLink :href="login()" class="underline underline-offset-4" :tabindex="6">
                        Log in
                    </TextLink>
                </span>
            </div>
        </Form>
    </AuthSplitLayout>
</template>
