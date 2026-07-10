<script setup lang="ts">
import { Head, Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, show, store, update } from '@/routes/staff/directory/users';

</script>

<template>
    <StaffLayout>
        <Head title="Create user" />

        <StaffPageHeader title="Create user" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2">
    <Label for="name">Name</Label>
    <Input id="name" name="name" type="text"  required />
    <InputError :message="errors.name" />
</div>

                <div class="grid gap-2">
    <Label for="email">Email</Label>
    <Input id="email" name="email" type="email"  required />
    <InputError :message="errors.email" />
</div>

                <div class="grid gap-2">
    <Label for="phone">Phone</Label>
    <Input id="phone" name="phone" type="text"   />
    <InputError :message="errors.phone" />
</div>

                <div class="grid gap-2">
    <Label for="password">Password</Label>
    <Input id="password" name="password" type="password"  required />
    <InputError :message="errors.password" />
</div>

                <div class="grid gap-2">
    <Label for="password_confirmation">Confirm password</Label>
    <Input id="password_confirmation" name="password_confirmation" type="password"  required />
    <InputError :message="errors.password_confirmation" />
</div>

                    <StaffFormActions :cancel-href="index()" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
