<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h1 class="page-title">Minit Mesyuarat</h1>
                <p class="page-subtitle">Urus minit mesyuarat bulanan MPKK</p>
            </div>
            <button @click="openCreate" class="btn-primary">+ Minit Mesyuarat Baru</button>
        </div>

        <Alert v-if="alertMsg" :type="alertType" class="mb-4">{{ alertMsg }}</Alert>

        <div class="card">
            <DataTable :columns="columns" :items="records" :loading="loading">
                <template #cell-bil="{ item }">
                    <span class="text-sm text-gray-700">{{ records.indexOf(item) + 1 }}</span>
                </template>
                <template #cell-user="{ item }">
                    <span class="text-sm text-gray-700">{{ item.user?.name || '-' }}</span>
                </template>
                <template #cell-bulan="{ item }">
                    <span class="text-sm text-gray-700">{{ formatBulan(item.bulan) }}</span>
                </template>
                <template #actions="{ item }">
                    <div class="flex items-center gap-2 justify-end">
                        <a :href="item.view_url" target="_blank" class="text-gray-400 hover:text-primary-600" title="Lihat">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </a>
                        <a :href="item.download_url" class="text-gray-400 hover:text-primary-600" title="Muat Turun">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                        </a>
                        <button @click="openEdit(item)" class="text-gray-400 hover:text-primary-600" title="Edit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                        </button>
                        <button @click="confirmDelete(item)" class="text-gray-400 hover:text-red-600" title="Padam">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ editingRecord ? 'Edit Minit Mesyuarat' : 'Minit Mesyuarat Baru' }}</h3>
                <form @submit.prevent="handleSave" class="space-y-4">
                    <div v-if="isAdmin && !editingRecord">
                        <label class="label-text">Pengguna MPKK *</label>
                        <select v-model="form.user_id" required class="input-field">
                            <option value="">Pilih pengguna MPKK</option>
                            <option v-for="u in mpkkUserOptions" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-text">Bulan *</label>
                        <select v-model="form.month" required class="input-field">
                            <option value="">Pilih bulan</option>
                            <option v-for="(name, idx) in MALAY_MONTHS" :key="idx" :value="idx + 1">{{ name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-text">Fail Minit Mesyuarat (PDF/DOC/DOCX)</label>
                        <p v-if="editingRecord && !files.length" class="mt-1 text-xs text-gray-500">Fail sedia ada: {{ editingRecord.original_name }}</p>
                        <FileUpload v-model="files" :multiple="false" :max-files="1" accept=".pdf,.doc,.docx" label="" />
                    </div>
                    <div v-if="formError" class="text-sm text-red-600">{{ formError }}</div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" :disabled="saving" class="btn-primary">
                            {{ saving ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <ConfirmDialog
            v-model="showDeleteDialog"
            title="Padam Minit Mesyuarat"
            :message="`Adakah anda pasti mahu memadam minit mesyuarat untuk bulan ${deleteTarget ? formatBulan(deleteTarget.bulan) : ''}?`"
            confirm-text="Padam"
            :danger="true"
            @confirm="handleDelete"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '../../composables/useAuth';
import minitMesyuaratApi from '../../api/minitMesyuarat';
import usersApi from '../../api/users';
import DataTable from '../../components/common/DataTable.vue';
import Alert from '../../components/common/Alert.vue';
import ConfirmDialog from '../../components/common/ConfirmDialog.vue';
import FileUpload from '../../components/common/FileUpload.vue';

const auth = useAuth();
const isAdmin = computed(() => auth.hasAnyRole(['super-admin', 'admin']));

const records = ref([]);
const loading = ref(true);
const mpkkUserOptions = ref([]);

const showModal = ref(false);
const editingRecord = ref(null);
// `year` is tracked internally (not shown in the UI) so editing a record keeps
// its original year; new records use the current year.
const form = ref({ user_id: '', month: '', year: new Date().getFullYear() });
const formError = ref('');
const saving = ref(false);

const showDeleteDialog = ref(false);
const deleteTarget = ref(null);
const alertMsg = ref('');
const alertType = ref('success');

const files = ref([]);

const MALAY_MONTHS = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];

function formatBulan(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return `${MALAY_MONTHS[d.getMonth()]} ${d.getFullYear()}`;
}

const columns = computed(() => {
    const cols = [{ key: 'bil', label: 'Bil.' }];
    if (isAdmin.value) cols.push({ key: 'user', label: 'Pengguna' });
    cols.push({ key: 'bulan', label: 'Minit Mesyuarat Bulan' });
    return cols;
});

async function fetchRecords() {
    loading.value = true;
    try {
        const { data } = await minitMesyuaratApi.list();
        records.value = data.data;
    } catch {
        showAlert('error', 'Gagal memuatkan senarai minit mesyuarat.');
    }
    loading.value = false;
}

async function fetchMpkkUsers() {
    if (!isAdmin.value) return;
    try {
        const { data } = await usersApi.list({ role: 'mpkk', per_page: 1000 });
        mpkkUserOptions.value = data.data;
    } catch {}
}

function openCreate() {
    editingRecord.value = null;
    form.value = { user_id: '', month: '', year: new Date().getFullYear() };
    files.value = [];
    formError.value = '';
    showModal.value = true;
}

function openEdit(rec) {
    editingRecord.value = rec;
    const [year, month] = rec.bulan.split('-');
    form.value = { user_id: rec.user_id, month: Number(month), year: Number(year) };
    files.value = [];
    formError.value = '';
    showModal.value = true;
}

async function handleSave() {
    saving.value = true;
    formError.value = '';

    const formData = new FormData();
    formData.append('bulan', `${form.value.year}-${String(form.value.month).padStart(2, '0')}-01`);
    if (files.value[0]) {
        formData.append('file', files.value[0]);
    }

    try {
        if (editingRecord.value) {
            await minitMesyuaratApi.update(editingRecord.value.id, formData);
            showAlert('success', 'Minit mesyuarat dikemas kini.');
        } else {
            if (isAdmin.value) {
                formData.append('user_id', form.value.user_id);
            }
            await minitMesyuaratApi.create(formData);
            showAlert('success', 'Minit mesyuarat dicipta.');
        }
        showModal.value = false;
        fetchRecords();
    } catch (e) {
        const errors = e.response?.data?.errors;
        formError.value = errors ? Object.values(errors).flat()[0] : 'Gagal menyimpan minit mesyuarat.';
    }
    saving.value = false;
}

function confirmDelete(rec) {
    deleteTarget.value = rec;
    showDeleteDialog.value = true;
}

async function handleDelete() {
    showDeleteDialog.value = false;
    try {
        await minitMesyuaratApi.delete(deleteTarget.value.id);
        showAlert('success', `Minit mesyuarat untuk ${formatBulan(deleteTarget.value.bulan)} telah dipadam.`);
        fetchRecords();
    } catch {
        showAlert('error', 'Gagal memadam rekod.');
    }
    deleteTarget.value = null;
}

function showAlert(type, msg) {
    alertType.value = type;
    alertMsg.value = msg;
    setTimeout(() => { alertMsg.value = ''; }, 4000);
}

onMounted(() => {
    fetchRecords();
    fetchMpkkUsers();
});
</script>
