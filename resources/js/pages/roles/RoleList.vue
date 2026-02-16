<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h1 class="page-title">Peranan</h1>
                <p class="page-subtitle">Urus peranan dan kebenaran pengguna</p>
            </div>
            <router-link :to="{ name: 'roles.create' }" class="btn-primary">
                + Peranan Baru
            </router-link>
        </div>

        <Alert v-if="errorMsg" type="error" class="mb-6">{{ errorMsg }}</Alert>

        <div class="card">
            <DataTable :columns="columns" :items="roles" :loading="loading">
                <template #cell-is_system="{ item }">
                    <Badge v-if="item.is_system" color="indigo">Sistem</Badge>
                    <span v-else class="text-xs text-gray-400">-</span>
                </template>
                <template #cell-permissions_count="{ item }">
                    <span class="text-sm text-gray-700">{{ item.permissions_count ?? 0 }}</span>
                </template>
                <template #cell-users_count="{ item }">
                    <span class="text-sm text-gray-700">{{ item.users_count ?? 0 }}</span>
                </template>
                <template #actions="{ item }">
                    <div class="flex items-center gap-2 justify-end">
                        <router-link
                            :to="{ name: 'roles.edit', params: { id: item.id } }"
                            class="text-gray-400 hover:text-primary-600"
                            :class="{ 'opacity-40 pointer-events-none': item.is_system }"
                            :tabindex="item.is_system ? -1 : 0"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </router-link>
                        <button
                            @click="confirmDelete(item)"
                            class="text-gray-400 hover:text-red-600"
                            :class="{ 'opacity-40 pointer-events-none': item.is_system }"
                            :disabled="item.is_system"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <ConfirmDialog
            v-model="showDeleteDialog"
            title="Padam Peranan"
            :message="`Adakah anda pasti mahu memadam peranan '${roleToDelete?.name}'? Tindakan ini tidak boleh dibatalkan.`"
            confirm-text="Padam"
            :danger="true"
            @confirm="handleDelete"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNotification } from '../../composables/useNotification';
import rolesApi from '../../api/roles';
import DataTable from '../../components/common/DataTable.vue';
import Badge from '../../components/common/Badge.vue';
import Alert from '../../components/common/Alert.vue';
import ConfirmDialog from '../../components/common/ConfirmDialog.vue';

const notify = useNotification();
const roles = ref([]);
const loading = ref(true);
const errorMsg = ref('');
const showDeleteDialog = ref(false);
const roleToDelete = ref(null);

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'slug', label: 'Slug' },
    { key: 'is_system', label: 'Jenis' },
    { key: 'permissions_count', label: 'Kebenaran' },
    { key: 'users_count', label: 'Pengguna' },
];

async function fetchRoles() {
    loading.value = true;
    errorMsg.value = '';
    try {
        const { data } = await rolesApi.list();
        roles.value = data.data;
    } catch {
        errorMsg.value = 'Gagal memuatkan peranan.';
    }
    loading.value = false;
}

function confirmDelete(role) {
    if (role.is_system) return;
    roleToDelete.value = role;
    showDeleteDialog.value = true;
}

async function handleDelete() {
    if (!roleToDelete.value) return;
    try {
        await rolesApi.delete(roleToDelete.value.id);
        notify.success('Peranan berjaya dipadam.');
        showDeleteDialog.value = false;
        roleToDelete.value = null;
        await fetchRoles();
    } catch (e) {
        const msg = e.response?.data?.message || 'Gagal memadam peranan.';
        notify.error(msg);
        showDeleteDialog.value = false;
    }
}

onMounted(() => fetchRoles());
</script>
