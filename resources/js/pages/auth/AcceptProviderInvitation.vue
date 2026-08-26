<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { store } from '@/routes/provider-invitations';

defineProps<{
    email: string;
    providerName: string | null;
    token: string;
}>();
</script>

<template>
    <AuthSplitLayout
        title="Accept invitation"
        :description="`Join ${providerName ?? 'a service provider'} on BigTreeGarden`"
        panel-title="You're almost in"
        panel-description="Set your password to access the provider portal and start collaborating with your team."
    >
        <Head title="Accept invitation" />

        <Form
            v-bind="store.form(token)"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" type="email" :model-value="email" disabled />
            </div>

            <div class="grid gap-2">
                <Label for="name">Your name</Label>
                <Input id="name" name="name" type="text" required autocomplete="name" />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput id="password" name="password" required autocomplete="new-password" />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm password</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button type="submit" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Join team
            </Button>
        </Form>
    </AuthSplitLayout>
</template>
