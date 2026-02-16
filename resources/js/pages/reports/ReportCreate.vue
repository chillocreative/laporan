<template>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="page-title">Create New Report</h1>
            <p class="page-subtitle">Submit a new report for review</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                    <div>
                        <label class="label-text">Title *</label>
                        <input v-model="form.title" type="text" required class="input-field" placeholder="Brief title of the issue" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Category *</label>
                            <select v-model="form.category" required class="input-field">
                                <option value="">Select category</option>
                                <option v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label-text">Date *</label>
                            <input v-model="form.incident_date" type="date" required class="input-field" :max="today" />
                        </div>
                    </div>

                    <div>
                        <label class="label-text">Report *</label>
                        <RichTextEditor v-model="form.description" placeholder="Describe the issue in detail..." />
                    </div>

                    <FileUpload v-model="attachments" label="Attachments (optional)" />

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <router-link :to="{ name: 'reports.index' }" class="btn-secondary">Cancel</router-link>
                        <button type="submit" :disabled="submitting" class="btn-primary">
                            {{ submitting ? 'Submitting...' : 'Submit Report' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotification } from '../../composables/useNotification';
import reportsApi from '../../api/reports';
import categoriesApi from '../../api/categories';
import FileUpload from '../../components/common/FileUpload.vue';
import RichTextEditor from '../../components/common/RichTextEditor.vue';

const router = useRouter();
const notify = useNotification();

const today = computed(() => new Date().toISOString().split('T')[0]);
const categoryOptions = ref([]);
const form = ref({ title: '', category: '', description: '', incident_date: '' });
const attachments = ref([]);
const submitting = ref(false);
const errorMsg = ref('');

onMounted(async () => {
    try {
        const { data } = await categoriesApi.active();
        categoryOptions.value = data.data;
    } catch {}
});

async function handleSubmit() {
    submitting.value = true;
    errorMsg.value = '';
    try {
        const formData = new FormData();
        Object.entries(form.value).forEach(([key, val]) => formData.append(key, val));
        attachments.value.forEach((file) => formData.append('attachments[]', file));

        await reportsApi.create(formData);
        notify.success('Report submitted successfully!');
        router.push({ name: 'reports.index' });
    } catch (e) {
        const errors = e.response?.data?.errors;
        errorMsg.value = errors ? Object.values(errors).flat()[0] : 'Failed to submit report.';
    } finally {
        submitting.value = false;
    }
}
</script>
