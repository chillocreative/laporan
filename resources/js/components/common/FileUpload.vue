<template>
    <div>
        <label class="label-text">{{ label }}</label>
        <div
            @dragover.prevent="dragover = true"
            @dragleave.prevent="dragover = false"
            @drop.prevent="handleDrop"
            :class="dragover ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
            class="mt-1 flex justify-center rounded-lg border-2 border-dashed px-6 py-8 transition-colors cursor-pointer"
            @click="$refs.fileInput.click()"
        >
            <div class="text-center">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">Klik atau seret fail di sini</p>
                <p class="mt-1 text-xs text-gray-400">Maksimum {{ maxFiles }} fail, {{ maxSizeMb }}MB setiap satu</p>
            </div>
        </div>
        <input ref="fileInput" type="file" :multiple="multiple" :accept="accept" class="hidden" @change="handleSelect" />

        <!-- File list -->
        <ul v-if="files.length" class="mt-3 space-y-2">
            <li v-for="(file, i) in files" :key="i" class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                <div class="flex items-center gap-2 min-w-0">
                    <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                    </svg>
                    <span class="truncate">{{ file.name }}</span>
                    <span class="text-gray-400 flex-shrink-0">({{ formatSize(file.size) }})</span>
                </div>
                <button @click.stop="removeFile(i)" class="ml-2 text-red-500 hover:text-red-700 flex-shrink-0">&times;</button>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    label: { type: String, default: 'Lampiran' },
    multiple: { type: Boolean, default: true },
    accept: { type: String, default: '.jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx' },
    maxFiles: { type: Number, default: 10 },
    maxSizeMb: { type: Number, default: 5 },
    modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);
const files = ref([...props.modelValue]);
const dragover = ref(false);

function handleSelect(e) {
    addFiles(Array.from(e.target.files));
    e.target.value = '';
}

function handleDrop(e) {
    dragover.value = false;
    addFiles(Array.from(e.dataTransfer.files));
}

function addFiles(newFiles) {
    const remaining = props.maxFiles - files.value.length;
    const toAdd = newFiles.slice(0, remaining);
    files.value.push(...toAdd);
    emit('update:modelValue', files.value);
}

function removeFile(index) {
    files.value.splice(index, 1);
    emit('update:modelValue', files.value);
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}
</script>
