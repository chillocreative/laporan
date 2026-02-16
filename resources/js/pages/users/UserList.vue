<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h1 class="page-title">Pengguna</h1>
                <p class="page-subtitle">Urus akaun pengguna dan peranan</p>
            </div>
            <router-link v-if="auth.hasPermission('users.create')" :to="{ name: 'users.create' }" class="btn-primary">
                + Pengguna Baru
            </router-link>
        </div>

        <!-- Alerts -->
        <Alert v-if="alertMsg" :type="alertType" class="mb-4">{{ alertMsg }}</Alert>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <input v-model="filters.search" @input="debouncedFetch" type="text" placeholder="Cari mengikut nama atau e-mel..." class="input-field" />
                    <select v-model="filters.role" @change="fetchUsers" class="input-field">
                        <option value="">Semua Peranan</option>
                        <option v-for="r in roles" :key="r.id" :value="r.slug">{{ r.name }}</option>
                    </select>
                    <select v-model="filters.is_active" @change="fetchUsers" class="input-field">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <DataTable :columns="columns" :items="users" :loading="loading">
                <template #cell-name="{ item }">
                    <span class="font-medium text-gray-900">{{ item.name }}</span>
                </template>
                <template #cell-roles="{ item }">
                    <div class="flex flex-wrap gap-1">
                        <Badge v-for="role in item.roles" :key="role.id" color="indigo">{{ role.name }}</Badge>
                    </div>
                </template>
                <template #cell-is_active="{ item }">
                    <Badge v-if="isPending(item)" color="yellow">Menunggu</Badge>
                    <Badge v-else :color="item.is_active ? 'green' : 'red'">{{ item.is_active ? 'Aktif' : 'Tidak Aktif' }}</Badge>
                </template>
                <template #cell-created_at="{ item }">
                    <span class="text-xs text-gray-500">{{ formatDate(item.created_at) }}</span>
                </template>
                <template #actions="{ item }">
                    <div class="flex items-center gap-2 justify-end">
                        <button v-if="isPending(item)" @click="openApprove(item)" class="text-xs bg-green-600 hover:bg-green-700 text-white px-2.5 py-1 rounded-md font-medium" title="Luluskan">
                            Luluskan
                        </button>
                        <router-link v-if="auth.hasPermission('users.edit')" :to="{ name: 'users.edit', params: { id: item.id } }" class="text-gray-400 hover:text-primary-600" title="Edit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                        </router-link>
                        <button v-if="auth.hasPermission('users.edit') && !isPending(item)" @click="handleToggleActive(item)" class="text-gray-400 hover:text-yellow-600" :title="item.is_active ? 'Deactivate' : 'Activate'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" /></svg>
                        </button>
                        <button v-if="auth.hasPermission('users.delete')" @click="confirmDelete(item)" class="text-gray-400 hover:text-red-600" title="Delete">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        </button>
                    </div>
                </template>
            </DataTable>
            <div class="px-4 border-t border-gray-100">
                <Pagination :current-page="pagination.currentPage" :last-page="pagination.lastPage" :total="pagination.total" @page-change="goToPage" />
            </div>
        </div>

        <!-- Approve Modal -->
        <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showApproveModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Luluskan Pengguna</h3>
                <p class="text-sm text-gray-600 mb-4">Luluskan <strong>{{ approveTarget?.name }}</strong> dan tetapkan peranan:</p>
                <select v-model="approveRoleId" class="input-field mb-4">
                    <option value="">Pilih peranan</option>
                    <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                </select>
                <div class="flex justify-end gap-3">
                    <button @click="showApproveModal = false" class="btn-secondary">Batal</button>
                    <button @click="handleApprove" :disabled="!approveRoleId || approving" class="btn-primary">
                        {{ approving ? 'Meluluskan...' : 'Luluskan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirm -->
        <ConfirmDialog
            v-model="showDeleteDialog"
            title="Padam Pengguna"
            :message="`Adakah anda pasti mahu memadam '${deleteTarget?.name}'? Tindakan ini tidak boleh dibatalkan.`"
            confirm-text="Padam"
            :danger="true"
            @confirm="handleDelete"
        />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuth } from '../../composables/useAuth';
import usersApi from '../../api/users';
import rolesApi from '../../api/roles';
import { formatDate } from '../../utils/formatters';
import DataTable from '../../components/common/DataTable.vue';
import Badge from '../../components/common/Badge.vue';
import Pagination from '../../components/common/Pagination.vue';
import ConfirmDialog from '../../components/common/ConfirmDialog.vue';
import Alert from '../../components/common/Alert.vue';

const auth = useAuth();
const users = ref([]);
const roles = ref([]);
const loading = ref(true);
const pagination = reactive({ currentPage: 1, lastPage: 1, total: 0 });
const filters = reactive({ search: '', role: '', is_active: '' });

const alertMsg = ref('');
const alertType = ref('success');
const showDeleteDialog = ref(false);
const deleteTarget = ref(null);
const showApproveModal = ref(false);
const approveTarget = ref(null);
const approveRoleId = ref('');
const approving = ref(false);

function isPending(user) {
    return !user.is_active && (!user.roles || user.roles.length === 0);
}

function openApprove(user) {
    approveTarget.value = user;
    approveRoleId.value = '';
    showApproveModal.value = true;
}

async function handleApprove() {
    approving.value = true;
    try {
        const { data } = await usersApi.approve(approveTarget.value.id, { role_id: approveRoleId.value });
        showAlert('success', data.message);
        showApproveModal.value = false;
        fetchUsers(pagination.currentPage);
    } catch (e) {
        showAlert('error', e.response?.data?.message || 'Gagal meluluskan pengguna.');
    }
    approving.value = false;
}

let debounceTimer;
const debouncedFetch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchUsers, 400);
};

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'email', label: 'E-mel' },
    { key: 'roles', label: 'Peranan' },
    { key: 'is_active', label: 'Status' },
    { key: 'created_at', label: 'Dicipta' },
];

async function fetchUsers(page = 1) {
    loading.value = true;
    try {
        const params = { ...filters, page, per_page: 15 };
        Object.keys(params).forEach(k => { if (!params[k] && params[k] !== 0) delete params[k]; });
        const { data } = await usersApi.list(params);
        users.value = data.data;
        pagination.currentPage = data.meta?.current_page || 1;
        pagination.lastPage = data.meta?.last_page || 1;
        pagination.total = data.meta?.total || 0;
    } catch {
        showAlert('error', 'Gagal memuatkan pengguna.');
    }
    loading.value = false;
}

async function fetchRoles() {
    try {
        const { data } = await rolesApi.assignable();
        roles.value = data.data;
    } catch {}
}

function goToPage(page) {
    fetchUsers(page);
}

async function handleToggleActive(user) {
    try {
        await usersApi.toggleActive(user.id);
        user.is_active = !user.is_active;
        showAlert('success', `Pengguna '${user.name}' telah ${user.is_active ? 'diaktifkan' : 'dinyahaktifkan'}.`);
    } catch {
        showAlert('error', 'Gagal mengemas kini status pengguna.');
    }
}

function confirmDelete(user) {
    deleteTarget.value = user;
    showDeleteDialog.value = true;
}

async function handleDelete() {
    showDeleteDialog.value = false;
    try {
        await usersApi.delete(deleteTarget.value.id);
        showAlert('success', `Pengguna '${deleteTarget.value.name}' telah dipadam.`);
        fetchUsers(pagination.currentPage);
    } catch {
        showAlert('error', 'Gagal memadam pengguna.');
    }
    deleteTarget.value = null;
}

function showAlert(type, msg) {
    alertType.value = type;
    alertMsg.value = msg;
    setTimeout(() => { alertMsg.value = ''; }, 4000);
}

onMounted(() => {
    fetchUsers();
    fetchRoles();
});
</script>
