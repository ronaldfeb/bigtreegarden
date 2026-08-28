<script setup lang="ts">
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    Heading2,
    Heading3,
    Italic,
    List,
    ListOrdered,
} from 'lucide-vue-next';
import { onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        name: string;
        id?: string;
        defaultValue?: string;
        modelValue?: string;
        class?: string;
    }>(),
    {
        id: undefined,
        defaultValue: '',
        modelValue: undefined,
        class: undefined,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const html = ref(props.modelValue ?? props.defaultValue ?? '');

const editor = useEditor({
    content: html.value,
    extensions: [StarterKit],
    editorProps: {
        attributes: {
            class: 'rich-text min-h-64 px-3 py-2 focus:outline-none',
        },
    },
    onUpdate: ({ editor: currentEditor }) => {
        html.value = currentEditor.getHTML();
        emit('update:modelValue', html.value);
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (value === undefined || value === html.value) {
            return;
        }

        html.value = value;
        editor.value?.commands.setContent(value, { emitUpdate: false });
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

function toggleBold(): void {
    editor.value?.chain().focus().toggleBold().run();
}

function toggleItalic(): void {
    editor.value?.chain().focus().toggleItalic().run();
}

function toggleHeading(level: 2 | 3): void {
    editor.value?.chain().focus().toggleHeading({ level }).run();
}

function toggleBulletList(): void {
    editor.value?.chain().focus().toggleBulletList().run();
}

function toggleOrderedList(): void {
    editor.value?.chain().focus().toggleOrderedList().run();
}
</script>

<template>
    <div :class="cn('overflow-hidden rounded-md border border-input bg-transparent shadow-xs', props.class)">
        <div
            v-if="editor"
            class="flex flex-wrap gap-1 border-b border-input bg-muted/30 p-1"
        >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                :class="editor.isActive('bold') ? 'bg-muted' : ''"
                @click="toggleBold"
            >
                <Bold class="size-4" />
                <span class="sr-only">Bold</span>
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                :class="editor.isActive('italic') ? 'bg-muted' : ''"
                @click="toggleItalic"
            >
                <Italic class="size-4" />
                <span class="sr-only">Italic</span>
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                :class="editor.isActive('heading', { level: 2 }) ? 'bg-muted' : ''"
                @click="toggleHeading(2)"
            >
                <Heading2 class="size-4" />
                <span class="sr-only">Heading 2</span>
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                :class="editor.isActive('heading', { level: 3 }) ? 'bg-muted' : ''"
                @click="toggleHeading(3)"
            >
                <Heading3 class="size-4" />
                <span class="sr-only">Heading 3</span>
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                :class="editor.isActive('bulletList') ? 'bg-muted' : ''"
                @click="toggleBulletList"
            >
                <List class="size-4" />
                <span class="sr-only">Bullet list</span>
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                :class="editor.isActive('orderedList') ? 'bg-muted' : ''"
                @click="toggleOrderedList"
            >
                <ListOrdered class="size-4" />
                <span class="sr-only">Numbered list</span>
            </Button>
        </div>

        <EditorContent :editor="editor" />

        <input type="hidden" :id="id" :name="name" :value="html" />
    </div>
</template>
