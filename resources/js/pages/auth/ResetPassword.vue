<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 px-4 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4">
                    <img src="/jata.png" alt="Logo" class="h-20 w-auto mx-auto drop-shadow-lg" />
                </div>
                <h1 class="text-2xl font-bold text-white">{{ settingsStore.systemName }}</h1>
                <p class="mt-1 text-primary-200 text-sm">Set your new password</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div v-if="success" class="text-center py-4">
                    <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Password Reset</h2>
                    <p class="text-sm text-gray-600 mb-6">Your password has been reset successfully. You can now log in with your new password.</p>
                    <router-link :to="{ name: 'login' }" class="btn-primary inline-block">Back to Login</router-link>
                </div>

                <template v-else>
                    <form @submit.prevent="handleSubmit" class="space-y-5">
                        <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                        <div>
                            <label class="label-text">Email</label>
                            <input v-model="form.email" type="email" required class="input-field" />
                        </div>

                        <div>
                            <label class="label-text">New Password</label>
                            <input v-model="form.password" type="password" required class="input-field" placeholder="Minimum 8 characters" />
                        </div>

                        <div>
                            <label class="label-text">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password" required class="input-field" />
                        </div>

                        <button type="submit" :disabled="loading" class="btn-primary w-full">
                            {{ loading ? 'Resetting...' : 'Reset Password' }}
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useSettingsStore } from '../../stores/settings';
import authApi from '../../api/auth';

const route = useRoute();
const settingsStore = useSettingsStore();

const form = ref({
    email: '',
    password: '',
    password_confirmation: '',
    token: '',
});
const loading = ref(false);
const errorMsg = ref('');
const success = ref(false);

onMounted(() => {
    if (!settingsStore.loaded) settingsStore.fetchPublic();
    form.value.token = route.query.token || '';
    form.value.email = route.query.email || '';
});

async function handleSubmit() {
    loading.value = true;
    errorMsg.value = '';
    try {
        await authApi.resetPassword(form.value);
        success.value = true;
    } catch (e) {
        errorMsg.value = e.response?.data?.errors?.email?.[0] || e.response?.data?.message || 'Failed to reset password.';
    } finally {
        loading.value = false;
    }
}
</script>
