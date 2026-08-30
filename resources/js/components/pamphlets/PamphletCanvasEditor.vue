<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';
import {
    clonePamphletLayout,
    formatPamphletDate,
    photoBlockStyle,
    textBlockStyle,
    type PamphletBlockKey,
    type PamphletLayoutData,
} from '@/lib/pamphletLayout';

const props = defineProps<{
    layout: PamphletLayoutData;
    selectedBlock: PamphletBlockKey | null;
    heading: string;
    personFullName: string;
    shortText: string;
    dateOfBirth: string;
    dateOfPassing: string;
    dateFormat: string;
    fontFamily: string;
    headingColor: string;
    nameColor: string;
    datesColor: string;
    shortTextColor: string;
    imageShape: 'circle' | 'square';
    imageCropMode: 'cover' | 'contain';
    uploadedImagePreviewUrl: string | null;
    backgroundAssetPath: string | null;
}>();

const emit = defineEmits<{
    'update:selectedBlock': [PamphletBlockKey | null];
    'update:layout': [PamphletLayoutData];
    'update:heading': [string];
    'update:personFullName': [string];
    'update:shortText': [string];
}>();

const canvasRef = ref<HTMLElement | null>(null);

type DragMode = 'move' | 'resize';

type DragState = {
    key: PamphletBlockKey;
    mode: DragMode;
    startX: number;
    startY: number;
    originX: number;
    originY: number;
    originW: number;
    originH: number;
};

const dragState = ref<DragState | null>(null);

const memorialImageClasses = computed(() => [
    'pointer-events-none h-full w-full',
    props.imageCropMode === 'contain' ? 'object-contain bg-white/10' : 'object-cover',
    props.imageShape === 'circle' ? 'rounded-full' : 'rounded-lg',
]);

const formattedDateRange = computed(() => {
    const birth =
        props.dateOfBirth === ''
            ? 'Date of birth'
            : formatPamphletDate(props.dateOfBirth, props.dateFormat);
    const passing =
        props.dateOfPassing === ''
            ? 'Date of passing'
            : formatPamphletDate(props.dateOfPassing, props.dateFormat);

    return `${birth} – ${passing}`;
});

const selectBlock = (key: PamphletBlockKey): void => {
    emit('update:selectedBlock', key);
};

const blockOutline = (key: PamphletBlockKey): string =>
    props.selectedBlock === key
        ? 'ring-2 ring-brand ring-offset-1 ring-offset-transparent'
        : 'hover:ring-1 hover:ring-white/70';

const startDrag = (event: PointerEvent, key: PamphletBlockKey, mode: DragMode = 'move'): void => {
    if (event.button !== 0) {
        return;
    }

    const target = event.target as HTMLElement | null;

    if (mode === 'move' && target !== null && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA')) {
        selectBlock(key);

        return;
    }

    event.preventDefault();
    event.stopPropagation();
    selectBlock(key);

    const block = props.layout[key];

    dragState.value = {
        key,
        mode,
        startX: event.clientX,
        startY: event.clientY,
        originX: block.x,
        originY: block.y,
        originW: block.w,
        originH: 'h' in block ? block.h : 0,
    };

    window.addEventListener('pointermove', onPointerMove);
    window.addEventListener('pointerup', onPointerUp);
};

const onPointerMove = (event: PointerEvent): void => {
    if (dragState.value === null || canvasRef.value === null) {
        return;
    }

    const rect = canvasRef.value.getBoundingClientRect();
    const dx = ((event.clientX - dragState.value.startX) / rect.width) * 100;
    const dy = ((event.clientY - dragState.value.startY) / rect.height) * 100;
    const next = clonePamphletLayout(props.layout);
    const key = dragState.value.key;

    if (dragState.value.mode === 'resize' && key === 'photo') {
        next.photo = {
            ...next.photo,
            w: Math.max(10, Math.min(90, dragState.value.originW + dx)),
            h: Math.max(10, Math.min(90, dragState.value.originH + dy)),
        };
    } else if (key === 'photo') {
        next.photo = {
            ...next.photo,
            x: Math.max(0, Math.min(95, dragState.value.originX + dx)),
            y: Math.max(0, Math.min(95, dragState.value.originY + dy)),
        };
    } else {
        next[key] = {
            ...next[key],
            x: Math.max(0, Math.min(95, dragState.value.originX + dx)),
            y: Math.max(0, Math.min(95, dragState.value.originY + dy)),
        };
    }

    emit('update:layout', next);
};

const onPointerUp = (): void => {
    dragState.value = null;
    window.removeEventListener('pointermove', onPointerMove);
    window.removeEventListener('pointerup', onPointerUp);
};

onBeforeUnmount(() => {
    window.removeEventListener('pointermove', onPointerMove);
    window.removeEventListener('pointerup', onPointerUp);
});
</script>

<template>
    <div
        ref="canvasRef"
        class="relative mx-auto w-full min-w-0 max-w-xl overflow-hidden rounded-2xl border border-border bg-muted shadow-md"
        style="container-type: inline-size"
        @pointerdown="emit('update:selectedBlock', null)"
    >
        <img
            v-if="backgroundAssetPath"
            :src="`/${backgroundAssetPath}`"
            alt=""
            class="pointer-events-none block h-auto w-full max-w-full select-none"
        />
        <div v-else class="aspect-[3/4] w-full bg-muted" aria-hidden="true" />

        <div class="absolute inset-0 overflow-hidden" :style="{ fontFamily }">
            <div
                class="absolute cursor-move overflow-hidden border border-white/40 bg-white/10"
                :class="[imageShape === 'circle' ? 'rounded-full' : 'rounded-lg', blockOutline('photo')]"
                :style="photoBlockStyle(layout.photo)"
                @pointerdown.stop="startDrag($event, 'photo')"
            >
                <img
                    v-if="uploadedImagePreviewUrl"
                    :src="uploadedImagePreviewUrl"
                    alt="Memorial preview image"
                    :class="memorialImageClasses"
                />
                <div v-else class="flex h-full w-full items-center justify-center text-sm text-black/50">
                    Image preview
                </div>
                <button
                    v-if="selectedBlock === 'photo'"
                    type="button"
                    class="absolute right-1 bottom-1 size-4 cursor-se-resize rounded-sm border border-white bg-brand shadow"
                    aria-label="Resize photo"
                    @pointerdown.stop="startDrag($event, 'photo', 'resize')"
                />
            </div>

            <div
                class="absolute cursor-move text-center"
                :class="blockOutline('heading')"
                :style="textBlockStyle(layout.heading, headingColor, fontFamily)"
                @pointerdown.stop="startDrag($event, 'heading')"
            >
                <input
                    :value="heading"
                    type="text"
                    required
                    placeholder="Heading"
                    class="w-full border-0 bg-transparent text-center font-bold leading-tight shadow-none outline-none ring-0 placeholder:opacity-55 focus:ring-1 focus:ring-black/25"
                    :style="{ color: headingColor, fontFamily, fontSize: 'inherit' }"
                    @focus="selectBlock('heading')"
                    @input="emit('update:heading', ($event.target as HTMLInputElement).value)"
                />
            </div>

            <div
                class="absolute cursor-move text-center"
                :class="blockOutline('name')"
                :style="textBlockStyle(layout.name, nameColor, fontFamily)"
                @pointerdown.stop="startDrag($event, 'name')"
            >
                <input
                    :value="personFullName"
                    type="text"
                    required
                    placeholder="Person full name"
                    class="w-full border-0 bg-transparent text-center font-semibold leading-tight shadow-none outline-none ring-0 placeholder:opacity-55 focus:ring-1 focus:ring-black/25"
                    :style="{ color: nameColor, fontFamily, fontSize: 'inherit' }"
                    @focus="selectBlock('name')"
                    @input="emit('update:personFullName', ($event.target as HTMLInputElement).value)"
                />
            </div>

            <div
                class="absolute cursor-move text-center leading-tight"
                :class="blockOutline('dates')"
                :style="textBlockStyle(layout.dates, datesColor, fontFamily)"
                @pointerdown.stop="startDrag($event, 'dates')"
            >
                {{ formattedDateRange }}
            </div>

            <div
                class="absolute cursor-move text-center"
                :class="blockOutline('tribute')"
                :style="textBlockStyle(layout.tribute, shortTextColor, fontFamily)"
                @pointerdown.stop="startDrag($event, 'tribute')"
            >
                <textarea
                    :value="shortText"
                    rows="3"
                    required
                    placeholder="Short memorial text…"
                    class="w-full resize-none border-0 bg-transparent text-center leading-relaxed shadow-none outline-none ring-0 placeholder:opacity-55 focus:ring-1 focus:ring-black/25"
                    :style="{ color: shortTextColor, fontFamily, fontSize: 'inherit' }"
                    @focus="selectBlock('tribute')"
                    @input="emit('update:shortText', ($event.target as HTMLTextAreaElement).value)"
                />
            </div>

            <div class="pointer-events-none absolute bottom-[3%] left-1/2 flex -translate-x-1/2 flex-col items-center gap-1.5">
                <div class="rounded-lg bg-white p-2 shadow-sm">
                    <svg viewBox="0 0 41 41" class="h-16 w-16" role="img" aria-label="Mock QR code">
                        <rect width="41" height="41" fill="#fff" />
                        <g fill="#111">
                            <rect x="2" y="2" width="11" height="11" />
                            <rect x="4" y="4" width="7" height="7" fill="#fff" />
                            <rect x="6" y="6" width="3" height="3" />
                            <rect x="28" y="2" width="11" height="11" />
                            <rect x="30" y="4" width="7" height="7" fill="#fff" />
                            <rect x="32" y="6" width="3" height="3" />
                            <rect x="2" y="28" width="11" height="11" />
                            <rect x="4" y="30" width="7" height="7" fill="#fff" />
                            <rect x="6" y="32" width="3" height="3" />
                            <rect x="18" y="18" width="5" height="5" />
                        </g>
                    </svg>
                </div>
                <p class="text-[10px] uppercase tracking-wide text-black/55">QR appears after publish</p>
            </div>
        </div>
    </div>
</template>
