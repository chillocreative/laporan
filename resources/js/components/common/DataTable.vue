<template>
    <div
        ref="scrollContainer"
        class="overflow-x-auto cursor-grab active:cursor-grabbing select-none"
        @mousedown="startDragging"
        @mousemove="onDragging"
        @mouseup="stopDragging"
        @mouseleave="stopDragging"
    >
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                        :class="col.class"
                    >
                        {{ col.label }}
                    </th>
                    <th v-if="$slots.actions" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading">
                    <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-12 text-center">
                        <LoadingSpinner text="Loading..." />
                    </td>
                </tr>
                <tr v-else-if="!items.length">
                    <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-12 text-center text-sm text-gray-500">
                        {{ emptyText }}
                    </td>
                </tr>
                <tr v-else v-for="(item, idx) in items" :key="item.id || idx" class="hover:bg-gray-50 transition-colors">
                    <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap" :class="col.cellClass">
                        <slot :name="`cell-${col.key}`" :item="item" :value="getNestedValue(item, col.key)">
                            {{ getNestedValue(item, col.key) }}
                        </slot>
                    </td>
                    <td v-if="$slots.actions" class="px-4 py-3 text-sm text-right whitespace-nowrap">
                        <slot name="actions" :item="item" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import LoadingSpinner from './LoadingSpinner.vue';

defineProps({
    columns: { type: Array, required: true },
    items: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    emptyText: { type: String, default: 'No records found.' },
});

function getNestedValue(obj, path) {
    return path.split('.').reduce((o, k) => (o || {})[k], obj) ?? '-';
}

// Drag-to-scroll functionality
const scrollContainer = ref(null);
const isDragging = ref(false);
const startX = ref(0);
const scrollLeft = ref(0);

function startDragging(e) {
    if (!scrollContainer.value) return;
    isDragging.value = true;
    startX.value = e.pageX - scrollContainer.value.offsetLeft;
    scrollLeft.value = scrollContainer.value.scrollLeft;
}

function onDragging(e) {
    if (!isDragging.value || !scrollContainer.value) return;
    e.preventDefault();
    const x = e.pageX - scrollContainer.value.offsetLeft;
    const walk = (x - startX.value) * 2; // Scroll speed multiplier
    scrollContainer.value.scrollLeft = scrollLeft.value - walk;
}

function stopDragging() {
    isDragging.value = false;
}
</script>
