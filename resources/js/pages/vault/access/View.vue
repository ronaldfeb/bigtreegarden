<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    beneficiary: {
        full_name: string;
        type: string;
    };
    vault: {
        name: string | null;
        person_display_name: string | null;
        released_at: string | null;
    };
    posts: Array<{
        id: string;
        title: string | null;
        body: string;
        created_at: string | null;
    }>;
    media: Array<{
        id: string;
        type: string;
        title: string | null;
        url: string;
        mime_type: string;
        file_size_bytes: number;
    }>;
}>();

function formatFileSize(bytes: number): string {
    if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    return `${Math.max(1, Math.round(bytes / 1024))} KB`;
}
</script>

<template>
    <Head :title="vault.name ?? 'Vault'" />

    <div class="min-h-svh bg-background p-6 md:p-10">
        <div class="mx-auto w-full max-w-3xl space-y-8">
            <div class="space-y-2 text-center">
                <h1 class="text-2xl font-semibold">
                    {{ vault.name ?? 'Digital vault' }}
                </h1>
                <p class="text-muted-foreground">
                    A vault left for you in memory of
                    {{ vault.person_display_name }}.
                </p>
                <div class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                    <span>Welcome, {{ beneficiary.full_name }}</span>
                    <Badge variant="secondary">{{ beneficiary.type }}</Badge>
                </div>
                <p v-if="vault.released_at" class="text-sm text-muted-foreground">
                    Released on {{ vault.released_at }}
                </p>
            </div>

            <section class="space-y-4">
                <h2 class="text-lg font-medium">Messages for you</h2>

                <Card v-if="posts.length === 0">
                    <CardHeader>
                        <CardDescription>
                            There are no messages addressed to you in this vault.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card v-for="post in posts" :key="post.id">
                    <CardHeader>
                        <CardTitle>{{ post.title ?? 'A message' }}</CardTitle>
                        <CardDescription v-if="post.created_at">
                            {{ post.created_at }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-wrap text-sm">{{ post.body }}</p>
                    </CardContent>
                </Card>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-medium">Files</h2>

                <Card v-if="media.length === 0">
                    <CardHeader>
                        <CardDescription>
                            There are no files in this vault.
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card v-for="item in media" :key="item.id">
                    <CardHeader>
                        <div class="flex items-center justify-between gap-2">
                            <CardTitle>{{ item.title ?? 'Untitled file' }}</CardTitle>
                            <Badge variant="secondary">{{ item.type }}</Badge>
                        </div>
                        <CardDescription>
                            {{ item.mime_type }} · {{ formatFileSize(item.file_size_bytes) }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Button as-child variant="outline" size="sm">
                            <a :href="item.url" target="_blank" rel="noopener" download>
                                Download
                            </a>
                        </Button>
                    </CardContent>
                </Card>
            </section>
        </div>
    </div>
</template>
