<template>
    <Modal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" :title="title" size="sm">
        <p class="text-sm text-gray-600">{{ message }}</p>
        <template #footer>
            <div class="flex justify-end gap-3">
                <button class="btn-secondary btn-sm" @click="$emit('update:modelValue', false)">Batal</button>
                <button :class="confirmClass" class="btn-sm" @click="$emit('confirm')">{{ confirmText }}</button>
            </div>
        </template>
    </Modal>
</template>

<script setup>
import { computed } from 'vue';
import Modal from './Modal.vue';

const props = defineProps({
    modelValue: Boolean,
    title: { type: String, default: 'Sahkan Tindakan' },
    message: { type: String, default: 'Adakah anda pasti?' },
    confirmText: { type: String, default: 'Sahkan' },
    danger: { type: Boolean, default: false },
});

defineEmits(['update:modelValue', 'confirm']);

const confirmClass = computed(() => props.danger ? 'btn-danger' : 'btn-primary');
</script>
