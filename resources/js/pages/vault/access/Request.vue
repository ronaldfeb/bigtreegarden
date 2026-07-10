<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/vault/access';
</script>

<template>
    <Head title="Vault access" />

    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <Card>
                <CardHeader class="text-center">
                    <CardTitle>Access a vault</CardTitle>
                    <CardDescription>
                        Enter your email address and the access code you were given to
                        view the vault entrusted to you.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Form
                        v-bind="store.form()"
                        class="flex flex-col gap-6"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label for="email">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="email@example.com"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="access_code">Access code</Label>
                            <Input
                                id="access_code"
                                type="text"
                                name="access_code"
                                required
                                autocomplete="off"
                                placeholder="e.g. AB12CD34EF"
                                class="font-mono uppercase tracking-widest"
                            />
                            <InputError :message="errors.access_code" />
                        </div>

                        <Button type="submit" class="w-full" :disabled="processing">
                            View vault
                        </Button>
                    </Form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
