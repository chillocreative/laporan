<template>
    <teleport to="body">
        <transition name="modal">
            <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60" @click="close"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-h-[90vh] overflow-y-auto" :class="sizeClass">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                        <button @click="close" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-6 py-4">
                        <slot />
                    </div>
                    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    title: { type: String, default: '' },
    size: { type: String, default: 'md' },
});

const emit = defineEmits(['update:modelValue']);

const sizeClass = computed(() => ({
    sm: 'max-w-md',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
})[props.size] || 'max-w-lg');

function close() {
    emit('update:modelValue', false);
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
