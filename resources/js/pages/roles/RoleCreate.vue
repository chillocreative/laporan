<template>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <router-link :to="{ name: 'roles.index' }" class="text-sm text-gray-500 hover:text-primary-600 mb-1 inline-block">&larr; Kembali ke Peranan</router-link>
            <h1 class="page-title">Cipta Peranan Baru</h1>
            <p class="page-subtitle">Tentukan peranan baru dengan kebenaran khusus</p>
        </div>

        <LoadingSpinner v-if="loadingPermissions" full-page />

        <template v-else>
            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text">Nama Peranan *</label>
                                <input v-model="form.name" @input="generateSlug" type="text" required class="input-field" placeholder="e.g. Content Manager" />
                            </div>
                            <div>
                                <label class="label-text">Slug *</label>
                                <input v-model="form.slug" type="text" required class="input-field" placeholder="e.g. content-manager" />
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div>
                            <label class="label-text mb-3">Kebenaran</label>
                            <div class="space-y-4">
                                <div v-for="(perms, groupName) in groupedPermissions" :key="groupName" class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <input
                                            type="checkbox"
                                            :id="`group-${groupName}`"
                                            :checked="isGroupFullySelected(groupName)"
                                            :indeterminate="isGroupPartiallySelected(groupName)"
                                            @change="toggleGroup(groupName, $event.target.checked)"
                                            class="h-4 w-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500"
                                        />
                                        <label :for="`group-${groupName}`" class="text-sm font-semibold text-gray-700 capitalize">
                                            {{ groupName }}
                                        </label>
                                        <span class="text-xs text-gray-400">
                                            ({{ selectedCountInGroup(groupName) }}/{{ perms.length }})
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 ml-7">
                                        <label v-for="perm in perms" :key="perm.id" class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :value="perm.id"
                                                v-model="form.permissions"
                                                class="h-4 w-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500"
                                            />
                                            {{ perm.name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!Object.keys(groupedPermissions).length" class="text-sm text-gray-400 mt-2">Tiada kebenaran tersedia.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <router-link :to="{ name: 'roles.index' }" class="btn-secondary">Batal</router-link>
                            <button type="submit" :disabled="submitting" class="btn-primary">
                                {{ submitting ? 'Mencipta...' : 'Cipta Peranan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotification } from '../../composables/useNotification';
import rolesApi from '../../api/roles';
import LoadingSpinner from '../../components/common/LoadingSpinner.vue';

const router = useRouter();
const notify = useNotification();

const form = ref({ name: '', slug: '', permissions: [] });
const groupedPermissions = ref({});
const loadingPermissions = ref(true);
const submitting = ref(false);
const errorMsg = ref('');

function generateSlug() {
    form.value.slug = form.value.name
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s]+/g, '-')
        .replace(/-+/g, '-');
}

function isGroupFullySelected(groupName) {
    const perms = groupedPermissions.value[groupName] || [];
    return perms.length > 0 && perms.every(p => form.value.permissions.includes(p.id));
}

function isGroupPartiallySelected(groupName) {
    const perms = groupedPermissions.value[groupName] || [];
    const count = perms.filter(p => form.value.permissions.includes(p.id)).length;
    return count > 0 && count < perms.length;
}

function selectedCountInGroup(groupName) {
    const perms = groupedPermissions.value[groupName] || [];
    return perms.filter(p => form.value.permissions.includes(p.id)).length;
}

function toggleGroup(groupName, checked) {
    const perms = groupedPermissions.value[groupName] || [];
    const ids = perms.map(p => p.id);
    if (checked) {
        const current = new Set(form.value.permissions);
        ids.forEach(id => current.add(id));
        form.value.permissions = [...current];
    } else {
        form.value.permissions = form.value.permissions.filter(id => !ids.includes(id));
    }
}

async function fetchPermissions() {
    loadingPermissions.value = true;
    try {
        const { data } = await rolesApi.permissions();
        groupedPermissions.value = data.data;
    } catch {
        errorMsg.value = 'Gagal memuatkan kebenaran.';
    }
    loadingPermissions.value = false;
}

async function handleSubmit() {
    submitting.value = true;
    errorMsg.value = '';
    try {
        await rolesApi.create({
            name: form.value.name,
            slug: form.value.slug,
            permission_ids: form.value.permissions,
        });
        notify.success('Peranan berjaya dicipta!');
        router.push({ name: 'roles.index' });
    } catch (e) {
        const errors = e.response?.data?.errors;
        errorMsg.value = errors ? Object.values(errors).flat()[0] : 'Gagal mencipta peranan.';
    } finally {
        submitting.value = false;
    }
}

onMounted(() => fetchPermissions());
</script>
