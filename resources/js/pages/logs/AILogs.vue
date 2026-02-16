<template>
    <div>
        <div class="mb-6">
            <h1 class="page-title">Log AI</h1>
            <p class="page-subtitle">Pantau penggunaan dan kos API OpenAI</p>
        </div>

        <!-- Today's Usage Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <StatCard
                title="Permintaan Hari Ini"
                :value="usageStats.total_requests ?? '-'"
                :icon="iconPaths.requests"
                color="blue"
            />
            <StatCard
                title="Jumlah Token Hari Ini"
                :value="usageStats.total_tokens != null ? usageStats.total_tokens.toLocaleString() : '-'"
                :icon="iconPaths.tokens"
                color="indigo"
            />
            <StatCard
                title="Anggaran Kos Hari Ini"
                :value="usageStats.estimated_cost != null ? formatCurrency(usageStats.estimated_cost) : '-'"
                :icon="iconPaths.cost"
                color="green"
            />
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <select v-model="filters.status" @change="fetchLogs" class="input-field">
                        <option value="">Semua Status</option>
                        <option value="success">Berjaya</option>
                        <option value="failed">Gagal</option>
                    </select>
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
                <template #cell-report_id="{ item }">
                    <span class="font-mono text-xs text-gray-700">{{ item.report_id || '-' }}</span>
                </template>
                <template #cell-model="{ item }">
                    <span class="text-sm font-medium text-gray-700">{{ item.model || '-' }}</span>
                </template>
                <template #cell-prompt_tokens="{ item }">
                    <span class="text-sm text-gray-600">{{ item.prompt_tokens?.toLocaleString() ?? '-' }}</span>
                </template>
                <template #cell-completion_tokens="{ item }">
                    <span class="text-sm text-gray-600">{{ item.completion_tokens?.toLocaleString() ?? '-' }}</span>
                </template>
                <template #cell-total_tokens="{ item }">
                    <span class="text-sm font-medium text-gray-800">{{ item.total_tokens?.toLocaleString() ?? '-' }}</span>
                </template>
                <template #cell-estimated_cost="{ item }">
                    <span class="text-sm text-gray-600">{{ formatCurrency(item.estimated_cost) }}</span>
                </template>
                <template #cell-response_time="{ item }">
                    <span v-if="item.response_time != null" class="text-xs text-gray-500">{{ item.response_time }}ms</span>
                    <span v-else class="text-xs text-gray-400">-</span>
                </template>
                <template #cell-status="{ item }">
                    <Badge :color="item.status === 'success' ? 'green' : 'red'">{{ item.status }}</Badge>
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
import { formatDateTime, formatCurrency } from '../../utils/formatters';
import DataTable from '../../components/common/DataTable.vue';
import Badge from '../../components/common/Badge.vue';
import Pagination from '../../components/common/Pagination.vue';
import StatCard from '../../components/common/StatCard.vue';

const logs = ref([]);
const loading = ref(true);
const usageStats = reactive({ total_requests: null, total_tokens: null, estimated_cost: null });
const pagination = reactive({ currentPage: 1, lastPage: 1, total: 0 });
const filters = reactive({ status: '', date_from: '', date_to: '' });

const iconPaths = {
    requests: 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6',
    tokens: 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z',
    cost: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

const columns = [
    { key: 'report_id', label: 'ID Laporan' },
    { key: 'model', label: 'Model' },
    { key: 'prompt_tokens', label: 'Prompt' },
    { key: 'completion_tokens', label: 'Pelengkapan' },
    { key: 'total_tokens', label: 'Jumlah Token' },
    { key: 'estimated_cost', label: 'Kos' },
    { key: 'response_time', label: 'Masa Respons' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Tarikh' },
];

async function fetchUsageStats() {
    try {
        const { data } = await logsApi.aiTodayUsage();
        usageStats.total_requests = data.data?.total_requests ?? data.total_requests ?? 0;
        usageStats.total_tokens = data.data?.total_tokens ?? data.total_tokens ?? 0;
        usageStats.estimated_cost = data.data?.estimated_cost ?? data.estimated_cost ?? 0;
    } catch {
        // Keep default null values on error
    }
}

async function fetchLogs(page = 1) {
    loading.value = true;
    try {
        const params = { ...filters, page, per_page: 15 };
        Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });
        const { data } = await logsApi.ai(params);
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

onMounted(() => {
    fetchUsageStats();
    fetchLogs();
});
</script>
