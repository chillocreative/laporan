<template>
    <div>
        <div class="mb-6">
            <h1 class="page-title">Tetapan</h1>
            <p class="page-subtitle">Urus konfigurasi sistem</p>
        </div>

        <LoadingSpinner v-if="loading" full-page text="Memuatkan tetapan..." />

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
                            <label class="label-text">Nama Sistem</label>
                            <input
                                v-model="forms.general.system_name"
                                type="text"
                                class="input-field"
                                placeholder="Masukkan nama sistem"
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('general')"
                            :disabled="saving.general"
                            class="btn-primary"
                        >
                            <span v-if="saving.general">Menyimpan...</span>
                            <span v-else>Simpan Tetapan Umum</span>
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
                            <label class="label-text">Logo Sistem</label>

                            <!-- Current logo preview -->
                            <div v-if="logoPreview" class="mt-2 mb-3">
                                <p class="text-xs text-gray-500 mb-1">Logo semasa:</p>
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
                            <p class="mt-1 text-xs text-gray-500">Format diterima: JPG, PNG, SVG. Maksimum 2MB disyorkan.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('branding')"
                            :disabled="saving.branding || !forms.branding.logo"
                            class="btn-primary"
                        >
                            <span v-if="saving.branding">Memuat naik...</span>
                            <span v-else>Simpan Penjenamaan</span>
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
                                <label class="label-text">Hos SMTP</label>
                                <input
                                    v-model="forms.smtp.smtp_host"
                                    type="text"
                                    class="input-field"
                                    placeholder="smtp-relay.brevo.com"
                                />
                            </div>
                            <div>
                                <label class="label-text">Port SMTP</label>
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
                                <label class="label-text">Nama Pengguna</label>
                                <input
                                    v-model="forms.smtp.smtp_username"
                                    type="text"
                                    class="input-field"
                                    placeholder="your-smtp-login"
                                />
                            </div>
                            <div>
                                <label class="label-text">Kata Laluan</label>
                                <input
                                    v-model="forms.smtp.smtp_password"
                                    type="password"
                                    class="input-field"
                                    placeholder="Masukkan kata laluan SMTP"
                                />
                                <p class="mt-1 text-xs text-gray-500">Disimpan secara disulitkan di pelayan.</p>
                            </div>
                        </div>

                        <div>
                            <label class="label-text">Penyulitan</label>
                            <select v-model="forms.smtp.smtp_encryption" class="input-field">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">None</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text">Alamat Pengirim</label>
                                <input
                                    v-model="forms.smtp.smtp_from_address"
                                    type="email"
                                    class="input-field"
                                    placeholder="noreply@yourdomain.com"
                                />
                            </div>
                            <div>
                                <label class="label-text">Nama Pengirim</label>
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
                            <span v-if="saving.smtp">Menyimpan...</span>
                            <span v-else>Simpan Tetapan SMTP</span>
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
                                <label class="label-text">Aktifkan Integrasi OpenAI</label>
                                <p class="text-xs text-gray-500">Benarkan analisis laporan berkuasa AI</p>
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
                            <label class="label-text">Kunci API</label>
                            <input
                                v-model="forms.openai.openai_api_key"
                                type="password"
                                class="input-field"
                                placeholder="sk-..."
                            />
                            <p class="mt-1 text-xs text-gray-500">Kunci API OpenAI anda. Disimpan secara disulitkan di pelayan.</p>
                        </div>

                        <!-- Model -->
                        <div>
                            <label class="label-text">Model</label>
                            <select v-model="forms.openai.openai_model" class="input-field">
                                <option value="" disabled>Pilih model</option>
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
                            <label class="label-text">Suhu</label>
                            <input
                                v-model.number="forms.openai.openai_temperature"
                                type="number"
                                step="0.1"
                                min="0"
                                max="2"
                                class="input-field"
                                placeholder="0.7"
                            />
                            <p class="mt-1 text-xs text-gray-500">Mengawal tahap rawak. Nilai rendah lebih fokus, nilai tinggi lebih kreatif (0-2).</p>
                        </div>

                        <!-- Max Tokens -->
                        <div>
                            <label class="label-text">Token Maksimum</label>
                            <input
                                v-model.number="forms.openai.openai_max_tokens"
                                type="number"
                                min="100"
                                max="4000"
                                class="input-field"
                                placeholder="1000"
                            />
                            <p class="mt-1 text-xs text-gray-500">Bilangan maksimum token dalam respons (100-4000).</p>
                        </div>

                        <!-- Queue Enabled toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="label-text">Gilirkan Permintaan AI</label>
                                <p class="text-xs text-gray-500">Proses permintaan AI secara tidak segerak melalui baris gilir</p>
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
                            <label class="label-text">Had Permintaan Harian</label>
                            <input
                                v-model.number="forms.openai.openai_daily_limit"
                                type="number"
                                min="0"
                                class="input-field"
                                placeholder="100"
                            />
                            <p class="mt-1 text-xs text-gray-500">Permintaan AI maksimum setiap hari. Tetapkan 0 untuk tanpa had.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('openai')"
                            :disabled="saving.openai"
                            class="btn-primary"
                        >
                            <span v-if="saving.openai">Menyimpan...</span>
                            <span v-else>Simpan Tetapan OpenAI</span>
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
                            <label class="label-text">Kunci Tapak reCAPTCHA</label>
                            <input
                                v-model="forms.captcha.recaptcha_site_key"
                                type="text"
                                class="input-field"
                                placeholder="6Lc..."
                            />
                        </div>

                        <div>
                            <label class="label-text">Kunci Rahsia reCAPTCHA</label>
                            <input
                                v-model="forms.captcha.recaptcha_secret_key"
                                type="password"
                                class="input-field"
                                placeholder="6Lc..."
                            />
                            <p class="mt-1 text-xs text-gray-500">Kunci rahsia Google reCAPTCHA v2/v3 anda. Disimpan secara disulitkan di pelayan.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="saveGroup('captcha')"
                            :disabled="saving.captcha"
                            class="btn-primary"
                        >
                            <span v-if="saving.captcha">Menyimpan...</span>
                            <span v-else>Simpan Tetapan Captcha</span>
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
    { key: 'general', label: 'Umum' },
    { key: 'branding', label: 'Penjenamaan' },
    { key: 'smtp', label: 'SMTP / E-mel' },
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
        alerts[group].message = 'Tetapan berjaya disimpan.';
        notification.success('Tetapan berjaya disimpan.');
    } catch (error) {
        const message = error.response?.data?.message || 'Gagal menyimpan tetapan. Sila cuba lagi.';
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
        notification.error('Gagal memuatkan tetapan.');
    }
    loading.value = false;
});
</script>
