<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h1 class="page-title">Laporan</h1>
                <p class="page-subtitle">Urus dan jejaki semua laporan</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    v-if="auth.isSuperAdmin && pendingAnalysisCount > 0"
                    @click="confirmAnalyzePending"
                    :disabled="analyzingPending"
                    class="btn-secondary"
                    :title="`${pendingAnalysisCount} laporan belum dianalisis`"
                >
                    {{ analyzingPending ? 'Menganalisis...' : `Analisis Tertunggak (${pendingAnalysisCount})` }}
                </button>
                <router-link v-if="auth.hasPermission('reports.create')" :to="{ name: 'reports.create' }" class="btn-primary">
                    + Laporan Baru
                </router-link>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <input v-model="filters.search" @input="debouncedFetch" type="text" placeholder="Cari laporan..." class="input-field" />
                    <select v-model="filters.category" @change="fetchReports" class="input-field">
                        <option value="">Semua Jenis Aktiviti</option>
                        <option v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <select v-model="filters.risk_level" @change="fetchReports" class="input-field">
                        <option value="">Semua Tahap Risiko</option>
                        <option v-for="r in RISK_LEVELS" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                    <select v-if="auth.hasPermission('users.view-all')" v-model="filters.user_id" @change="fetchReports" class="input-field">
                        <option value="">Semua Pengguna</option>
                        <option v-for="user in userOptions" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                    <select v-if="auth.hasPermission('users.view-all') || auth.hasPermission('roles.view')" v-model="filters.role" @change="fetchReports" class="input-field">
                        <option value="">Semua Peranan</option>
                        <option v-for="role in filteredRoleOptions" :key="role.id" :value="role.name">{{ role.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <DataTable :columns="columns" :items="reports" :loading="loading">
                <template #cell-title="{ item }">
                    <router-link :to="{ name: 'reports.show', params: { id: item.id } }" class="font-medium text-primary-600 hover:text-primary-800">
                        {{ item.title }}
                    </router-link>
                </template>
                <template #cell-user="{ item }">
                    <span class="text-sm text-gray-700">{{ item.user?.name || 'N/A' }}</span>
                </template>
                <template #cell-ai_analysis="{ item }">
                    <Badge v-if="item.ai_analysis" :color="item.ai_analysis.risk_level?.color">{{ item.ai_analysis.risk_level?.label }}</Badge>
                    <span v-else class="text-xs text-gray-400">Menunggu</span>
                </template>
                <template #cell-incident_date="{ item }">
                    <span class="text-xs text-gray-500">{{ formatDate(item.incident_date) }}</span>
                </template>
                <template #actions="{ item }">
                    <div class="flex items-center gap-2 justify-end">
                        <router-link :to="{ name: 'reports.show', params: { id: item.id } }" class="text-gray-400 hover:text-primary-600" title="Lihat">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </router-link>
                        <router-link v-if="canEdit(item)" :to="{ name: 'reports.edit', params: { id: item.id } }" class="text-gray-400 hover:text-primary-600" title="Edit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                        </router-link>
                        <button v-if="canDelete(item)" @click="confirmDelete(item)" class="text-gray-400 hover:text-red-600" title="Padam">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        </button>
                    </div>
                </template>
            </DataTable>
            <div class="px-4 border-t border-gray-100">
                <Pagination :current-page="pagination.currentPage" :last-page="pagination.lastPage" :total="pagination.total" @page-change="goToPage" />
            </div>
        </div>

        <ConfirmDialog
            v-model="showDeleteDialog"
            title="Padam Laporan"
            :message="`Adakah anda pasti mahu memadam laporan ini? Tindakan ini tidak boleh dibatalkan.`"
            confirm-text="Padam"
            :danger="true"
            @confirm="handleDelete"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuth } from '../../composables/useAuth';
import reportsApi from '../../api/reports';
import categoriesApi from '../../api/categories';
import usersApi from '../../api/users';
import rolesApi from '../../api/roles';
import { RISK_LEVELS } from '../../utils/constants';
import { formatDate } from '../../utils/formatters';
import DataTable from '../../components/common/DataTable.vue';
import Badge from '../../components/common/Badge.vue';
import Pagination from '../../components/common/Pagination.vue';
import ConfirmDialog from '../../components/common/ConfirmDialog.vue';
import { useNotification } from '../../composables/useNotification';

const auth = useAuth();
const notify = useNotification();
const categoryOptions = ref([]);
const userOptions = ref([]);
const roleOptions = ref([]);
const reports = ref([]);
const loading = ref(true);
const pagination = reactive({ currentPage: 1, lastPage: 1, total: 0 });
const filters = reactive({ search: '', category: '', risk_level: '', user_id: '', role: '' });
const pendingAnalysisCount = ref(0);
const analyzingPending = ref(false);

// Filter out super-admin and admin roles for the dropdown
const filteredRoleOptions = computed(() => {
    return roleOptions.value.filter(role =>
        !['super-admin', 'admin'].includes(role.slug)
    );
});

let debounceTimer;
const debouncedFetch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchReports, 400);
};

const columns = [
    { key: 'title', label: 'Tajuk' },
    { key: 'category', label: 'Jenis Aktiviti' },
    { key: 'user', label: 'Pengguna' },
    { key: 'ai_analysis', label: 'Risiko' },
    { key: 'incident_date', label: 'Tarikh' },
    { key: 'incident_time', label: 'Masa' },
];

async function fetchReports(page = 1) {
    loading.value = true;
    try {
        const params = { ...filters, page, per_page: 15 };
        Object.keys(params).forEach(k => { if (!params[k]) delete params[k]; });
        const { data } = await reportsApi.list(params);
        reports.value = data.data;
        pagination.currentPage = data.meta?.current_page || 1;
        pagination.lastPage = data.meta?.last_page || 1;
        pagination.total = data.meta?.total || 0;
    } catch {}
    loading.value = false;
}

function goToPage(page) {
    fetchReports(page);
}

function canEdit(item) {
    if (auth.hasPermission('reports.edit-any')) return true;
    return auth.hasPermission('reports.edit-own') && item.user?.id === auth.user.value?.id;
}

function canDelete(item) {
    if (auth.hasPermission('reports.delete-any')) return true;
    return auth.hasPermission('reports.delete-own') && item.user?.id === auth.user.value?.id;
}

const showDeleteDialog = ref(false);
const deleteTarget = ref(null);

function confirmDelete(item) {
    deleteTarget.value = item;
    showDeleteDialog.value = true;
}

async function handleDelete() {
    showDeleteDialog.value = false;
    try {
        await reportsApi.delete(deleteTarget.value.id);
        notify.success('Laporan berjaya dipadam.');
        fetchReports(pagination.currentPage);
    } catch {
        notify.error('Gagal memadam laporan.');
    }
    deleteTarget.value = null;
}

async function fetchPendingAnalysisCount() {
    if (!auth.isSuperAdmin.value) return;
    try {
        const { data } = await reportsApi.pendingAnalysisCount();
        pendingAnalysisCount.value = data.data?.count || 0;
    } catch {}
}

function confirmAnalyzePending() {
    if (!confirm(`Mulakan analisis AI untuk ${pendingAnalysisCount.value} laporan tertunggak? Setiap kali butang ini diklik akan memproses sehingga 25 laporan.`)) {
        return;
    }
    handleAnalyzePending();
}

async function handleAnalyzePending() {
    analyzingPending.value = true;
    try {
        const { data } = await reportsApi.analyzePending(25);
        notify.success(data.message);
        pendingAnalysisCount.value = data.data?.remaining ?? 0;
        fetchReports(pagination.currentPage);
    } catch (e) {
        notify.error(e.response?.data?.message || 'Gagal mencetuskan analisis pukal.');
    }
    analyzingPending.value = false;
}

onMounted(() => {
    fetchReports();
    fetchPendingAnalysisCount();
    categoriesApi.active().then(({ data }) => { categoryOptions.value = data.data; }).catch(() => {});
    if (auth.hasPermission('users.view-all')) {
        usersApi.list({ per_page: 1000, exclude_admin_roles: true }).then(({ data }) => { userOptions.value = data.data; }).catch(() => {});
    }
    if (auth.hasPermission('users.view-all') || auth.hasPermission('roles.view')) {
        rolesApi.list({ per_page: 100 }).then(({ data }) => { roleOptions.value = data.data; }).catch(() => {});
    }
});
</script>
