<template>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <router-link :to="{ name: 'users.index' }" class="text-sm text-gray-500 hover:text-primary-600 mb-1 inline-block">&larr; Back to Users</router-link>
            <h1 class="page-title">Edit User</h1>
        </div>

        <LoadingSpinner v-if="loading" full-page />

        <div v-else class="card">
            <div class="card-body">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                    <div>
                        <label class="label-text">Name *</label>
                        <input v-model="form.name" type="text" required class="input-field" />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
                    </div>

                    <div>
                        <label class="label-text">Email *</label>
                        <input v-model="form.email" type="email" required class="input-field" />
                        <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">New Password</label>
                            <input v-model="form.password" type="password" class="input-field" placeholder="Leave blank to keep current" />
                            <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
                        </div>
                        <div>
                            <label class="label-text">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password" class="input-field" placeholder="Leave blank to keep current" />
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
                            {{ submitting ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useNotification } from '../../composables/useNotification';
import usersApi from '../../api/users';
import rolesApi from '../../api/roles';
import LoadingSpinner from '../../components/common/LoadingSpinner.vue';

const props = defineProps({ id: [String, Number] });
const route = useRoute();
const router = useRouter();
const notify = useNotification();

const userId = computed(() => props.id || route.params.id);
const form = ref({ name: '', email: '', password: '', password_confirmation: '', role_id: '', is_active: true });
const roles = ref([]);
const loading = ref(true);
const submitting = ref(false);
const errorMsg = ref('');
const errors = reactive({});

onMounted(async () => {
    try {
        const [userRes, rolesRes] = await Promise.all([
            usersApi.get(userId.value),
            rolesApi.assignable(),
        ]);
        const u = userRes.data.data;
        form.value = {
            name: u.name,
            email: u.email,
            password: '',
            password_confirmation: '',
            role_id: u.roles?.[0]?.id || '',
            is_active: !!u.is_active,
        };
        roles.value = rolesRes.data.data;
    } catch {
        router.push({ name: 'users.index' });
    }
    loading.value = false;
});

async function handleSubmit() {
    submitting.value = true;
    errorMsg.value = '';
    Object.keys(errors).forEach(k => delete errors[k]);
    try {
        const payload = {
            name: form.value.name,
            email: form.value.email,
            role_ids: [form.value.role_id],
            is_active: form.value.is_active ? 1 : 0,
        };
        if (form.value.password) {
            payload.password = form.value.password;
            payload.password_confirmation = form.value.password_confirmation;
        }
        await usersApi.update(userId.value, payload);
        notify.success('User updated successfully!');
        router.push({ name: 'users.index' });
    } catch (e) {
        const apiErrors = e.response?.data?.errors;
        if (apiErrors) {
            Object.entries(apiErrors).forEach(([key, msgs]) => { errors[key] = msgs[0]; });
            errorMsg.value = Object.values(apiErrors).flat()[0];
        } else {
            errorMsg.value = e.response?.data?.message || 'Failed to update user.';
        }
    } finally {
        submitting.value = false;
    }
}
</script>
