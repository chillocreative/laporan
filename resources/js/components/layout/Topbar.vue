<template>
    <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
        <!-- Mobile menu button -->
        <button
            @click="$emit('toggle-sidebar')"
            class="lg:hidden -ml-1 p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <!-- Page title area -->
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-semibold text-gray-900 truncate">{{ pageTitle }}</h1>
        </div>

        <!-- Right section - User dropdown -->
        <div class="relative" ref="dropdownRef">
            <button
                @click="showDropdown = !showDropdown"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors"
            >
                <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ initials }}
                </div>
                <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth.user.value?.name }}</span>
                <svg class="hidden sm:block h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <!-- Dropdown menu -->
            <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-if="showDropdown"
                    class="absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-lg ring-1 ring-black/5 py-1"
                >
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth.user.value?.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth.user.value?.email }}</p>
                    </div>

                    <router-link
                        :to="{ name: 'profile' }"
                        @click="showDropdown = false"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Profil
                    </router-link>

                    <div class="border-t border-gray-100">
                        <button
                            @click="handleLogout"
                            class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Log Keluar
                        </button>
                    </div>
                </div>
            </transition>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../../composables/useAuth';
import { useAuthStore } from '../../stores/auth';

defineEmits(['toggle-sidebar']);

const route = useRoute();
const router = useRouter();
const auth = useAuth();
const authStore = useAuthStore();

const showDropdown = ref(false);
const dropdownRef = ref(null);

const initials = computed(() => {
    const name = auth.user.value?.name || '';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
});

const pageTitle = computed(() => {
    const titles = {
        'dashboard': 'Dashboard',
        'reports.index': 'Laporan',
        'reports.create': 'Laporan Baru',
        'reports.show': 'Butiran Laporan',
        'reports.edit': 'Edit Laporan',
        'users.index': 'Pengguna',
        'users.create': 'Pengguna Baru',
        'users.edit': 'Edit Pengguna',
        'roles.index': 'Peranan',
        'roles.create': 'Peranan Baru',
        'roles.edit': 'Edit Peranan',
        'categories.index': 'Kategori',
        'settings': 'Tetapan',
        'logs.activity': 'Log Aktiviti',
        'logs.security': 'Log Keselamatan',
        'logs.ai': 'Log AI',
        'profile': 'Profil',
    };
    return titles[route.name] || 'Sistem Pelaporan';
});

function handleClickOutside(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        showDropdown.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));

async function handleLogout() {
    showDropdown.value = false;
    await authStore.logout();
    router.push({ name: 'login' });
}
</script>
