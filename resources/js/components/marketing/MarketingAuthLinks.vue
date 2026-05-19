<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister?: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage<{ auth: { user: { id: string } | null } }>();
</script>

<template>
    <div class="flex items-center gap-3 text-sm">
        <Link
            v-if="page.props.auth.user"
            :href="dashboard()"
            class="text-muted-foreground transition-colors hover:text-foreground"
        >
            Dashboard
        </Link>
        <template v-else>
            <Link
                :href="login()"
                class="text-muted-foreground transition-colors hover:text-foreground"
            >
                Log in
            </Link>
            <Link
                v-if="canRegister"
                :href="register()"
                class="hidden rounded-md border border-border px-4 py-1.5 text-foreground transition-colors hover:bg-muted sm:inline-block"
            >
                Register
            </Link>
        </template>
    </div>
</template>
