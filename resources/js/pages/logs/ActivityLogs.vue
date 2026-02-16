<template>
    <div>
        <div class="mb-6">
            <h1 class="page-title">Log Aktiviti</h1>
            <p class="page-subtitle">Jejaki semua aktiviti pengguna dalam sistem</p>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <input
                        v-model="filters.search"
                        @input="debouncedFetch"
                        type="text"
                        placeholder="Cari mengikut penerangan..."
                        class="input-field"
                    />
                    <input
                        v-model="filters.user"
                        @input="debouncedFetch"
                        type="text"
                        placeholder="Tapis mengikut pengguna..."
                        class="input-field"
                    />
                    <input
                        v-model="filters.date_from"
                        @change="fetchLogs"
                        type="date"
                        class="input-field"
                        placeholder="From date"
                    />
                    <input
                        v-model="filters.date_to"
                        @change="fetchLogs"
                        type="date"
                        class="input-field"
                        placeholder="To date"
                    />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <DataTable :columns="columns" :items="logs" :loading="loading">
                <template #cell-user_name="{ item }">
                    <span class="font-medium text-gray-900">{{ item.user_name || '-' }}</span>
                </template>
                <template #cell-action="{ item }">
                    <Badge :color="actionColor(item.action)">{{ item.action }}</Badge>
                </template>
                <template #cell-description="{ item }">
                    <span class="text-sm text-gray-600 max-w-xs truncate block">{{ item.description }}</span>
                </template>
                <template #cell-ip_address="{ item }">
                    <span class="font-mono text-xs text-gray-500">{{ item.ip_address || '-' }}</span>
                </template>
                <template #cell-created_at="{ item }">
                    <span class="text-xs text-gray-500">{{ formatDateTime(item.created_at) }}</span>
                </template>
            </DataTable>
            <div class="px-4 border-t border-gray-100">
                <Pagination
                    :current-page="pagination.currentPage"
                    :last-page="pagination.lastPage"
                    :total="pagination.total"
                    @page-change="goToPage"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import logsApi from '../../api/logs';
import { formatDateTime } from '../../utils/formatters';
import DataTable from '../../components/common/DataTable.vue';
import Badge from '../../components/common/Badge.vue';
import Pagination from '../../components/common/Pagination.vue';

const logs = ref([]);
const loading = ref(true);
const pagination = reactive({ currentPage: 1, lastPage: 1, total: 0 });
const filters = reactive({ search: '', user: '', date_from: '', date_to: '' });

let debounceTimer;
const debouncedFetch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchLogs(), 400);
};

const columns = [
    { key: 'user_name', label: 'Pengguna' },
    { key: 'action', label: 'Tindakan' },
    { key: 'description', label: 'Penerangan' },
    { key: 'ip_address', label: 'Alamat IP' },
    { key: 'created_at', label: 'Tarikh' },
];

function actionColor(action) {
    if (!action) return 'gray';
    const lower = action.toLowerCase();
    const map = {
        created: 'green',
        updated: 'blue',
        deleted: 'red',
        login: 'indigo',
        logout: 'gray',
        exported: 'yellow',
        imported: 'orange',
    };
    return map[lower] || 'gray';
}

async function fetchLogs(page = 1) {
    loading.value = true;
    try {
        const params = { ...filters, page, per_page: 15 };
        Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });
        const { data } = await logsApi.activity(params);
        logs.value = data.data;
        pagination.currentPage = data.meta?.current_page || 1;
        pagination.lastPage = data.meta?.last_page || 1;
        pagination.total = data.meta?.total || 0;
    } catch {
        logs.value = [];
    }
    loading.value = false;
}

function goToPage(page) {
    fetchLogs(page);
}

onMounted(() => fetchLogs());
</script>
