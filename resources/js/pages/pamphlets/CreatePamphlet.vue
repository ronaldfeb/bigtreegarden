<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Minus, PanelLeft, Plus } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PamphletCanvasEditor from '@/components/pamphlets/PamphletCanvasEditor.vue';
import PamphletSettingsPanel from '@/components/pamphlets/PamphletSettingsPanel.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import MarketingLayout from '@/layouts/marketing/MarketingLayout.vue';
import {
    clonePamphletLayout,
    normalizePamphletLayout,
    type PamphletBlockKey,
    type PamphletLayoutData,
} from '@/lib/pamphletLayout';

type BackgroundOption = {
    id: string;
    name: string;
    asset_path: string;
    collection_slug: string;
};

type EditablePamphlet = {
    id: string;
    heading: string;
    person_full_name: string;
    date_of_birth: string | null;
    date_of_passing: string | null;
    date_format: 'd M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y' | string;
    image_shape: 'circle' | 'square' | string;
    image_crop_mode: 'cover' | 'contain' | string;
    short_text: string;
    background_id: string | null;
    uploaded_image_url: string | null;
    font_family: string;
    heading_color: string;
    name_color: string;
    short_text_color: string;
    dates_color: string;
    layout?: PamphletLayoutData | null;
};

const props = withDefaults(
    defineProps<{
        backgrounds: BackgroundOption[];
        canRegister?: boolean;
        pamphlet?: EditablePamphlet | null;
    }>(),
    {
        canRegister: true,
        pamphlet: null,
    },
);

const page = usePage<{ auth: { user: { id: string } | null } }>();

const isEditing = computed(() => props.pamphlet !== null);
const existingImageUrl = props.pamphlet?.uploaded_image_url ?? null;

const heading = ref(props.pamphlet?.heading ?? '');
const personFullName = ref(props.pamphlet?.person_full_name ?? '');
const fontFamily = ref(props.pamphlet?.font_family ?? 'Georgia');
const dateOfBirth = ref(props.pamphlet?.date_of_birth ?? '');
const dateOfPassing = ref(props.pamphlet?.date_of_passing ?? '');
const shortText = ref(props.pamphlet?.short_text ?? '');
const dateFormat = ref<'d M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y'>(
    (props.pamphlet?.date_format as 'd M Y' | 'd/m/Y' | 'Y-m-d' | 'j F Y') ?? 'd M Y',
);
const imageShape = ref<'circle' | 'square'>(
    props.pamphlet?.image_shape === 'circle' ? 'circle' : 'square',
);
const imageCropMode = ref<'cover' | 'contain'>(
    props.pamphlet?.image_crop_mode === 'contain' ? 'contain' : 'cover',
);
const selectedBackgroundId = ref<string | null>(props.pamphlet?.background_id ?? null);
const uploadedImagePreviewUrl = ref<string | null>(existingImageUrl);
const uploadedFileName = ref<string | null>(null);
const headingColor = ref(props.pamphlet?.heading_color ?? '#000000');
const nameColor = ref(props.pamphlet?.name_color ?? '#000000');
const shortTextColor = ref(props.pamphlet?.short_text_color ?? '#000000');
const datesColor = ref(props.pamphlet?.dates_color ?? '#000000');
const layout = ref<PamphletLayoutData>(normalizePamphletLayout(props.pamphlet?.layout));
const selectedBlock = ref<PamphletBlockKey | null>('heading');
const settingsOpen = ref(false);
const imageInput = ref<HTMLInputElement | null>(null);

const selectedBackground = computed(
    () => props.backgrounds.find((background) => background.id === selectedBackgroundId.value) ?? null,
);

const isFormComplete = computed(
    () =>
        heading.value.trim() !== '' &&
        personFullName.value.trim() !== '' &&
        shortText.value.trim() !== '' &&
        dateOfBirth.value !== '' &&
        dateOfPassing.value !== '' &&
        selectedBackgroundId.value !== null &&
        uploadedImagePreviewUrl.value !== null,
);

const formAction = computed(() =>
    isEditing.value && props.pamphlet !== null ? `/pamphlets/${props.pamphlet.id}` : '/pamphlets',
);

const formMethod = computed(() => (isEditing.value ? 'put' : 'post'));
const pageTitle = computed(() => (isEditing.value ? 'Edit Memorial Pamphlet' : 'Create Memorial Pamphlet'));
const submitLabel = computed(() => (isEditing.value ? 'Save changes' : 'Continue'));

const backHref = computed(() => {
    if (isEditing.value && props.pamphlet !== null) {
        return `/pamphlets/${props.pamphlet.id}`;
    }

    return page.props.auth?.user ? '/dashboard' : '/';
});

const backLabel = computed(() => {
    if (isEditing.value) {
        return 'Back to review';
    }

    return page.props.auth?.user ? 'Back to dashboard' : 'Back to home';
});

const selectedBlockLabel = computed(() => {
    switch (selectedBlock.value) {
        case 'heading':
            return 'Heading';
        case 'name':
            return 'Name';
        case 'dates':
            return 'Dates';
        case 'tribute':
            return 'Tribute';
        case 'photo':
            return 'Photo';
        default:
            return 'Select a block';
    }
});

const selectedTextColor = computed({
    get: (): string => {
        switch (selectedBlock.value) {
            case 'heading':
                return headingColor.value;
            case 'name':
                return nameColor.value;
            case 'dates':
                return datesColor.value;
            case 'tribute':
                return shortTextColor.value;
            default:
                return '#000000';
        }
    },
    set: (value: string): void => {
        switch (selectedBlock.value) {
            case 'heading':
                headingColor.value = value;
                break;
            case 'name':
                nameColor.value = value;
                break;
            case 'dates':
                datesColor.value = value;
                break;
            case 'tribute':
                shortTextColor.value = value;
                break;
        }
    },
});

const canEditTextStyle = computed(
    () =>
        selectedBlock.value === 'heading' ||
        selectedBlock.value === 'name' ||
        selectedBlock.value === 'dates' ||
        selectedBlock.value === 'tribute',
);

const layoutJson = computed(() => JSON.stringify(layout.value));

const adjustFontSize = (delta: number): void => {
    if (!canEditTextStyle.value || selectedBlock.value === null || selectedBlock.value === 'photo') {
        return;
    }

    const key = selectedBlock.value;
    const next = clonePamphletLayout(layout.value);
    next[key] = {
        ...next[key],
        fontSize: Math.max(1.5, Math.min(14, Math.round((next[key].fontSize + delta) * 10) / 10)),
    };
    layout.value = next;
};

const assignImageFile = (file: File | null): void => {
    if (uploadedImagePreviewUrl.value !== null && uploadedImagePreviewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(uploadedImagePreviewUrl.value);
    }

    uploadedImagePreviewUrl.value = null;
    uploadedFileName.value = null;

    if (file === null || imageInput.value === null) {
        if (imageInput.value !== null) {
            imageInput.value.value = '';
        }

        return;
    }

    const transfer = new DataTransfer();
    transfer.items.add(file);
    imageInput.value.files = transfer.files;
    uploadedImagePreviewUrl.value = URL.createObjectURL(file);
    uploadedFileName.value = file.name;
};

const handleImageChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    assignImageFile(target.files?.[0] ?? null);
};

const handleImageDrop = (event: DragEvent): void => {
    event.preventDefault();
    const file = event.dataTransfer?.files?.[0] ?? null;

    if (file !== null && file.type.startsWith('image/')) {
        assignImageFile(file);
    }
};

const clearImage = (): void => {
    assignImageFile(null);

    if (existingImageUrl !== null) {
        uploadedImagePreviewUrl.value = existingImageUrl;
        uploadedFileName.value = null;
    }
};

onBeforeUnmount(() => {
    if (uploadedImagePreviewUrl.value !== null && uploadedImagePreviewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(uploadedImagePreviewUrl.value);
    }
});
</script>

<template>
    <Head :title="pageTitle" />

    <MarketingLayout :can-register="canRegister" hide-footer>
        <Form
            :action="formAction"
            :method="formMethod"
            enctype="multipart/form-data"
            class="flex min-h-[calc(100vh-4rem)] min-w-0 flex-col"
            #default="{ errors, processing }"
        >
            <input type="hidden" name="heading" :value="heading" />
            <input type="hidden" name="person_full_name" :value="personFullName" />
            <input type="hidden" name="short_text" :value="shortText" />
            <input type="hidden" name="font_family" :value="fontFamily" />
            <input type="hidden" name="date_format" :value="dateFormat" />
            <input type="hidden" name="date_of_birth" :value="dateOfBirth" />
            <input type="hidden" name="date_of_passing" :value="dateOfPassing" />
            <input type="hidden" name="image_shape" :value="imageShape" />
            <input type="hidden" name="image_crop_mode" :value="imageCropMode" />
            <input type="hidden" name="background_id" :value="selectedBackgroundId ?? ''" />
            <input type="hidden" name="heading_color" :value="headingColor" />
            <input type="hidden" name="name_color" :value="nameColor" />
            <input type="hidden" name="short_text_color" :value="shortTextColor" />
            <input type="hidden" name="dates_color" :value="datesColor" />
            <input type="hidden" name="layout" :value="layoutJson" />
            <input
                ref="imageInput"
                id="image"
                name="image"
                type="file"
                accept="image/*"
                class="sr-only"
                :required="existingImageUrl === null"
                @change="handleImageChange"
            />

            <div
                class="sticky top-16 z-40 flex flex-wrap items-center gap-2 border-b border-border/80 bg-background/95 px-3 py-2 backdrop-blur sm:gap-3 sm:px-4"
            >
                <Link :href="backHref" class="text-muted-foreground text-sm hover:text-foreground">
                    {{ backLabel }}
                </Link>

                <div class="mx-1 hidden h-5 w-px bg-border sm:block" />

                <p class="text-sm font-medium">{{ selectedBlockLabel }}</p>

                <template v-if="canEditTextStyle">
                    <label class="flex items-center gap-2 text-sm">
                        <span class="text-muted-foreground hidden sm:inline">Colour</span>
                        <input
                            v-model="selectedTextColor"
                            type="color"
                            class="size-8 cursor-pointer rounded border border-border bg-transparent p-0"
                            :aria-label="`${selectedBlockLabel} colour`"
                        />
                    </label>

                    <div class="flex items-center gap-1 rounded-md border border-border p-0.5">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="size-8"
                            aria-label="Decrease font size"
                            @click="adjustFontSize(-0.5)"
                        >
                            <Minus class="size-4" />
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="size-8"
                            aria-label="Increase font size"
                            @click="adjustFontSize(0.5)"
                        >
                            <Plus class="size-4" />
                        </Button>
                    </div>
                </template>

                <div class="ml-auto flex items-center gap-2">
                    <Sheet v-model:open="settingsOpen">
                        <SheetTrigger as-child>
                            <Button type="button" variant="outline" size="sm" class="lg:hidden">
                                <PanelLeft class="size-4" />
                                Settings
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[20rem] overflow-y-auto sm:max-w-sm">
                            <SheetHeader>
                                <SheetTitle>Design settings</SheetTitle>
                            </SheetHeader>
                            <div class="px-1 pb-6">
                                <PamphletSettingsPanel
                                    v-model:heading="heading"
                                    v-model:person-full-name="personFullName"
                                    v-model:short-text="shortText"
                                    v-model:font-family="fontFamily"
                                    v-model:date-format="dateFormat"
                                    v-model:date-of-birth="dateOfBirth"
                                    v-model:date-of-passing="dateOfPassing"
                                    v-model:image-shape="imageShape"
                                    v-model:image-crop-mode="imageCropMode"
                                    v-model:selected-background-id="selectedBackgroundId"
                                    :backgrounds="backgrounds"
                                    :uploaded-image-preview-url="uploadedImagePreviewUrl"
                                    :uploaded-file-name="uploadedFileName"
                                    :existing-image-url="existingImageUrl"
                                    :errors="errors"
                                    @open-image-picker="imageInput?.click()"
                                    @image-drop="handleImageDrop"
                                    @clear-image="clearImage"
                                />
                            </div>
                        </SheetContent>
                    </Sheet>

                    <Button
                        type="submit"
                        :disabled="processing || !isFormComplete"
                        class="bg-brand hover:bg-brand-strong"
                    >
                        {{ submitLabel }}
                    </Button>
                </div>
            </div>

            <div class="flex min-h-0 min-w-0 flex-1">
                <aside
                    class="hidden w-72 shrink-0 overflow-y-auto border-r border-border bg-card px-4 py-5 lg:block xl:w-80"
                >
                    <div class="mb-4">
                        <h1 class="font-semibold text-lg">{{ pageTitle }}</h1>
                        <p class="text-muted-foreground text-sm">
                            Edit content and globals here. Drag blocks on the canvas.
                        </p>
                    </div>

                    <PamphletSettingsPanel
                        v-model:heading="heading"
                        v-model:person-full-name="personFullName"
                        v-model:short-text="shortText"
                        v-model:font-family="fontFamily"
                        v-model:date-format="dateFormat"
                        v-model:date-of-birth="dateOfBirth"
                        v-model:date-of-passing="dateOfPassing"
                        v-model:image-shape="imageShape"
                        v-model:image-crop-mode="imageCropMode"
                        v-model:selected-background-id="selectedBackgroundId"
                        :backgrounds="backgrounds"
                        :uploaded-image-preview-url="uploadedImagePreviewUrl"
                        :uploaded-file-name="uploadedFileName"
                        :existing-image-url="existingImageUrl"
                        :errors="errors"
                        @open-image-picker="imageInput?.click()"
                        @image-drop="handleImageDrop"
                        @clear-image="clearImage"
                    />
                </aside>

                <div class="min-w-0 flex-1 overflow-y-auto bg-muted/20 px-3 py-6 sm:px-6 lg:px-8">
                    <div class="mx-auto w-full max-w-xl space-y-3">
                        <p class="text-center text-muted-foreground text-sm">
                            Select a block, then drag it or change colour and size from the toolbar.
                        </p>

                        <PamphletCanvasEditor
                            v-model:selected-block="selectedBlock"
                            v-model:layout="layout"
                            v-model:heading="heading"
                            v-model:person-full-name="personFullName"
                            v-model:short-text="shortText"
                            :date-of-birth="dateOfBirth"
                            :date-of-passing="dateOfPassing"
                            :date-format="dateFormat"
                            :font-family="fontFamily"
                            :heading-color="headingColor"
                            :name-color="nameColor"
                            :dates-color="datesColor"
                            :short-text-color="shortTextColor"
                            :image-shape="imageShape"
                            :image-crop-mode="imageCropMode"
                            :uploaded-image-preview-url="uploadedImagePreviewUrl"
                            :background-asset-path="selectedBackground?.asset_path ?? null"
                        />

                        <div class="space-y-1">
                            <InputError :message="errors.heading" />
                            <InputError :message="errors.person_full_name" />
                            <InputError :message="errors.short_text" />
                            <InputError :message="errors.image" />
                            <InputError :message="errors.background_id" />
                            <InputError :message="errors.layout" />
                        </div>
                    </div>
                </div>
            </div>
        </Form>
    </MarketingLayout>
</template>
