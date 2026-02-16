<template>
    <span :class="classes" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    color: { type: String, default: 'gray' },
});

const classes = computed(() => {
    const map = {
        green: 'bg-green-100 text-green-800',
        yellow: 'bg-yellow-100 text-yellow-800',
        red: 'bg-red-100 text-red-800',
        blue: 'bg-blue-100 text-blue-800',
        indigo: 'bg-indigo-100 text-indigo-800',
        orange: 'bg-orange-100 text-orange-800',
        gray: 'bg-gray-100 text-gray-800',
    };
    return map[props.color] || map.gray;
});
</script>
