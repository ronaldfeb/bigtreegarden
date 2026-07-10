<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

interface LiveMessage {
    id: string;
    author: string | null;
    body: string | null;
    image_url: string | null;
    posted_at: string | null;
}

const props = defineProps<{
    memorialPage: {
        title: string;
        person_full_name: string | null;
        public_slug: string;
        sections: Array<{ title: string; body: string | null }>;
    };
    isActiveDay: boolean;
    canPost: boolean;
    messagesUrl: string;
    postUrl: string;
    memorialUrl: string;
    loginUrl: string;
    registerUrl: string;
}>();

const page = usePage();
const isAuthenticated = computed(() => Boolean((page.props.auth as { user: unknown } | undefined)?.user));
const programmeSections = computed(() =>
    props.memorialPage.sections.filter((section) => (section.body ?? '').trim() !== ''),
);

const messages = ref<LiveMessage[]>([]);
const feedElement = ref<HTMLElement | null>(null);
let pollTimer: ReturnType<typeof setInterval> | null = null;

const fetchMessages = async (): Promise<void> => {
    try {
        const response = await fetch(props.messagesUrl, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            return;
        }

        const data = (await response.json()) as { messages: LiveMessage[] };
        const shouldScroll =
            feedElement.value !== null &&
            data.messages.length > messages.value.length &&
            feedElement.value.scrollHeight - feedElement.value.scrollTop - feedElement.value.clientHeight < 120;

        messages.value = data.messages;

        if (shouldScroll) {
            requestAnimationFrame(() => {
                feedElement.value?.scrollTo({ top: feedElement.value.scrollHeight });
            });
        }
    } catch {
        // Network hiccups are expected while polling; the next tick retries.
    }
};

onMounted(() => {
    void fetchMessages();
    pollTimer = setInterval(() => void fetchMessages(), 1000);
});

onBeforeUnmount(() => {
    if (pollTimer !== null) {
        clearInterval(pollTimer);
    }
});

const form = useForm<{ body: string; image: File | null }>({
    body: '',
    image: null,
});

const imageInput = ref<HTMLInputElement | null>(null);

const onImageSelected = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    form.image = input.files?.[0] ?? null;
};

const submit = (): void => {
    if (form.processing) {
        return;
    }

    form.post(props.postUrl, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();

            if (imageInput.value) {
                imageInput.value.value = '';
            }

            void fetchMessages();
        },
    });
};

const formatTime = (value: string | null): string => {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head :title="`Live — ${memorialPage.person_full_name ?? memorialPage.title}`" />

    <div class="flex min-h-screen flex-col bg-background" data-surface="memorial">
        <header class="border-b border-border/40 px-4 py-6 text-center">
            <p class="text-eyebrow text-gold">Live remembrance</p>
            <h1 class="mt-1 text-balance text-display text-2xl md:text-3xl">
                {{ memorialPage.person_full_name ?? memorialPage.title }}
            </h1>
            <a
                :href="memorialUrl"
                class="mt-2 inline-block text-muted-foreground text-sm underline-offset-4 hover:underline"
            >
                Back to the memorial page
            </a>
        </header>

        <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col px-4 py-6">
            <details
                v-if="programmeSections.length > 0"
                class="group mb-4 rounded-2xl border border-border bg-card shadow-warm-sm"
            >
                <summary
                    class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-foreground [&::-webkit-details-marker]:hidden"
                >
                    <span class="font-medium text-sm">Service programme</span>
                    <span
                        class="shrink-0 text-muted-foreground transition-transform duration-200 group-open:rotate-180"
                        aria-hidden="true"
                    >
                        ⌄
                    </span>
                </summary>
                <div class="space-y-4 border-t border-border/60 px-4 py-4">
                    <div v-for="section in programmeSections" :key="section.title">
                        <p class="text-eyebrow text-gold">{{ section.title }}</p>
                        <p class="mt-1 whitespace-pre-wrap text-foreground text-sm">{{ section.body }}</p>
                    </div>
                </div>
            </details>

            <div
                v-if="!isActiveDay"
                class="rounded-xl border border-dashed border-border p-6 text-center text-muted-foreground text-sm"
            >
                Live comments are not open right now. They are available on the day of the service.
            </div>

            <div
                ref="feedElement"
                class="flex-1 space-y-4 overflow-y-auto pb-6"
                aria-live="polite"
            >
                <p
                    v-if="messages.length === 0 && isActiveDay"
                    class="py-10 text-center text-muted-foreground text-sm"
                >
                    No messages yet. Be the first to share a memory.
                </p>

                <article
                    v-for="message in messages"
                    :key="message.id"
                    class="rounded-2xl border border-border bg-card p-4 shadow-warm-sm"
                >
                    <div class="flex items-baseline justify-between gap-3">
                        <p class="font-medium text-foreground text-sm">
                            {{ message.author ?? 'Anonymous' }}
                        </p>
                        <p class="shrink-0 text-muted-foreground text-xs">
                            {{ formatTime(message.posted_at) }}
                        </p>
                    </div>
                    <p v-if="message.body" class="mt-2 whitespace-pre-wrap text-foreground text-sm">
                        {{ message.body }}
                    </p>
                    <img
                        v-if="message.image_url"
                        :src="message.image_url"
                        alt="Shared live-day photo"
                        class="mt-3 max-h-80 w-full rounded-xl border border-border object-cover"
                    />
                </article>
            </div>

            <footer class="sticky bottom-0 border-t border-border/40 bg-background pb-4 pt-4">
                <form v-if="canPost" class="space-y-3" @submit.prevent="submit">
                    <textarea
                        v-model="form.body"
                        rows="2"
                        maxlength="500"
                        placeholder="Share a memory or message…"
                        class="w-full resize-none rounded-xl border border-border bg-card p-3 text-foreground text-sm outline-none focus:ring-2 focus:ring-ring"
                    ></textarea>
                    <p v-if="form.errors.body" class="text-destructive text-sm">{{ form.errors.body }}</p>
                    <p v-if="form.errors.image" class="text-destructive text-sm">{{ form.errors.image }}</p>
                    <div class="flex items-center justify-between gap-3">
                        <input
                            ref="imageInput"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="text-muted-foreground text-xs file:mr-3 file:rounded-md file:border file:border-border file:bg-muted file:px-3 file:py-1.5 file:text-foreground file:text-xs"
                            @change="onImageSelected"
                        />
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-full bg-primary px-5 py-2 text-primary-foreground text-sm transition-opacity disabled:opacity-50"
                        >
                            {{ form.processing ? 'Sending…' : 'Send' }}
                        </button>
                    </div>
                </form>

                <p
                    v-else-if="isActiveDay && !isAuthenticated"
                    class="text-center text-muted-foreground text-sm"
                >
                    <a :href="loginUrl" class="text-foreground underline underline-offset-4">Log in</a>
                    or
                    <a :href="registerUrl" class="text-foreground underline underline-offset-4">sign up</a>
                    to share a message.
                </p>
            </footer>
        </main>
    </div>
</template>
