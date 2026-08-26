<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
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
import {
    destroy,
    store,
    update,
} from '@/routes/provider/team';
import { destroy as destroyInvitation } from '@/routes/provider/team/invitations';

type Member = {
    user_id: string;
    name: string | null;
    email: string | null;
    role: string;
};

type Invitation = {
    id: string;
    email: string;
    role: string;
    expires_at: string | null;
};

type RoleOption = {
    value: string;
    label: string;
};

defineProps<{
    members: Member[];
    invitations: Invitation[];
    roles: RoleOption[];
}>();

function removeMember(userId: string): void {
    if (!confirm('Remove this team member?')) {
        return;
    }

    router.delete(destroy.url(userId));
}

function cancelInvitation(invitationId: string): void {
    if (!confirm('Cancel this invitation?')) {
        return;
    }

    router.delete(destroyInvitation.url(invitationId));
}
</script>

<template>
    <ProviderLayout>
        <Head title="Team" />

        <Heading
            title="Team"
            description="Invite colleagues to help manage memorial pages for your clients"
        />

        <Card>
            <CardHeader>
                <CardTitle>Invite a team member</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    reset-on-success
                    class="grid gap-4 sm:grid-cols-3"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-2 sm:col-span-1">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" required />
                        <InputError :message="errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            name="role"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                            required
                        >
                            <option v-for="role in roles" :key="role.value" :value="role.value">
                                {{ role.label }}
                            </option>
                        </select>
                        <InputError :message="errors.role" />
                    </div>
                    <div class="flex items-end">
                        <Button type="submit" :disabled="processing">Send invite</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Members</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div
                    v-for="member in members"
                    :key="member.user_id"
                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border p-3"
                >
                    <div>
                        <p class="font-medium">{{ member.name }}</p>
                        <p class="text-sm text-muted-foreground">{{ member.email }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Form
                            v-bind="update.form(member.user_id)"
                            class="flex items-center gap-2"
                            #default="{ processing }"
                        >
                            <select
                                name="role"
                                class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                :value="member.role"
                            >
                                <option
                                    v-for="role in roles"
                                    :key="role.value"
                                    :value="role.value"
                                >
                                    {{ role.label }}
                                </option>
                            </select>
                            <Button type="submit" variant="outline" size="sm" :disabled="processing">
                                Update
                            </Button>
                        </Form>
                        <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            @click="removeMember(member.user_id)"
                        >
                            Remove
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card v-if="invitations.length > 0">
            <CardHeader>
                <CardTitle>Pending invitations</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div
                    v-for="invitation in invitations"
                    :key="invitation.id"
                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border p-3"
                >
                    <div>
                        <p class="font-medium">{{ invitation.email }}</p>
                        <Badge variant="secondary" class="mt-1 capitalize">{{ invitation.role }}</Badge>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="cancelInvitation(invitation.id)"
                    >
                        Cancel
                    </Button>
                </div>
            </CardContent>
        </Card>
    </ProviderLayout>
</template>
