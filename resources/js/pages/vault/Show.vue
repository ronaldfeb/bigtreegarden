<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, release } from '@/routes/vault';
import {
    destroy as destroyBeneficiary,
    store as storeBeneficiary,
    update as updateBeneficiary,
} from '@/routes/vault/beneficiaries';
import {
    destroy as destroyMedia,
    store as storeMedia,
} from '@/routes/vault/media';
import {
    destroy as destroyPost,
    store as storePost,
    update as updatePost,
} from '@/routes/vault/posts';
import type { BreadcrumbItem } from '@/types';

type Beneficiary = {
    id: string;
    type: string;
    full_name: string;
    email: string;
    contact_number: string;
    physical_address: string;
    access_code_hint: string | null;
    first_accessed_at: string | null;
};

type Media = {
    id: string;
    type: string;
    title: string | null;
    url: string;
    mime_type: string;
    file_size_bytes: number;
    created_at: string | null;
};

type Post = {
    id: string;
    title: string | null;
    body: string;
    visibility: string;
    beneficiary_ids: string[];
    created_at: string | null;
};

const props = defineProps<{
    vault: {
        id: string;
        name: string | null;
        status: string;
        released_at: string | null;
        storage_limit_mb: number;
        storage_used_bytes: number;
        person_display_name: string | null;
        beneficiaries: Beneficiary[];
        media: Media[];
        posts: Post[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vault', href: index().url },
    { title: props.vault.name ?? 'Vault', href: '#' },
];

const page = usePage();
const flashedAccessCode = computed(
    () =>
        (page.props.flash as Record<string, unknown> | undefined)?.vault_access_code as
            | { beneficiary_id: string; beneficiary_name: string; code: string }
            | null
            | undefined,
);
const flashedStatus = computed(
    () => (page.props.flash as Record<string, unknown> | undefined)?.status as string | undefined,
);

const isReleased = computed(() => props.vault.status === 'released');

const activeSection = ref<'beneficiaries' | 'media' | 'posts'>('beneficiaries');
const sections = [
    { key: 'beneficiaries', label: 'Beneficiaries' },
    { key: 'media', label: 'Media' },
    { key: 'posts', label: 'Posts' },
] as const;

const storageUsedMb = computed(() => props.vault.storage_used_bytes / (1024 * 1024));

/* Release */
const showReleaseDialog = ref(false);
const releaseForm = useForm({});

function submitRelease(): void {
    releaseForm.post(release(props.vault.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showReleaseDialog.value = false;
        },
    });
}

/* Beneficiaries */
const showBeneficiaryDialog = ref(false);
const editingBeneficiaryId = ref<string | null>(null);
const beneficiaryForm = useForm({
    type: 'beneficiary',
    full_name: '',
    email: '',
    contact_number: '',
    physical_address: '',
});

function openCreateBeneficiary(): void {
    editingBeneficiaryId.value = null;
    beneficiaryForm.reset();
    beneficiaryForm.clearErrors();
    showBeneficiaryDialog.value = true;
}

function openEditBeneficiary(beneficiary: Beneficiary): void {
    editingBeneficiaryId.value = beneficiary.id;
    beneficiaryForm.type = beneficiary.type;
    beneficiaryForm.full_name = beneficiary.full_name;
    beneficiaryForm.email = beneficiary.email;
    beneficiaryForm.contact_number = beneficiary.contact_number;
    beneficiaryForm.physical_address = beneficiary.physical_address;
    beneficiaryForm.clearErrors();
    showBeneficiaryDialog.value = true;
}

function submitBeneficiary(): void {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showBeneficiaryDialog.value = false;
            beneficiaryForm.reset();
        },
    };

    if (editingBeneficiaryId.value === null) {
        beneficiaryForm.post(storeBeneficiary(props.vault.id).url, options);
    } else {
        beneficiaryForm.patch(
            updateBeneficiary({
                vault: props.vault.id,
                beneficiary: editingBeneficiaryId.value,
            }).url,
            options,
        );
    }
}

const deleteBeneficiaryForm = useForm({});

function removeBeneficiary(beneficiary: Beneficiary): void {
    if (!confirm(`Remove ${beneficiary.full_name} as a beneficiary?`)) {
        return;
    }

    deleteBeneficiaryForm.delete(
        destroyBeneficiary({ vault: props.vault.id, beneficiary: beneficiary.id }).url,
        { preserveScroll: true },
    );
}

/* Media */
const mediaForm = useForm<{ title: string; file: File | null }>({
    title: '',
    file: null,
});

function onFileChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    mediaForm.file = target.files?.[0] ?? null;
}

function submitMedia(): void {
    mediaForm.post(storeMedia(props.vault.id).url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => mediaForm.reset(),
    });
}

const deleteMediaForm = useForm({});

function removeMedia(media: Media): void {
    if (!confirm(`Delete "${media.title ?? 'this file'}" from the vault?`)) {
        return;
    }

    deleteMediaForm.delete(destroyMedia({ vault: props.vault.id, media: media.id }).url, {
        preserveScroll: true,
    });
}

function formatFileSize(bytes: number): string {
    if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    return `${Math.max(1, Math.round(bytes / 1024))} KB`;
}

/* Posts */
const editingPostId = ref<string | null>(null);
const postForm = useForm<{
    title: string;
    body: string;
    visibility: string;
    beneficiary_ids: string[];
}>({
    title: '',
    body: '',
    visibility: 'all',
    beneficiary_ids: [],
});

function openEditPost(post: Post): void {
    editingPostId.value = post.id;
    postForm.title = post.title ?? '';
    postForm.body = post.body;
    postForm.visibility = post.visibility;
    postForm.beneficiary_ids = [...post.beneficiary_ids];
    postForm.clearErrors();
}

function resetPostForm(): void {
    editingPostId.value = null;
    postForm.reset();
    postForm.clearErrors();
}

function togglePostBeneficiary(beneficiaryId: string, checked: boolean): void {
    if (checked) {
        if (!postForm.beneficiary_ids.includes(beneficiaryId)) {
            postForm.beneficiary_ids.push(beneficiaryId);
        }
    } else {
        postForm.beneficiary_ids = postForm.beneficiary_ids.filter(
            (id) => id !== beneficiaryId,
        );
    }
}

function submitPost(): void {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetPostForm(),
    };

    if (editingPostId.value === null) {
        postForm.post(storePost(props.vault.id).url, options);
    } else {
        postForm.patch(
            updatePost({ vault: props.vault.id, post: editingPostId.value }).url,
            options,
        );
    }
}

const deletePostForm = useForm({});

function removePost(post: Post): void {
    if (!confirm(`Delete "${post.title ?? 'this post'}"?`)) {
        return;
    }

    deletePostForm.delete(destroyPost({ vault: props.vault.id, post: post.id }).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="vault.name ?? 'Vault'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl space-y-6 p-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-semibold">
                            {{ vault.name ?? 'Vault' }}
                        </h1>
                        <Badge :variant="isReleased ? 'secondary' : 'default'">
                            {{ vault.status }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        For {{ vault.person_display_name }} ·
                        {{ storageUsedMb.toFixed(1) }} MB of
                        {{ vault.storage_limit_mb }} MB used
                    </p>
                    <p v-if="vault.released_at" class="text-sm text-muted-foreground">
                        Released on {{ vault.released_at }}. The vault is now read-only.
                    </p>
                </div>

                <Button
                    v-if="!isReleased"
                    variant="destructive"
                    @click="showReleaseDialog = true"
                >
                    Release vault
                </Button>
            </div>

            <div
                v-if="flashedStatus"
                class="rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-800"
            >
                {{ flashedStatus }}
            </div>

            <div
                v-if="flashedAccessCode"
                class="space-y-1 rounded-md border border-amber-300 bg-amber-50 p-4"
            >
                <p class="text-sm font-medium text-amber-900">
                    Access code for {{ flashedAccessCode.beneficiary_name }}
                </p>
                <p class="font-mono text-2xl tracking-widest text-amber-900">
                    {{ flashedAccessCode.code }}
                </p>
                <p class="text-sm text-amber-800">
                    Copy this code now and share it securely — it will not be shown
                    again.
                </p>
            </div>

            <div class="flex gap-2 border-b pb-2">
                <Button
                    v-for="section in sections"
                    :key="section.key"
                    :variant="activeSection === section.key ? 'default' : 'ghost'"
                    size="sm"
                    @click="activeSection = section.key"
                >
                    {{ section.label }}
                </Button>
            </div>

            <!-- Beneficiaries -->
            <div v-if="activeSection === 'beneficiaries'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium">Beneficiaries</h2>
                    <Button v-if="!isReleased" @click="openCreateBeneficiary">
                        Add beneficiary
                    </Button>
                </div>

                <Card v-if="vault.beneficiaries.length === 0">
                    <CardHeader>
                        <CardTitle>No beneficiaries yet</CardTitle>
                        <CardDescription>
                            Add the people who should receive access to this vault once
                            it is released.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card v-for="beneficiary in vault.beneficiaries" :key="beneficiary.id">
                    <CardHeader>
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle>{{ beneficiary.full_name }}</CardTitle>
                            <Badge
                                :variant="beneficiary.type === 'executor' ? 'default' : 'secondary'"
                            >
                                {{ beneficiary.type }}
                            </Badge>
                        </div>
                        <CardDescription>
                            {{ beneficiary.email }} · {{ beneficiary.contact_number }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-1 text-sm">
                        <p class="text-muted-foreground">
                            {{ beneficiary.physical_address }}
                        </p>
                        <p v-if="beneficiary.access_code_hint" class="text-muted-foreground">
                            Access code:
                            <span class="font-mono">{{ beneficiary.access_code_hint }}</span>
                        </p>
                        <p class="text-muted-foreground">
                            {{
                                beneficiary.first_accessed_at
                                    ? `First accessed ${beneficiary.first_accessed_at}`
                                    : 'Has not accessed the vault yet'
                            }}
                        </p>
                        <div v-if="!isReleased" class="flex gap-2 pt-2">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="openEditBeneficiary(beneficiary)"
                            >
                                Edit
                            </Button>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="removeBeneficiary(beneficiary)"
                            >
                                Remove
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Media -->
            <div v-if="activeSection === 'media'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium">Media</h2>
                </div>

                <Card v-if="!isReleased">
                    <CardHeader>
                        <CardTitle>Upload a file</CardTitle>
                        <CardDescription>
                            Images (jpg, png, webp), video (mp4), audio (mp3, m4a, ogg)
                            or PDF. Max 50 MB per file.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form class="space-y-4" @submit.prevent="submitMedia">
                            <div class="grid gap-2">
                                <Label for="media-title">Title (optional)</Label>
                                <Input
                                    id="media-title"
                                    v-model="mediaForm.title"
                                    placeholder="A letter for the family"
                                />
                                <InputError :message="mediaForm.errors.title" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="media-file">File</Label>
                                <input
                                    id="media-file"
                                    type="file"
                                    class="text-sm"
                                    accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf,.mp3,.m4a,.ogg"
                                    @change="onFileChange"
                                />
                                <InputError :message="mediaForm.errors.file" />
                            </div>
                            <Button type="submit" :disabled="mediaForm.processing || !mediaForm.file">
                                Upload
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card v-if="vault.media.length === 0">
                    <CardHeader>
                        <CardTitle>No files yet</CardTitle>
                        <CardDescription>
                            Files uploaded here are kept safe until the vault is
                            released.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card v-for="media in vault.media" :key="media.id">
                    <CardHeader>
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle>{{ media.title ?? 'Untitled file' }}</CardTitle>
                            <Badge variant="secondary">{{ media.type }}</Badge>
                        </div>
                        <CardDescription>
                            {{ media.mime_type }} ·
                            {{ formatFileSize(media.file_size_bytes) }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex gap-2">
                        <Button as-child variant="outline" size="sm">
                            <a :href="media.url" target="_blank" rel="noopener">View</a>
                        </Button>
                        <Button
                            v-if="!isReleased"
                            variant="destructive"
                            size="sm"
                            @click="removeMedia(media)"
                        >
                            Delete
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Posts -->
            <div v-if="activeSection === 'posts'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium">Posts</h2>
                </div>

                <Card v-if="!isReleased">
                    <CardHeader>
                        <CardTitle>
                            {{ editingPostId ? 'Edit post' : 'Write a post' }}
                        </CardTitle>
                        <CardDescription>
                            Leave a message for everyone, or only for selected
                            beneficiaries.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form class="space-y-4" @submit.prevent="submitPost">
                            <div class="grid gap-2">
                                <Label for="post-title">Title (optional)</Label>
                                <Input
                                    id="post-title"
                                    v-model="postForm.title"
                                    placeholder="To my family"
                                />
                                <InputError :message="postForm.errors.title" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="post-body">Message</Label>
                                <Textarea
                                    id="post-body"
                                    v-model="postForm.body"
                                    rows="5"
                                    placeholder="Write your message..."
                                />
                                <InputError :message="postForm.errors.body" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Visible to</Label>
                                <Select v-model="postForm.visibility">
                                    <SelectTrigger class="w-full md:w-64">
                                        <SelectValue placeholder="Visibility" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All beneficiaries
                                        </SelectItem>
                                        <SelectItem value="selected">
                                            Selected beneficiaries
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="postForm.errors.visibility" />
                            </div>
                            <div v-if="postForm.visibility === 'selected'" class="grid gap-2">
                                <Label>Beneficiaries</Label>
                                <p
                                    v-if="vault.beneficiaries.length === 0"
                                    class="text-sm text-muted-foreground"
                                >
                                    Add beneficiaries first to select them here.
                                </p>
                                <Label
                                    v-for="beneficiary in vault.beneficiaries"
                                    :key="beneficiary.id"
                                    class="flex items-center gap-2 font-normal"
                                >
                                    <Checkbox
                                        :model-value="postForm.beneficiary_ids.includes(beneficiary.id)"
                                        @update:model-value="
                                            (checked: boolean | 'indeterminate') =>
                                                togglePostBeneficiary(beneficiary.id, checked === true)
                                        "
                                    />
                                    <span>{{ beneficiary.full_name }}</span>
                                </Label>
                                <InputError :message="postForm.errors.beneficiary_ids" />
                            </div>
                            <div class="flex gap-2">
                                <Button type="submit" :disabled="postForm.processing">
                                    {{ editingPostId ? 'Save changes' : 'Add post' }}
                                </Button>
                                <Button
                                    v-if="editingPostId"
                                    type="button"
                                    variant="ghost"
                                    @click="resetPostForm"
                                >
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card v-if="vault.posts.length === 0">
                    <CardHeader>
                        <CardTitle>No posts yet</CardTitle>
                        <CardDescription>
                            Posts are private until the vault is released.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card v-for="post in vault.posts" :key="post.id">
                    <CardHeader>
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle>{{ post.title ?? 'Untitled post' }}</CardTitle>
                            <Badge variant="secondary">
                                {{ post.visibility === 'all' ? 'All beneficiaries' : 'Selected' }}
                            </Badge>
                        </div>
                        <CardDescription v-if="post.created_at">
                            {{ post.created_at }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <p class="whitespace-pre-wrap text-sm">{{ post.body }}</p>
                        <div v-if="!isReleased" class="flex gap-2 pt-2">
                            <Button variant="outline" size="sm" @click="openEditPost(post)">
                                Edit
                            </Button>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="removePost(post)"
                            >
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Beneficiary dialog -->
        <Dialog v-model:open="showBeneficiaryDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ editingBeneficiaryId ? 'Edit beneficiary' : 'Add beneficiary' }}
                    </DialogTitle>
                    <DialogDescription>
                        Beneficiaries can access this vault with their email address and
                        access code once it has been released.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submitBeneficiary">
                    <div class="grid gap-2">
                        <Label for="beneficiary-type">Role</Label>
                        <Select v-model="beneficiaryForm.type">
                            <SelectTrigger id="beneficiary-type" class="w-full">
                                <SelectValue placeholder="Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="beneficiary">Beneficiary</SelectItem>
                                <SelectItem value="executor">Executor</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="beneficiaryForm.errors.type" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="beneficiary-name">Full name</Label>
                        <Input
                            id="beneficiary-name"
                            v-model="beneficiaryForm.full_name"
                            required
                        />
                        <InputError :message="beneficiaryForm.errors.full_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="beneficiary-email">Email address</Label>
                        <Input
                            id="beneficiary-email"
                            v-model="beneficiaryForm.email"
                            type="email"
                            required
                        />
                        <InputError :message="beneficiaryForm.errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="beneficiary-contact">Contact number</Label>
                        <Input
                            id="beneficiary-contact"
                            v-model="beneficiaryForm.contact_number"
                            required
                        />
                        <InputError :message="beneficiaryForm.errors.contact_number" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="beneficiary-address">Physical address</Label>
                        <Textarea
                            id="beneficiary-address"
                            v-model="beneficiaryForm.physical_address"
                            rows="3"
                            required
                        />
                        <InputError :message="beneficiaryForm.errors.physical_address" />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="ghost"
                            @click="showBeneficiaryDialog = false"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="beneficiaryForm.processing">
                            {{ editingBeneficiaryId ? 'Save changes' : 'Add beneficiary' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Release dialog -->
        <Dialog v-model:open="showReleaseDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Release this vault?</DialogTitle>
                    <DialogDescription>
                        Releasing the vault gives all beneficiaries access with their
                        access codes and makes the vault permanently read-only. This
                        cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="ghost" @click="showReleaseDialog = false">
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        :disabled="releaseForm.processing"
                        @click="submitRelease"
                    >
                        Release vault
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
