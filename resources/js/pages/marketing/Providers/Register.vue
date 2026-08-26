<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/providers/register';

defineProps<{
    canRegister?: boolean;
    authenticated: boolean;
    user?: { name: string; email: string } | null;
}>();
</script>

<template>
    <AuthSplitLayout
        title="Register your funeral home"
        description="Create a service provider account to manage memorial pages for your clients"
        panel-title="Built for funeral service providers"
        panel-description="Buy memorial page credits in bulk, create pages for families, and appear in our public directory once approved."
    >
        <Head title="Register as a service provider" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div v-if="!authenticated" class="grid gap-4">
                <p class="text-sm font-medium text-foreground">Your account</p>
                <div class="grid gap-2">
                    <Label for="name">Your name</Label>
                    <Input id="name" name="name" type="text" required autocomplete="name" />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Your login email</Label>
                    <Input id="email" name="email" type="email" required autocomplete="email" />
                    <InputError :message="errors.email" />
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
            </div>

            <div v-else class="rounded-lg border border-border bg-muted/40 p-3 text-sm text-muted-foreground">
                Registering as <span class="font-medium text-foreground">{{ user?.name }}</span>
                ({{ user?.email }})
            </div>

            <div class="grid gap-4">
                <p class="text-sm font-medium text-foreground">Company details</p>
                <div class="grid gap-2">
                    <Label for="business_name">Business name</Label>
                    <Input id="business_name" name="business_name" type="text" required />
                    <InputError :message="errors.business_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="registration_number">Company registration number</Label>
                    <Input id="registration_number" name="registration_number" type="text" required />
                    <InputError :message="errors.registration_number" />
                </div>
                <div class="grid gap-2">
                    <Label for="vat_number">VAT number (optional)</Label>
                    <Input id="vat_number" name="vat_number" type="text" />
                    <InputError :message="errors.vat_number" />
                </div>
                <div class="grid gap-2">
                    <Label for="contact_phone">Contact phone number</Label>
                    <Input id="contact_phone" name="contact_phone" type="text" required />
                    <InputError :message="errors.contact_phone" />
                </div>
                <div class="grid gap-2">
                    <Label for="contact_email">Contact email address</Label>
                    <Input id="contact_email" name="contact_email" type="email" required />
                    <InputError :message="errors.contact_email" />
                </div>
            </div>

            <Button type="submit" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Submit registration
            </Button>

            <p v-if="!authenticated" class="text-center text-muted-foreground text-sm">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4">Log in</TextLink>
            </p>
            <p v-else class="text-center text-muted-foreground text-sm">
                <Link href="/providers" class="underline underline-offset-4">Back to directory</Link>
            </p>
        </Form>
    </AuthSplitLayout>
</template>
