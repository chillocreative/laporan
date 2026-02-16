<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile sidebar overlay -->
        <MobileSidebar :open="sidebarOpen" @close="sidebarOpen = false" />

        <!-- Desktop sidebar -->
        <Sidebar class="hidden lg:flex" />

        <!-- Main content -->
        <div class="lg:pl-64">
            <Topbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

            <main class="p-4 sm:p-6 lg:p-8">
                <!-- Notifications -->
                <div class="fixed top-4 right-4 z-50 space-y-2 w-80">
                    <transition-group name="notification">
                        <div
                            v-for="n in notifications"
                            :key="n.id"
                            :class="notifClass(n.type)"
                            class="rounded-lg px-4 py-3 shadow-lg text-sm font-medium flex items-center justify-between"
                        >
                            <span>{{ n.message }}</span>
                            <button @click="removeNotif(n.id)" class="ml-3 opacity-70 hover:opacity-100">&times;</button>
                        </div>
                    </transition-group>
                </div>

                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useNotificationStore } from '../../stores/notifications';
import Sidebar from './Sidebar.vue';
import MobileSidebar from './MobileSidebar.vue';
import Topbar from './Topbar.vue';

const sidebarOpen = ref(false);
const notifStore = useNotificationStore();
const notifications = computed(() => notifStore.items);
const removeNotif = (id) => notifStore.remove(id);

function notifClass(type) {
    const map = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-primary-500 text-white',
    };
    return map[type] || map.info;
}
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.3s ease;
}
.notification-enter-from {
    opacity: 0;
    transform: translateX(30px);
}
.notification-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>
