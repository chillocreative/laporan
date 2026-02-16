<template>
    <teleport to="body">
        <transition name="sidebar">
            <div v-if="open" class="fixed inset-0 z-40 lg:hidden">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-900/70" @click="$emit('close')"></div>

                <!-- Sidebar panel -->
                <div class="fixed inset-y-0 left-0 w-64">
                    <Sidebar />
                </div>
            </div>
        </transition>
    </teleport>
</template>

<script setup>
import Sidebar from './Sidebar.vue';

defineProps({
    open: Boolean,
});

defineEmits(['close']);
</script>

<style scoped>
.sidebar-enter-active,
.sidebar-leave-active {
    transition: opacity 0.3s ease;
}
.sidebar-enter-from,
.sidebar-leave-to {
    opacity: 0;
}
</style>
