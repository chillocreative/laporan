<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h1 class="page-title">Reports</h1>
                <p class="page-subtitle">Manage and track all reports</p>
            </div>
            <router-link v-if="auth.hasPermission('reports.create')" :to="{ name: 'reports.create' }" class="btn-primary">
                + New Report
            </router-link>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <input v-model="filters.search" @input="debouncedFetch" type="text" placeholder="Search reports..." class="input-field" />
                    <select v-model="filters.category" @change="fetchReports" class="input-field">
                        <option value="">All Categories</option>
                        <option v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <select v-model="filters.risk_level" @change="fetchReports" class="input-field">
                        <option value="">All Risk Levels</option>
                        <option v-for="r in RISK_LEVELS" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                    <select v-if="auth.hasPermission('users.view-all')" v-model="filters.user_id" @change="fetchReports" class="input-field">
                        <option value="">All Users</option>
                        <option v-for="user in userOptions" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                    <select v-if="auth.hasPermission('users.view-all') || auth.hasPermission('roles.view')" v-model="filters.role" @change="fetchReports" class="input-field">
                        <option value="">All Roles</option>
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
                    <span v-else class="text-xs text-gray-400">Pending</span>
                </template>
                <template #cell-incident_date="{ item }">
                    <span class="text-xs text-gray-500">{{ formatDate(item.incident_date) }}</span>
                </template>
                <template #actions="{ item }">
                    <div class="flex items-center gap-2 justify-end">
                        <router-link :to="{ name: 'reports.show', params: { id: item.id } }" class="text-gray-400 hover:text-primary-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </router-link>
                    </div>
                </template>
            </DataTable>
            <div class="px-4 border-t border-gray-100">
                <Pagination :current-page="pagination.currentPage" :last-page="pagination.lastPage" :total="pagination.total" @page-change="goToPage" />
            </div>
        </div>
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

const auth = useAuth();
const categoryOptions = ref([]);
const userOptions = ref([]);
const roleOptions = ref([]);
const reports = ref([]);
const loading = ref(true);
const pagination = reactive({ currentPage: 1, lastPage: 1, total: 0 });
const filters = reactive({ search: '', category: '', risk_level: '', user_id: '', role: '' });

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
    { key: 'title', label: 'Title' },
    { key: 'category', label: 'Category' },
    { key: 'user', label: 'User' },
    { key: 'ai_analysis', label: 'Risk' },
    { key: 'incident_date', label: 'Date' },
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

onMounted(() => {
    fetchReports();
    categoriesApi.active().then(({ data }) => { categoryOptions.value = data.data; }).catch(() => {});
    if (auth.hasPermission('users.view-all')) {
        usersApi.list({ per_page: 1000, exclude_admin_roles: true }).then(({ data }) => { userOptions.value = data.data; }).catch(() => {});
    }
    if (auth.hasPermission('users.view-all') || auth.hasPermission('roles.view')) {
        rolesApi.list({ per_page: 100 }).then(({ data }) => { roleOptions.value = data.data; }).catch(() => {});
    }
});
</script>
