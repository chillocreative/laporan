<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 px-4 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4">
                    <img src="/jata.png" alt="Logo" class="h-20 w-auto mx-auto drop-shadow-lg" />
                </div>
                <h1 class="text-2xl font-bold text-white">{{ settingsStore.systemName }}</h1>
                <p class="mt-1 text-primary-200 text-sm">Reset your password</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div v-if="sent" class="text-center py-4">
                    <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Check Your Email</h2>
                    <p class="text-sm text-gray-600 mb-6">We've sent a password reset link to your email address. Please check your inbox.</p>
                    <router-link :to="{ name: 'login' }" class="btn-primary inline-block">Back to Login</router-link>
                </div>

                <template v-else>
                    <p class="text-sm text-gray-600 mb-5">Enter your email address and we'll send you a link to reset your password.</p>

                    <form @submit.prevent="handleSubmit" class="space-y-5">
                        <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                        <div>
                            <label class="label-text">Email</label>
                            <input v-model="form.email" type="email" required class="input-field" placeholder="your@email.com" />
                        </div>

                        <button type="submit" :disabled="loading" class="btn-primary w-full">
                            {{ loading ? 'Sending...' : 'Send Reset Link' }}
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-gray-500">
                        Remember your password?
                        <router-link :to="{ name: 'login' }" class="font-medium text-primary-600 hover:text-primary-500">Sign in</router-link>
                    </p>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useSettingsStore } from '../../stores/settings';
import authApi from '../../api/auth';

const settingsStore = useSettingsStore();
const form = ref({ email: '' });
const loading = ref(false);
const errorMsg = ref('');
const sent = ref(false);

onMounted(() => {
    if (!settingsStore.loaded) settingsStore.fetchPublic();
});

async function handleSubmit() {
    loading.value = true;
    errorMsg.value = '';
    try {
        await authApi.forgotPassword(form.value);
        sent.value = true;
    } catch (e) {
        errorMsg.value = e.response?.data?.errors?.email?.[0] || e.response?.data?.message || 'Failed to send reset link.';
    } finally {
        loading.value = false;
    }
}
</script>
