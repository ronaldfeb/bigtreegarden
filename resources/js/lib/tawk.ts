const TAWK_SRC = 'https://embed.tawk.to/6a60e5a8aa83a11d48ca6784/1ju58273b';

let widgetShouldBeVisible = false;

function applyVisibility(): void {
    const api = window.Tawk_API;

    if (!api) {
        return;
    }

    if (widgetShouldBeVisible) {
        api.showWidget?.();
    } else {
        api.hideWidget?.();
    }
}

function ensureOnLoadHook(): void {
    const api = window.Tawk_API;

    if (!api || api.__btgVisibilityHooked) {
        return;
    }

    const previousOnLoad = api.onLoad;

    api.onLoad = () => {
        if (typeof previousOnLoad === 'function') {
            previousOnLoad();
        }

        applyVisibility();
    };

    api.__btgVisibilityHooked = true;
}

export function loadTawkToWidget(): void {
    widgetShouldBeVisible = true;

    if (document.querySelector(`script[src="${TAWK_SRC}"]`)) {
        applyVisibility();

        return;
    }

    window.Tawk_API = window.Tawk_API || {};
    window.Tawk_LoadStart = new Date();
    ensureOnLoadHook();

    const script = document.createElement('script');
    script.async = true;
    script.src = TAWK_SRC;
    script.charset = 'UTF-8';
    script.setAttribute('crossorigin', '*');

    const firstScript = document.getElementsByTagName('script')[0];
    firstScript.parentNode?.insertBefore(script, firstScript);
}

export function hideTawkToWidget(): void {
    widgetShouldBeVisible = false;
    ensureOnLoadHook();
    applyVisibility();
}
