<template>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="page-title">Create New User</h1>
            <p class="page-subtitle">Add a new user account</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                    <div>
                        <label class="label-text">Name *</label>
                        <input v-model="form.name" type="text" required class="input-field" placeholder="Full name" />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
                    </div>

                    <div>
                        <label class="label-text">Email *</label>
                        <input v-model="form.email" type="email" required class="input-field" placeholder="email@example.com" />
                        <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Password *</label>
                            <input v-model="form.password" type="password" required class="input-field" placeholder="Minimum 8 characters" />
                            <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
                        </div>
                        <div>
                            <label class="label-text">Confirm Password *</label>
                            <input v-model="form.password_confirmation" type="password" required class="input-field" placeholder="Re-enter password" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Role *</label>
                            <select v-model="form.role_id" required class="input-field">
                                <option value="">Select role</option>
                                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                            <p v-if="errors.role_ids" class="mt-1 text-xs text-red-600">{{ errors.role_ids }}</p>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                                <span class="text-sm text-gray-700">Active account</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <router-link :to="{ name: 'users.index' }" class="btn-secondary">Cancel</router-link>
                        <button type="submit" :disabled="submitting" class="btn-primary">
                            {{ submitting ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotification } from '../../composables/useNotification';
import usersApi from '../../api/users';
import rolesApi from '../../api/roles';

const router = useRouter();
const notify = useNotification();

const form = ref({ name: '', email: '', password: '', password_confirmation: '', role_id: '', is_active: true });
const roles = ref([]);
const submitting = ref(false);
const errorMsg = ref('');
const errors = reactive({});

onMounted(async () => {
    try {
        const { data } = await rolesApi.assignable();
        roles.value = data.data;
    } catch {}
});

async function handleSubmit() {
    submitting.value = true;
    errorMsg.value = '';
    Object.keys(errors).forEach(k => delete errors[k]);
    try {
        const payload = { ...form.value, role_ids: [form.value.role_id], is_active: form.value.is_active ? 1 : 0 };
        delete payload.role_id;
        await usersApi.create(payload);
        notify.success('User created successfully!');
        router.push({ name: 'users.index' });
    } catch (e) {
        const apiErrors = e.response?.data?.errors;
        if (apiErrors) {
            Object.entries(apiErrors).forEach(([key, msgs]) => { errors[key] = msgs[0]; });
            errorMsg.value = Object.values(apiErrors).flat()[0];
        } else {
            errorMsg.value = e.response?.data?.message || 'Failed to create user.';
        }
    } finally {
        submitting.value = false;
    }
}
</script>
