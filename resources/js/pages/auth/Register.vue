<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 px-4 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4">
                    <img src="/jata.png" alt="Logo" class="h-20 w-auto mx-auto drop-shadow-lg" />
                </div>
                <h1 class="text-2xl font-bold text-white">{{ settingsStore.systemName }}</h1>
                <p class="mt-1 text-primary-200 text-sm">Create your account</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Success: Pending Approval -->
                <div v-if="registered" class="text-center py-4">
                    <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Registration Successful</h2>
                    <p class="text-sm text-gray-600 mb-6">Your account is pending approval from an administrator. You will receive an email notification once your account has been approved.</p>
                    <router-link :to="{ name: 'login' }" class="btn-primary inline-block">Back to Login</router-link>
                </div>

                <!-- Registration Form -->
                <template v-else>
                    <form @submit.prevent="handleRegister" class="space-y-5">
                        <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                        <div>
                            <label class="label-text">Full Name</label>
                            <input v-model="form.name" type="text" required class="input-field" />
                        </div>

                        <div>
                            <label class="label-text">Email</label>
                            <input v-model="form.email" type="email" required class="input-field" />
                        </div>

                        <div>
                            <label class="label-text">Password</label>
                            <input v-model="form.password" type="password" required class="input-field" />
                        </div>

                        <div>
                            <label class="label-text">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password" required class="input-field" />
                        </div>

                        <div id="recaptcha-container"></div>

                        <button type="submit" :disabled="loading" class="btn-primary w-full">
                            {{ loading ? 'Creating account...' : 'Register' }}
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-gray-500">
                        Already have an account?
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

const form = ref({
    name: '', email: '', password: '', password_confirmation: '',
    recaptcha_token: 'placeholder',
});
const loading = ref(false);
const errorMsg = ref('');
const registered = ref(false);

onMounted(async () => {
    if (!settingsStore.loaded) settingsStore.fetchPublic();

    if (settingsStore.recaptchaSiteKey) {
        loadRecaptcha(settingsStore.recaptchaSiteKey);
    }
});

function loadRecaptcha(siteKey) {
    if (document.getElementById('recaptcha-script')) return;
    const script = document.createElement('script');
    script.id = 'recaptcha-script';
    script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
    script.async = true;
    script.defer = true;
    script.onload = () => {
        window.grecaptcha.ready(() => {
            window.grecaptcha.render('recaptcha-container', {
                sitekey: siteKey,
                callback: (token) => { form.value.recaptcha_token = token; },
            });
        });
    };
    document.head.appendChild(script);
}

async function handleRegister() {
    loading.value = true;
    errorMsg.value = '';
    try {
        await authApi.register(form.value);
        registered.value = true;
    } catch (e) {
        const errors = e.response?.data?.errors;
        errorMsg.value = errors ? Object.values(errors).flat()[0] : 'Registration failed.';
    } finally {
        loading.value = false;
    }
}
</script>
