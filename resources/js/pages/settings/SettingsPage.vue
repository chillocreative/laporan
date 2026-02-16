<template>
    <div>
        <div class="mb-6">
            <h1 class="page-title">Settings</h1>
            <p class="page-subtitle">Manage system configuration</p>
        </div>

        <LoadingSpinner v-if="loading" full-page text="Loading settings..." />

        <template v-else>
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors"
                        :class="activeTab === tab.key
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Tab: General -->
            <div v-show="activeTab === 'general'" class="card">
                <div class="card-body">
                    <Alert v-if="alerts.general.message" :type="alerts.general.type" class="mb-4">
                        {{ alerts.general.message }}
                    </Alert>

                    <div class="space-y-4">
                        <div>
                            <label class="label-text">System Name</label>
                            <input
                                v-model="forms.general.system_name"
                                type="text"
                                class="input-field"
                                placeholder="Enter system name"
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('general')"
                            :disabled="saving.general"
                            class="btn-primary"
                        >
                            <span v-if="saving.general">Saving...</span>
                            <span v-else>Save General Settings</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab: Branding -->
            <div v-show="activeTab === 'branding'" class="card">
                <div class="card-body">
                    <Alert v-if="alerts.branding.message" :type="alerts.branding.type" class="mb-4">
                        {{ alerts.branding.message }}
                    </Alert>

                    <div class="space-y-4">
                        <div>
                            <label class="label-text">System Logo</label>

                            <!-- Current logo preview -->
                            <div v-if="logoPreview" class="mt-2 mb-3">
                                <p class="text-xs text-gray-500 mb-1">Current logo:</p>
                                <img
                                    :src="logoPreview"
                                    alt="System logo"
                                    class="h-16 w-auto rounded border border-gray-200 bg-white p-1"
                                />
                            </div>

                            <input
                                ref="logoInput"
                                type="file"
                                accept="image/*"
                                class="input-field"
                                @change="onLogoChange"
                            />
                            <p class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, SVG. Max 2MB recommended.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('branding')"
                            :disabled="saving.branding || !forms.branding.logo"
                            class="btn-primary"
                        >
                            <span v-if="saving.branding">Uploading...</span>
                            <span v-else>Save Branding</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab: SMTP / Email -->
            <div v-show="activeTab === 'smtp'" class="card">
                <div class="card-body">
                    <Alert v-if="alerts.smtp.message" :type="alerts.smtp.type" class="mb-4">
                        {{ alerts.smtp.message }}
                    </Alert>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text">SMTP Host</label>
                                <input
                                    v-model="forms.smtp.smtp_host"
                                    type="text"
                                    class="input-field"
                                    placeholder="smtp-relay.brevo.com"
                                />
                            </div>
                            <div>
                                <label class="label-text">SMTP Port</label>
                                <input
                                    v-model.number="forms.smtp.smtp_port"
                                    type="number"
                                    class="input-field"
                                    placeholder="587"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text">Username</label>
                                <input
                                    v-model="forms.smtp.smtp_username"
                                    type="text"
                                    class="input-field"
                                    placeholder="your-smtp-login"
                                />
                            </div>
                            <div>
                                <label class="label-text">Password</label>
                                <input
                                    v-model="forms.smtp.smtp_password"
                                    type="password"
                                    class="input-field"
                                    placeholder="Enter SMTP password"
                                />
                                <p class="mt-1 text-xs text-gray-500">Stored encrypted on the server.</p>
                            </div>
                        </div>

                        <div>
                            <label class="label-text">Encryption</label>
                            <select v-model="forms.smtp.smtp_encryption" class="input-field">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">None</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text">From Address</label>
                                <input
                                    v-model="forms.smtp.smtp_from_address"
                                    type="email"
                                    class="input-field"
                                    placeholder="noreply@yourdomain.com"
                                />
                            </div>
                            <div>
                                <label class="label-text">From Name</label>
                                <input
                                    v-model="forms.smtp.smtp_from_name"
                                    type="text"
                                    class="input-field"
                                    placeholder="System Name"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('smtp')"
                            :disabled="saving.smtp"
                            class="btn-primary"
                        >
                            <span v-if="saving.smtp">Saving...</span>
                            <span v-else>Save SMTP Settings</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab: OpenAI -->
            <div v-show="activeTab === 'openai'" class="card">
                <div class="card-body">
                    <Alert v-if="alerts.openai.message" :type="alerts.openai.type" class="mb-4">
                        {{ alerts.openai.message }}
                    </Alert>

                    <div class="space-y-4">
                        <!-- Enabled toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="label-text">Enable OpenAI Integration</label>
                                <p class="text-xs text-gray-500">Allow AI-powered analysis of reports</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    v-model="forms.openai.openai_enabled"
                                    type="checkbox"
                                    class="sr-only peer"
                                    :true-value="true"
                                    :false-value="false"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- API Key -->
                        <div>
                            <label class="label-text">API Key</label>
                            <input
                                v-model="forms.openai.openai_api_key"
                                type="password"
                                class="input-field"
                                placeholder="sk-..."
                            />
                            <p class="mt-1 text-xs text-gray-500">Your OpenAI API key. Stored encrypted on the server.</p>
                        </div>

                        <!-- Model -->
                        <div>
                            <label class="label-text">Model</label>
                            <select v-model="forms.openai.openai_model" class="input-field">
                                <option value="" disabled>Select a model</option>
                                <option
                                    v-for="model in OPENAI_MODELS"
                                    :key="model.value"
                                    :value="model.value"
                                >
                                    {{ model.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Temperature -->
                        <div>
                            <label class="label-text">Temperature</label>
                            <input
                                v-model.number="forms.openai.openai_temperature"
                                type="number"
                                step="0.1"
                                min="0"
                                max="2"
                                class="input-field"
                                placeholder="0.7"
                            />
                            <p class="mt-1 text-xs text-gray-500">Controls randomness. Lower values are more focused, higher values more creative (0-2).</p>
                        </div>

                        <!-- Max Tokens -->
                        <div>
                            <label class="label-text">Max Tokens</label>
                            <input
                                v-model.number="forms.openai.openai_max_tokens"
                                type="number"
                                min="100"
                                max="4000"
                                class="input-field"
                                placeholder="1000"
                            />
                            <p class="mt-1 text-xs text-gray-500">Maximum number of tokens in the response (100-4000).</p>
                        </div>

                        <!-- Queue Enabled toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="label-text">Queue AI Requests</label>
                                <p class="text-xs text-gray-500">Process AI requests asynchronously via queue</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    v-model="forms.openai.openai_queue_enabled"
                                    type="checkbox"
                                    class="sr-only peer"
                                    :true-value="true"
                                    :false-value="false"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Daily Limit -->
                        <div>
                            <label class="label-text">Daily Request Limit</label>
                            <input
                                v-model.number="forms.openai.openai_daily_limit"
                                type="number"
                                min="0"
                                class="input-field"
                                placeholder="100"
                            />
                            <p class="mt-1 text-xs text-gray-500">Maximum AI requests per day. Set 0 for unlimited.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('openai')"
                            :disabled="saving.openai"
                            class="btn-primary"
                        >
                            <span v-if="saving.openai">Saving...</span>
                            <span v-else>Save OpenAI Settings</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab: Captcha -->
            <div v-show="activeTab === 'captcha'" class="card">
                <div class="card-body">
                    <Alert v-if="alerts.captcha.message" :type="alerts.captcha.type" class="mb-4">
                        {{ alerts.captcha.message }}
                    </Alert>

                    <div class="space-y-4">
                        <div>
                            <label class="label-text">reCAPTCHA Site Key</label>
                            <input
                                v-model="forms.captcha.recaptcha_site_key"
                                type="text"
                                class="input-field"
                                placeholder="6Lc..."
                            />
                        </div>

                        <div>
                            <label class="label-text">reCAPTCHA Secret Key</label>
                            <input
                                v-model="forms.captcha.recaptcha_secret_key"
                                type="password"
                                class="input-field"
                                placeholder="6Lc..."
                            />
                            <p class="mt-1 text-xs text-gray-500">Your Google reCAPTCHA v2/v3 secret key. Stored encrypted on the server.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('captcha')"
                            :disabled="saving.captcha"
                            class="btn-primary"
                        >
                            <span v-if="saving.captcha">Saving...</span>
                            <span v-else>Save Captcha Settings</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import settingsApi from '../../api/settings';
import { useNotification } from '../../composables/useNotification';
import { OPENAI_MODELS } from '../../utils/constants';
import Alert from '../../components/common/Alert.vue';
import LoadingSpinner from '../../components/common/LoadingSpinner.vue';

const notification = useNotification();

const loading = ref(true);
const activeTab = ref('general');

const tabs = [
    { key: 'general', label: 'General' },
    { key: 'branding', label: 'Branding' },
    { key: 'smtp', label: 'SMTP / Email' },
    { key: 'openai', label: 'OpenAI' },
    { key: 'captcha', label: 'Captcha' },
];

// Reactive form data per group
const forms = reactive({
    general: {
        system_name: '',
    },
    branding: {
        logo: null,
    },
    smtp: {
        smtp_host: '',
        smtp_port: 587,
        smtp_username: '',
        smtp_password: '',
        smtp_encryption: 'tls',
        smtp_from_address: '',
        smtp_from_name: '',
    },
    openai: {
        openai_enabled: false,
        openai_api_key: '',
        openai_model: '',
        openai_temperature: 0.7,
        openai_max_tokens: 1000,
        openai_queue_enabled: false,
        openai_daily_limit: 100,
    },
    captcha: {
        recaptcha_site_key: '',
        recaptcha_secret_key: '',
    },
});

// Per-tab save state
const saving = reactive({
    general: false,
    branding: false,
    smtp: false,
    openai: false,
    captcha: false,
});

// Per-tab alert state
const alerts = reactive({
    general: { type: '', message: '' },
    branding: { type: '', message: '' },
    smtp: { type: '', message: '' },
    openai: { type: '', message: '' },
    captcha: { type: '', message: '' },
});

const logoPreview = ref(null);
const logoInput = ref(null);

function onLogoChange(event) {
    const file = event.target.files[0];
    if (file) {
        forms.branding.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
}

function clearAlert(group) {
    alerts[group].type = '';
    alerts[group].message = '';
}

function distributeSettings(grouped) {
    // General
    if (grouped.general) {
        forms.general.system_name = grouped.general.system_name || '';
    }

    // Branding - only set preview from existing logo URL
    if (grouped.branding?.system_logo) {
        logoPreview.value = grouped.branding.system_logo;
    }

    // SMTP
    if (grouped.smtp) {
        forms.smtp.smtp_host = grouped.smtp.smtp_host || '';
        forms.smtp.smtp_port = parseInt(grouped.smtp.smtp_port) || 587;
        forms.smtp.smtp_username = grouped.smtp.smtp_username || '';
        forms.smtp.smtp_password = grouped.smtp.smtp_password || '';
        forms.smtp.smtp_encryption = grouped.smtp.smtp_encryption || 'tls';
        forms.smtp.smtp_from_address = grouped.smtp.smtp_from_address || '';
        forms.smtp.smtp_from_name = grouped.smtp.smtp_from_name || '';
    }

    // OpenAI
    if (grouped.openai) {
        forms.openai.openai_enabled = grouped.openai.openai_enabled === '1' || grouped.openai.openai_enabled === true;
        forms.openai.openai_api_key = grouped.openai.openai_api_key || '';
        forms.openai.openai_model = grouped.openai.openai_model || '';
        forms.openai.openai_temperature = parseFloat(grouped.openai.openai_temperature) || 0.7;
        forms.openai.openai_max_tokens = parseInt(grouped.openai.openai_max_tokens) || 1000;
        forms.openai.openai_queue_enabled = grouped.openai.openai_queue_enabled === '1' || grouped.openai.openai_queue_enabled === true;
        forms.openai.openai_daily_limit = parseInt(grouped.openai.openai_daily_limit) || 100;
    }

    // Captcha
    if (grouped.captcha) {
        forms.captcha.recaptcha_site_key = grouped.captcha.recaptcha_site_key || '';
        forms.captcha.recaptcha_secret_key = grouped.captcha.recaptcha_secret_key || '';
    }
}

async function saveGroup(group) {
    saving[group] = true;
    clearAlert(group);

    try {
        await settingsApi.updateGroup(group, forms[group]);
        alerts[group].type = 'success';
        alerts[group].message = 'Settings saved successfully.';
        notification.success('Settings saved successfully.');
    } catch (error) {
        const message = error.response?.data?.message || 'Failed to save settings. Please try again.';
        alerts[group].type = 'error';
        alerts[group].message = message;
        notification.error(message);
    } finally {
        saving[group] = false;
    }
}

onMounted(async () => {
    try {
        const { data } = await settingsApi.getAll();
        distributeSettings(data.data || data);
    } catch {
        notification.error('Failed to load settings.');
    }
    loading.value = false;
});
</script>
