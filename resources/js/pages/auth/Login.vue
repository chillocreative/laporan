<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 px-4 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4">
                    <img src="/jata.png" alt="Logo" class="h-20 w-auto mx-auto drop-shadow-lg" />
                </div>
                <h1 class="text-2xl font-bold text-white">{{ settingsStore.systemName }}</h1>
                <p class="mt-1 text-primary-200 text-sm">Log masuk ke akaun anda</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <form @submit.prevent="handleLogin" class="space-y-5">
                    <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                    <div>
                        <label class="label-text">E-mel</label>
                        <input v-model="form.email" type="email" required class="input-field" placeholder="anda@emel.com" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="label-text">Kata Laluan</label>
                            <router-link :to="{ name: 'forgot-password' }" class="text-xs text-primary-600 hover:text-primary-500">Lupa kata laluan?</router-link>
                        </div>
                        <input v-model="form.password" type="password" required class="input-field" placeholder="Minimum 8 aksara" />
                    </div>

                    <button type="submit" :disabled="loading" class="btn-primary w-full">
                        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ loading ? 'Sedang log masuk...' : 'Log Masuk' }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Belum mempunyai akaun?
                    <router-link :to="{ name: 'register' }" class="font-medium text-primary-600 hover:text-primary-500">Daftar</router-link>
                </p>
            </div>

            <div class="mt-6 text-center">
                <p class="text-[11px] text-primary-200/60">&copy; {{ settingsStore.systemName }} {{ new Date().getFullYear() }} &middot; Build &amp; Design by: <a href="https://www.chillocreative.com" target="_blank" class="text-primary-200/80 hover:text-white transition-colors">Chillo Creative</a></p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useSettingsStore } from '../../stores/settings';

const router = useRouter();
const authStore = useAuthStore();
const settingsStore = useSettingsStore();

const form = ref({ email: '', password: '' });
const loading = ref(false);
const errorMsg = ref('');

onMounted(() => {
    if (!settingsStore.loaded) settingsStore.fetchPublic();
});

async function handleLogin() {
    loading.value = true;
    errorMsg.value = '';
    try {
        await authStore.login(form.value);
        router.push({ name: 'dashboard' });
    } catch (e) {
        errorMsg.value = e.response?.data?.message || e.response?.data?.errors?.email?.[0] || 'Log masuk gagal.';
    } finally {
        loading.value = false;
    }
}
</script>
