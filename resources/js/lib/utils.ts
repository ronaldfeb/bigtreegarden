import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function formatCentsAsRand(
    cents: number | string | null | undefined,
    currency: string = 'ZAR',
): string {
    const amount = Number(cents);

    if (!Number.isFinite(amount)) {
        return '—';
    }

    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency,
    }).format(amount / 100);
}

export function centsToRandInput(cents: number | string | null | undefined): string {
    const amount = Number(cents);

    if (!Number.isFinite(amount)) {
        return '';
    }

    return (amount / 100).toFixed(2);
}

export function randInputToCents(rand: string | number): number {
    const amount = Number(rand);

    if (!Number.isFinite(amount)) {
        return 0;
    }

    return Math.round(amount * 100);
}

