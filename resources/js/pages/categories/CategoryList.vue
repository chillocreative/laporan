<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h1 class="page-title">Categories</h1>
                <p class="page-subtitle">Manage report categories</p>
            </div>
            <button @click="openCreate" class="btn-primary">+ New Category</button>
        </div>

        <Alert v-if="alertMsg" :type="alertType" class="mb-4">{{ alertMsg }}</Alert>

        <div class="card">
            <DataTable :columns="columns" :items="categories" :loading="loading">
                <template #cell-name="{ item }">
                    <span class="font-medium text-gray-900">{{ item.name }}</span>
                </template>
                <template #cell-is_active="{ item }">
                    <Badge :color="item.is_active ? 'green' : 'red'">{{ item.is_active ? 'Active' : 'Inactive' }}</Badge>
                </template>
                <template #cell-sort_order="{ item }">
                    <span class="text-sm text-gray-500">{{ item.sort_order }}</span>
                </template>
                <template #actions="{ item }">
                    <div class="flex items-center gap-2 justify-end">
                        <button @click="openEdit(item)" class="text-gray-400 hover:text-primary-600" title="Edit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                        </button>
                        <button @click="confirmDelete(item)" class="text-gray-400 hover:text-red-600" title="Delete">
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
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ editingCategory ? 'Edit Category' : 'New Category' }}</h3>
                <form @submit.prevent="handleSave" class="space-y-4">
                    <div>
                        <label class="label-text">Name *</label>
                        <input v-model="form.name" type="text" required class="input-field" placeholder="Category name" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Sort Order</label>
                            <input v-model.number="form.sort_order" type="number" min="0" class="input-field" />
                        </div>
                        <div v-if="editingCategory" class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>
                    <div v-if="formError" class="text-sm text-red-600">{{ formError }}</div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                        <button type="submit" :disabled="saving" class="btn-primary">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <ConfirmDialog
            v-model="showDeleteDialog"
            title="Delete Category"
            :message="`Are you sure you want to delete '${deleteTarget?.name}'?`"
            confirm-text="Delete"
            :danger="true"
            @confirm="handleDelete"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import categoriesApi from '../../api/categories';
import DataTable from '../../components/common/DataTable.vue';
import Badge from '../../components/common/Badge.vue';
import Alert from '../../components/common/Alert.vue';
import ConfirmDialog from '../../components/common/ConfirmDialog.vue';

const categories = ref([]);
const loading = ref(true);

const showModal = ref(false);
const editingCategory = ref(null);
const form = ref({ name: '', sort_order: 0, is_active: true });
const formError = ref('');
const saving = ref(false);

const showDeleteDialog = ref(false);
const deleteTarget = ref(null);
const alertMsg = ref('');
const alertType = ref('success');

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'is_active', label: 'Status' },
    { key: 'sort_order', label: 'Order' },
];

async function fetchCategories() {
    loading.value = true;
    try {
        const { data } = await categoriesApi.list();
        categories.value = data.data;
    } catch {
        showAlert('error', 'Failed to load categories.');
    }
    loading.value = false;
}

function openCreate() {
    editingCategory.value = null;
    form.value = { name: '', sort_order: categories.value.length + 1, is_active: true };
    formError.value = '';
    showModal.value = true;
}

function openEdit(cat) {
    editingCategory.value = cat;
    form.value = { name: cat.name, sort_order: cat.sort_order, is_active: cat.is_active };
    formError.value = '';
    showModal.value = true;
}

async function handleSave() {
    saving.value = true;
    formError.value = '';
    try {
        if (editingCategory.value) {
            await categoriesApi.update(editingCategory.value.id, form.value);
            showAlert('success', 'Category updated.');
        } else {
            await categoriesApi.store(form.value);
            showAlert('success', 'Category created.');
        }
        showModal.value = false;
        fetchCategories();
    } catch (e) {
        const errors = e.response?.data?.errors;
        formError.value = errors ? Object.values(errors).flat()[0] : 'Failed to save category.';
    }
    saving.value = false;
}

function confirmDelete(cat) {
    deleteTarget.value = cat;
    showDeleteDialog.value = true;
}

async function handleDelete() {
    showDeleteDialog.value = false;
    try {
        await categoriesApi.delete(deleteTarget.value.id);
        showAlert('success', `Category '${deleteTarget.value.name}' deleted.`);
        fetchCategories();
    } catch {
        showAlert('error', 'Failed to delete category.');
    }
    deleteTarget.value = null;
}

function showAlert(type, msg) {
    alertType.value = type;
    alertMsg.value = msg;
    setTimeout(() => { alertMsg.value = ''; }, 4000);
}

onMounted(fetchCategories);
</script>
