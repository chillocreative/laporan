<template>
    <div v-if="lastPage > 1" class="flex items-center justify-between px-2 py-3">
        <p class="text-sm text-gray-500">
            Halaman <span class="font-medium">{{ currentPage }}</span> daripada <span class="font-medium">{{ lastPage }}</span>
            <span class="hidden sm:inline"> ({{ total }} jumlah)</span>
        </p>
        <nav class="flex gap-1">
            <button
                @click="$emit('page-change', currentPage - 1)"
                :disabled="currentPage <= 1"
                class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
            >Sebelum</button>
            <template v-for="page in visiblePages" :key="page">
                <span v-if="page === '...'" class="px-2 py-1.5 text-sm text-gray-400">...</span>
                <button
                    v-else
                    @click="$emit('page-change', page)"
                    :class="page === currentPage ? 'bg-primary-500 text-white border-primary-500' : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
                    class="px-3 py-1.5 text-sm rounded-lg border"
                >{{ page }}</button>
            </template>
            <button
                @click="$emit('page-change', currentPage + 1)"
                :disabled="currentPage >= lastPage"
                class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
            >Seterusnya</button>
        </nav>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
    total: { type: Number, default: 0 },
});

defineEmits(['page-change']);

const visiblePages = computed(() => {
    const pages = [];
    const cur = props.currentPage;
    const last = props.lastPage;

    if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i);
        return pages;
    }

    pages.push(1);
    if (cur > 3) pages.push('...');
    for (let i = Math.max(2, cur - 1); i <= Math.min(last - 1, cur + 1); i++) {
        pages.push(i);
    }
    if (cur < last - 2) pages.push('...');
    pages.push(last);
    return pages;
});
</script>
