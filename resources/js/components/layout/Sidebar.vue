<template>
    <div class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col bg-gradient-to-b from-primary-800 to-primary-900">
        <!-- Logo -->
        <div class="flex items-center h-16 px-4 bg-primary-900/50">
            <img src="/jata.png" alt="Logo" class="h-10 w-10 object-contain flex-shrink-0" />
            <span class="ml-3 text-white font-semibold text-sm truncate">{{ settingsStore.systemName }}</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <template v-for="item in navItems" :key="item.name">
                <div v-if="item.heading && item.show" class="pt-4 pb-2 px-3">
                    <p class="text-xs font-semibold text-primary-300 uppercase tracking-wider">{{ item.heading }}</p>
                </div>
                <router-link
                    v-if="!item.heading && item.show"
                    :to="{ name: item.route }"
                    :class="[
                        isActive(item.route)
                            ? 'bg-white/15 text-white'
                            : 'text-primary-100 hover:bg-white/10 hover:text-white',
                        'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-150'
                    ]"
                >
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                    <span
                        v-if="item.badge && item.badge > 0"
                        class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-red-500 rounded-full"
                    >
                        {{ item.badge > 99 ? '99+' : item.badge }}
                    </span>
                </router-link>
            </template>
        </nav>

    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuth } from '../../composables/useAuth';
import { useSettingsStore } from '../../stores/settings';
import usersApi from '../../api/users';

const route = useRoute();
const auth = useAuth();
const settingsStore = useSettingsStore();
const pendingUsersCount = ref(0);
let pollInterval = null;

async function fetchPendingCount() {
    if (!auth.hasAnyRole(['super-admin', 'admin'])) return;
    try {
        const { data } = await usersApi.pendingCount();
        pendingUsersCount.value = data.data.count;
    } catch {}
}

onMounted(() => {
    fetchPendingCount();
    pollInterval = setInterval(fetchPendingCount, 60000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

function isActive(routeName) {
    if (route.name === routeName) return true;
    if (route.name?.startsWith?.(routeName + '.')) return true;
    if (routeName?.endsWith?.('.index')) {
        return route.name?.startsWith?.(routeName.replace(/\.index$/, '.'));
    }
    return false;
}

const navItems = computed(() => [
    {
        label: 'Dashboard', route: 'dashboard', show: true,
        icon: 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    },
    { heading: 'Laporan', show: true },
    {
        label: 'Semua Laporan', route: 'reports.index', show: true,
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    },
    {
        label: 'Laporan Baru', route: 'reports.create', show: auth.hasPermission('reports.create'),
        icon: 'M12 4.5v15m7.5-7.5h-15',
    },
    { heading: 'Pengurusan', show: auth.hasAnyRole(['super-admin', 'admin']) },
    {
        label: 'Pengguna', route: 'users.index', show: auth.hasAnyRole(['super-admin', 'admin']),
        badge: pendingUsersCount.value,
        icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    },
    {
        label: 'Kategori', route: 'categories.index', show: auth.hasAnyRole(['super-admin', 'admin']),
        icon: 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z',
    },
    {
        label: 'Peranan', route: 'roles.index', show: auth.isSuperAdmin.value,
        icon: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
    },
    { heading: 'Sistem', show: auth.isSuperAdmin.value },
    {
        label: 'Tetapan', route: 'settings', show: auth.isSuperAdmin.value,
        icon: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z',
    },
    {
        label: 'Log Aktiviti', route: 'logs.activity', show: auth.isSuperAdmin.value,
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        label: 'Log Keselamatan', route: 'logs.security', show: auth.isSuperAdmin.value,
        icon: 'M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z',
    },
    {
        label: 'Log AI', route: 'logs.ai', show: auth.isSuperAdmin.value,
        icon: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
    },
]);
</script>
