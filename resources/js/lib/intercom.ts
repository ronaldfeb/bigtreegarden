import type { User } from '@/types/auth';

const INTERCOM_APP_ID = 'a8f6x7iy';
const INTERCOM_API_BASE = 'https://api-iam.intercom.io';

type IntercomBootSettings = {
    api_base: string;
    app_id: string;
    user_id?: string;
    name?: string;
    email?: string;
    created_at?: number;
};

let scriptLoaded = false;
let isBooted = false;

function getIntercom(): ((...args: unknown[]) => void) | undefined {
    if (typeof window.Intercom === 'function') {
        return window.Intercom;
    }

    return undefined;
}

export function loadIntercomScript(): void {
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
        script.src = `https://widget.intercom.io/widget/${INTERCOM_APP_ID}`;

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

function bootSettings(user?: User): IntercomBootSettings {
    const settings: IntercomBootSettings = {
        api_base: INTERCOM_API_BASE,
        app_id: INTERCOM_APP_ID,
    };

    if (user === undefined) {
        return settings;
    }

    return {
        ...settings,
        user_id: String(user.id),
        name: user.name,
        email: user.email,
        created_at: Math.floor(new Date(user.created_at).getTime() / 1000),
    };
}

export function bootIntercom(user?: User): void {
    loadIntercomScript();

    const intercom = getIntercom();

    if (intercom === undefined) {
        return;
    }

    const settings = bootSettings(user);

    if (isBooted) {
        intercom('update', settings);
    } else {
        intercom('boot', settings);
        isBooted = true;
    }
}

export function updateIntercom(): void {
    const intercom = getIntercom();

    if (intercom === undefined || !isBooted) {
        return;
    }

    intercom('update');
}

export function shutdownIntercom(): void {
    const intercom = getIntercom();

    if (intercom === undefined || !isBooted) {
        return;
    }

    intercom('shutdown');
    isBooted = false;
}

export function syncIntercom(user: User | null): void {
    shutdownIntercom();

    if (user === null) {
        bootIntercom();

        return;
    }

    bootIntercom(user);
}
