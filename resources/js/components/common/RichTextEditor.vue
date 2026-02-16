<template>
    <div class="rich-text-editor">
        <QuillEditor
            ref="quillRef"
            :content="modelValue"
            content-type="html"
            :options="editorOptions"
            @update:content="$emit('update:modelValue', $event)"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Write your report here...' },
});

defineEmits(['update:modelValue']);

const quillRef = ref(null);

const editorOptions = {
    modules: {
        toolbar: [
            [{ header: [2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote'],
            ['link'],
            ['clean'],
        ],
    },
    placeholder: 'Write your report here...',
    theme: 'snow',
};
</script>

<style>
.rich-text-editor .ql-container {
    min-height: 200px;
    font-size: 0.875rem;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}
.rich-text-editor .ql-toolbar {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    background: #f9fafb;
}
.rich-text-editor .ql-toolbar,
.rich-text-editor .ql-container {
    border-color: #d1d5db !important;
}
.rich-text-editor .ql-editor {
    min-height: 200px;
}
.rich-text-editor .ql-editor.ql-blank::before {
    color: #9ca3af;
    font-style: normal;
}
</style>
