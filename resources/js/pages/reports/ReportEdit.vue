<template>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <router-link :to="{ name: 'reports.show', params: { id: reportId } }" class="text-sm text-gray-500 hover:text-primary-600 mb-1 inline-block">&larr; Kembali</router-link>
            <h1 class="page-title">Edit Laporan</h1>
        </div>

        <LoadingSpinner v-if="loading" full-page />

        <div v-else class="card">
            <div class="card-body">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <div v-if="errorMsg" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ errorMsg }}</div>

                    <div>
                        <label class="label-text">Tajuk *</label>
                        <input v-model="form.title" type="text" required class="input-field" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Kategori *</label>
                            <select v-model="form.category" required class="input-field">
                                <option v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label-text">Tarikh *</label>
                            <input v-model="form.incident_date" type="date" required class="input-field" />
                        </div>
                    </div>

                    <div>
                        <label class="label-text">Laporan *</label>
                        <RichTextEditor v-model="form.description" />
                    </div>

                    <FileUpload v-model="newAttachments" label="Tambah Lampiran Lagi" />

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <router-link :to="{ name: 'reports.show', params: { id: reportId } }" class="btn-secondary">Batal</router-link>
                        <button type="submit" :disabled="submitting" class="btn-primary">
                            {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useNotification } from '../../composables/useNotification';
import reportsApi from '../../api/reports';
import categoriesApi from '../../api/categories';
import FileUpload from '../../components/common/FileUpload.vue';
import RichTextEditor from '../../components/common/RichTextEditor.vue';
import LoadingSpinner from '../../components/common/LoadingSpinner.vue';

const props = defineProps({ id: [String, Number] });
const route = useRoute();
const router = useRouter();
const notify = useNotification();

const reportId = computed(() => props.id || route.params.id);
const categoryOptions = ref([]);
const form = ref({ title: '', category: '', description: '', incident_date: '' });
const newAttachments = ref([]);
const loading = ref(true);
const submitting = ref(false);
const errorMsg = ref('');

onMounted(async () => {
    try {
        const catRes = await categoriesApi.active();
        categoryOptions.value = catRes.data.data;
    } catch {}
    try {
        const { data } = await reportsApi.get(reportId.value);
        const r = data.data;
        form.value = { title: r.title, category: r.category, description: r.description, incident_date: r.incident_date };
    } catch { router.push({ name: 'reports.index' }); }
    loading.value = false;
});

async function handleSubmit() {
    submitting.value = true;
    errorMsg.value = '';
    try {
        const formData = new FormData();
        Object.entries(form.value).forEach(([key, val]) => formData.append(key, val));
        newAttachments.value.forEach((file) => formData.append('attachments[]', file));
        await reportsApi.update(reportId.value, formData);
        notify.success('Laporan dikemas kini!');
        router.push({ name: 'reports.show', params: { id: reportId.value } });
    } catch (e) {
        const errors = e.response?.data?.errors;
        errorMsg.value = errors ? Object.values(errors).flat()[0] : 'Gagal mengemas kini.';
    } finally { submitting.value = false; }
}
</script>
