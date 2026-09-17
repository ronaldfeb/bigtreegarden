import type { User } from '@/types/auth';

export const intercomAppId = import.meta.env.VITE_INTERCOM_APP_ID ?? '';
export const intercomApiBase =
    import.meta.env.VITE_INTERCOM_API_BASE ?? 'https://api-iam.intercom.io';

type IntercomBootSettings = {
    api_base: string;
    app_id: string;
    intercom_user_jwt?: string;
    user_id?: string;
    name?: string;
    email?: string;
    created_at?: number;
};

type IntercomSyncOptions = {
    user: User | null;
    userJwt?: string | null;
};

let scriptLoaded = false;
let isBooted = false;

export function isIntercomConfigured(): boolean {
    return intercomAppId.length > 0;
}

export function intercomInboxUrl(): string | null {
    if (! isIntercomConfigured()) {
        return null;
    }

    return `https://app.intercom.com/a/apps/${intercomAppId}/inbox`;
}

function getIntercom(): ((...args: unknown[]) => void) | undefined {
    if (typeof window.Intercom === 'function') {
        return window.Intercom;
    }

    return undefined;
}

export function loadIntercomScript(): void {
    if (! isIntercomConfigured()) {
        return;
    }

    if (scriptLoaded || document.querySelector('script[src*="widget.intercom.io"]')) {
        scriptLoaded = true;

        return;
    }

    const intercomStub = function (...args: unknown[]): void {
        intercomStub.c(args);
    };

    intercomStub.q = [] as unknown[][];
    intercomStub.c = function (args: unknown[]): void {
        intercomStub.q.push(args);
    };

    window.Intercom = intercomStub;

    const loadScript = (): void => {
        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.async = true;
        script.src = `https://widget.intercom.io/widget/${intercomAppId}`;

        const firstScript = document.getElementsByTagName('script')[0];
        firstScript.parentNode?.insertBefore(script, firstScript);
    };

    if (document.readyState === 'complete') {
        loadScript();
    } else {
        window.addEventListener('load', loadScript, false);
    }

    scriptLoaded = true;
}

function bootSettings({ user, userJwt }: IntercomSyncOptions): IntercomBootSettings {
    const settings: IntercomBootSettings = {
        api_base: intercomApiBase,
        app_id: intercomAppId,
    };

    if (user === null) {
        return settings;
    }

    if (userJwt !== undefined && userJwt !== null && userJwt !== '') {
        return {
            ...settings,
            intercom_user_jwt: userJwt,
        };
    }

    return {
        ...settings,
        user_id: String(user.id),
        name: user.name,
        email: user.email,
        created_at: Math.floor(new Date(user.created_at).getTime() / 1000),
    };
}

function applyIntercomSettings(options: IntercomSyncOptions): void {
    if (! isIntercomConfigured()) {
        return;
    }

    loadIntercomScript();

    const intercom = getIntercom();

    if (intercom === undefined) {
        return;
    }

    const settings = bootSettings(options);

    if (isBooted) {
        intercom('update', settings);
    } else {
        intercom('boot', settings);
        isBooted = true;
    }
}

export function bootIntercom(options: IntercomSyncOptions): void {
    applyIntercomSettings(options);
}

export function updateIntercom(options: IntercomSyncOptions): void {
    if (! isIntercomConfigured()) {
        return;
    }

    const intercom = getIntercom();

    if (intercom === undefined || ! isBooted) {
        return;
    }

    if (options.user !== null) {
        intercom('update', bootSettings(options));

        return;
    }

    intercom('update');
}

export function shutdownIntercom(): void {
    const intercom = getIntercom();

    if (intercom === undefined || ! isBooted) {
        return;
    }

    intercom('shutdown');
    isBooted = false;
}

export function syncIntercom(options: IntercomSyncOptions): void {
    if (! isIntercomConfigured()) {
        return;
    }

    shutdownIntercom();
    applyIntercomSettings(options);
}
