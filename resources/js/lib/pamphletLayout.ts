export type TextBlockLayout = {
    x: number;
    y: number;
    w: number;
    fontSize: number;
};

export type PhotoBlockLayout = {
    x: number;
    y: number;
    w: number;
    h: number;
};

export type PamphletLayoutData = {
    heading: TextBlockLayout;
    name: TextBlockLayout;
    dates: TextBlockLayout;
    tribute: TextBlockLayout;
    photo: PhotoBlockLayout;
};

export type PamphletBlockKey = keyof PamphletLayoutData;

export const defaultPamphletLayout = (): PamphletLayoutData => ({
    heading: { x: 8, y: 5, w: 84, fontSize: 7 },
    name: { x: 8, y: 54, w: 84, fontSize: 5 },
    dates: { x: 8, y: 62, w: 84, fontSize: 3.2 },
    tribute: { x: 10, y: 68, w: 80, fontSize: 3 },
    photo: { x: 28, y: 22, w: 44, h: 28 },
});

/** Plain deep copy — Vue reactive proxies cannot be passed to structuredClone. */
export const clonePamphletLayout = (layout: PamphletLayoutData): PamphletLayoutData => ({
    heading: { ...layout.heading },
    name: { ...layout.name },
    dates: { ...layout.dates },
    tribute: { ...layout.tribute },
    photo: { ...layout.photo },
});

export const normalizePamphletLayout = (layout?: PamphletLayoutData | null): PamphletLayoutData => {
    const defaults = defaultPamphletLayout();

    if (!layout) {
        return defaults;
    }

    const clamp = (value: number, min: number, max: number): number =>
        Math.round(Math.max(min, Math.min(max, value)) * 100) / 100;

    return {
        heading: {
            x: clamp(layout.heading?.x ?? defaults.heading.x, 0, 95),
            y: clamp(layout.heading?.y ?? defaults.heading.y, 0, 95),
            w: clamp(layout.heading?.w ?? defaults.heading.w, 10, 100),
            fontSize: clamp(layout.heading?.fontSize ?? defaults.heading.fontSize, 1.5, 14),
        },
        name: {
            x: clamp(layout.name?.x ?? defaults.name.x, 0, 95),
            y: clamp(layout.name?.y ?? defaults.name.y, 0, 95),
            w: clamp(layout.name?.w ?? defaults.name.w, 10, 100),
            fontSize: clamp(layout.name?.fontSize ?? defaults.name.fontSize, 1.5, 14),
        },
        dates: {
            x: clamp(layout.dates?.x ?? defaults.dates.x, 0, 95),
            y: clamp(layout.dates?.y ?? defaults.dates.y, 0, 95),
            w: clamp(layout.dates?.w ?? defaults.dates.w, 10, 100),
            fontSize: clamp(layout.dates?.fontSize ?? defaults.dates.fontSize, 1.5, 14),
        },
        tribute: {
            x: clamp(layout.tribute?.x ?? defaults.tribute.x, 0, 95),
            y: clamp(layout.tribute?.y ?? defaults.tribute.y, 0, 95),
            w: clamp(layout.tribute?.w ?? defaults.tribute.w, 10, 100),
            fontSize: clamp(layout.tribute?.fontSize ?? defaults.tribute.fontSize, 1.5, 14),
        },
        photo: {
            x: clamp(layout.photo?.x ?? defaults.photo.x, 0, 95),
            y: clamp(layout.photo?.y ?? defaults.photo.y, 0, 95),
            w: clamp(layout.photo?.w ?? defaults.photo.w, 10, 90),
            h: clamp(layout.photo?.h ?? defaults.photo.h, 10, 90),
        },
    };
};

export const formatPamphletDate = (value: string | null, dateFormat: string): string => {
    if (value === null || value === '') {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    switch (dateFormat) {
        case 'd/m/Y':
            return date.toLocaleDateString('en-GB');
        case 'Y-m-d':
            return date.toISOString().slice(0, 10);
        case 'j F Y':
            return date.toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });
        default:
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
    }
};

export const textBlockStyle = (
    block: TextBlockLayout,
    color: string,
    fontFamily: string,
    extra?: Record<string, string>,
): Record<string, string> => ({
    left: `${block.x}%`,
    top: `${block.y}%`,
    width: `${block.w}%`,
    fontSize: `${block.fontSize}cqw`,
    color,
    fontFamily,
    ...extra,
});

export const photoBlockStyle = (block: PhotoBlockLayout): Record<string, string> => ({
    left: `${block.x}%`,
    top: `${block.y}%`,
    width: `${block.w}%`,
    height: `${block.h}%`,
});
