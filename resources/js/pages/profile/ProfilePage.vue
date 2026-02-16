<template>
    <div class="max-w-3xl mx-auto">
        <h1 class="page-title mb-6">Profile</h1>

        <!-- Profile Info -->
        <div class="card mb-6">
            <div class="card-body">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Personal Information</h2>
                <form @submit.prevent="handleUpdateProfile" class="space-y-5">
                    <div v-if="profileError" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ profileError }}</div>

                    <div>
                        <label class="label-text">Name *</label>
                        <input v-model="profileForm.name" type="text" required class="input-field" />
                        <p v-if="profileErrors.name" class="mt-1 text-xs text-red-600">{{ profileErrors.name }}</p>
                    </div>

                    <div>
                        <label class="label-text">Email *</label>
                        <input v-model="profileForm.email" type="email" required class="input-field" />
                        <p v-if="profileErrors.email" class="mt-1 text-xs text-red-600">{{ profileErrors.email }}</p>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="submit" :disabled="savingProfile" class="btn-primary">
                            {{ savingProfile ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-body">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Change Password</h2>
                <form @submit.prevent="handleUpdatePassword" class="space-y-5">
                    <div v-if="passwordError" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ passwordError }}</div>
                    <div v-if="passwordSuccess" class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ passwordSuccess }}</div>

                    <div>
                        <label class="label-text">Current Password *</label>
                        <input v-model="passwordForm.current_password" type="password" required class="input-field" />
                        <p v-if="passwordErrors.current_password" class="mt-1 text-xs text-red-600">{{ passwordErrors.current_password }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">New Password *</label>
                            <input v-model="passwordForm.password" type="password" required class="input-field" />
                            <p v-if="passwordErrors.password" class="mt-1 text-xs text-red-600">{{ passwordErrors.password }}</p>
                        </div>
                        <div>
                            <label class="label-text">Confirm New Password *</label>
                            <input v-model="passwordForm.password_confirmation" type="password" required class="input-field" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="submit" :disabled="savingPassword" class="btn-primary">
                            {{ savingPassword ? 'Updating...' : 'Update Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuth } from '../../composables/useAuth';
import { useAuthStore } from '../../stores/auth';
import { useNotification } from '../../composables/useNotification';
import api from '../../api/axios';

const auth = useAuth();
const authStore = useAuthStore();
const notify = useNotification();

const profileForm = ref({ name: '', email: '' });
const profileError = ref('');
const profileErrors = reactive({});
const savingProfile = ref(false);

const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' });
const passwordError = ref('');
const passwordSuccess = ref('');
const passwordErrors = reactive({});
const savingPassword = ref(false);

onMounted(() => {
    profileForm.value.name = auth.user.value?.name || '';
    profileForm.value.email = auth.user.value?.email || '';
});

async function handleUpdateProfile() {
    savingProfile.value = true;
    profileError.value = '';
    Object.keys(profileErrors).forEach(k => delete profileErrors[k]);
    try {
        await api.put('/profile', {
            name: profileForm.value.name,
            email: profileForm.value.email,
        });
        await authStore.fetchUser();
        notify.success('Profile updated successfully!');
    } catch (e) {
        const errs = e.response?.data?.errors;
        if (errs) {
            Object.entries(errs).forEach(([key, msgs]) => { profileErrors[key] = msgs[0]; });
            profileError.value = Object.values(errs).flat()[0];
        } else {
            profileError.value = e.response?.data?.message || 'Failed to update profile.';
        }
    } finally {
        savingProfile.value = false;
    }
}

async function handleUpdatePassword() {
    savingPassword.value = true;
    passwordError.value = '';
    passwordSuccess.value = '';
    Object.keys(passwordErrors).forEach(k => delete passwordErrors[k]);
    try {
        await api.put('/profile/password', passwordForm.value);
        passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
        passwordSuccess.value = 'Password updated successfully!';
        notify.success('Password updated successfully!');
    } catch (e) {
        const errs = e.response?.data?.errors;
        if (errs) {
            Object.entries(errs).forEach(([key, msgs]) => { passwordErrors[key] = msgs[0]; });
            passwordError.value = Object.values(errs).flat()[0];
        } else {
            passwordError.value = e.response?.data?.message || 'Failed to update password.';
        }
    } finally {
        savingPassword.value = false;
    }
}
</script>
