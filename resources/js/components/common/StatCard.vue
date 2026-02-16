<template>
    <div class="card h-full">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg p-3" :class="bgClass">
                    <svg class="h-6 w-6" :class="iconClass" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
                    </svg>
                </div>
                <div class="ml-4 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 truncate">{{ title }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ value }}</p>
                </div>
            </div>
            <div v-if="$slots.footer" class="mt-3 pt-3 border-t border-gray-100">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    color: { type: String, default: 'blue' },
});

const bgClass = computed(() => ({
    blue: 'bg-primary-50',
    green: 'bg-green-50',
    yellow: 'bg-yellow-50',
    red: 'bg-red-50',
    indigo: 'bg-indigo-50',
})[props.color] || 'bg-primary-50');

const iconClass = computed(() => ({
    blue: 'text-primary-600',
    green: 'text-green-600',
    yellow: 'text-yellow-600',
    red: 'text-red-600',
    indigo: 'text-indigo-600',
})[props.color] || 'text-primary-600');
</script>
