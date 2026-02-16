<template>
    <div>
        <div class="mb-6">
            <h1 class="page-title">Log Keselamatan</h1>
            <p class="page-subtitle">Pantau peristiwa keselamatan dan potensi ancaman</p>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <select v-model="filters.event_type" @change="fetchLogs" class="input-field">
                        <option value="">Semua Jenis Peristiwa</option>
                        <option v-for="type in eventTypes" :key="type" :value="type">{{ type }}</option>
                    </select>
                    <select v-model="filters.severity" @change="fetchLogs" class="input-field">
                        <option value="">Semua Tahap Keterukan</option>
                        <option v-for="s in severityLevels" :key="s.value" :value="s.value">{{ s.label }}</option>
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
                <template #cell-event_type="{ item }">
                    <Badge color="blue">{{ item.event_type }}</Badge>
                </template>
                <template #cell-severity="{ item }">
                    <Badge :color="severityColor(item.severity)">{{ item.severity }}</Badge>
                </template>
                <template #cell-ip_address="{ item }">
                    <span class="font-mono text-xs text-gray-500">{{ item.ip_address || '-' }}</span>
                </template>
                <template #cell-description="{ item }">
                    <span class="text-sm text-gray-600 max-w-xs truncate block">{{ item.description }}</span>
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
const filters = reactive({ event_type: '', severity: '', date_from: '', date_to: '' });

const eventTypes = [
    'failed_login',
    'password_reset',
    'account_locked',
    'unauthorized_access',
    'ip_blocked',
    'suspicious_activity',
    'brute_force',
    'session_hijack',
];

const severityLevels = [
    { value: 'low', label: 'Rendah' },
    { value: 'medium', label: 'Sederhana' },
    { value: 'high', label: 'Tinggi' },
    { value: 'critical', label: 'Kritikal' },
];

const columns = [
    { key: 'event_type', label: 'Jenis Peristiwa' },
    { key: 'severity', label: 'Keterukan' },
    { key: 'ip_address', label: 'Alamat IP' },
    { key: 'description', label: 'Penerangan' },
    { key: 'created_at', label: 'Tarikh' },
];

function severityColor(severity) {
    if (!severity) return 'gray';
    const map = {
        low: 'green',
        medium: 'yellow',
        high: 'orange',
        critical: 'red',
    };
    return map[severity.toLowerCase()] || 'gray';
}

async function fetchLogs(page = 1) {
    loading.value = true;
    try {
        const params = { ...filters, page, per_page: 15 };
        Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });
        const { data } = await logsApi.security(params);
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
