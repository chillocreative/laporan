<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 px-4 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4">
                    <img src="/jata.png" alt="Logo" class="h-20 w-auto mx-auto drop-shadow-lg" />
                </div>
                <h1 class="text-2xl font-bold text-white">{{ settingsStore.systemName }}</h1>
                <p class="mt-1 text-primary-200 text-sm">Tukar kata laluan anda</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <h4 class="text-sm font-semibold text-amber-800 mb-1">Kata Laluan Sementara Dikesan</h4>
                    <p class="text-xs text-amber-700">Anda sedang menggunakan kata laluan sementara. Sila tetapkan kata laluan baru untuk meneruskan.</p>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                    <div>
                        <label class="label-text">Kata Laluan Semasa (Sementara)</label>
                        <input v-model="form.current_password" type="password" required class="input-field" placeholder="Masukkan kata laluan sementara" />
                    </div>

                    <div>
                        <label class="label-text">Kata Laluan Baru</label>
                        <input v-model="form.password" type="password" required class="input-field" placeholder="Minimum 8 aksara" />
                    </div>

                    <div>
                        <label class="label-text">Sahkan Kata Laluan Baru</label>
                        <input v-model="form.password_confirmation" type="password" required class="input-field" placeholder="Masukkan semula kata laluan baru" />
                    </div>

                    <button type="submit" :disabled="loading" class="btn-primary w-full">
                        {{ loading ? 'Mengemas kini...' : 'Tukar Kata Laluan' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useSettingsStore } from '../../stores/settings';
import api from '../../api/axios';

const router = useRouter();
const authStore = useAuthStore();
const settingsStore = useSettingsStore();

const form = ref({ current_password: '', password: '', password_confirmation: '' });
const loading = ref(false);
const errorMsg = ref('');

onMounted(() => {
    if (!settingsStore.loaded) settingsStore.fetchPublic();
});

async function handleSubmit() {
    loading.value = true;
    errorMsg.value = '';
    try {
        await api.put('/profile/password', form.value);
        await authStore.fetchUser();
        router.push({ name: 'dashboard' });
    } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
            errorMsg.value = Object.values(errors).flat()[0];
        } else {
            errorMsg.value = e.response?.data?.message || 'Gagal mengemas kini kata laluan.';
        }
    } finally {
        loading.value = false;
    }
}
</script>
