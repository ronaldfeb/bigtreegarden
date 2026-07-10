import type { User } from '@/types/auth';

export type StaffRole = 'admin' | 'marketing' | 'content' | 'support';

export type StaffUser = {
    id: string;
    role: StaffRole;
    job_title: string | null;
    is_active: boolean;
    user: User;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
};

export type StaffTableColumn<T = Record<string, unknown>> = {
    key: string;
    label: string;
    class?: string;
    format?: (row: T) => string | number | null | undefined;
};
