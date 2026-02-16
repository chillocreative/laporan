<template>
    <div>
        <LoadingSpinner v-if="loading" full-page />
        <template v-else-if="report">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                <div>
                    <router-link :to="{ name: 'reports.index' }" class="text-sm text-gray-500 hover:text-primary-600 mb-1 inline-block">&larr; Back to Reports</router-link>
                    <h1 class="page-title">{{ report.title }}</h1>
                </div>
                <div class="flex gap-2">
                    <router-link v-if="canEdit" :to="{ name: 'reports.edit', params: { id: report.id } }" class="btn-secondary btn-sm">Edit</router-link>
                    <button v-if="canDelete" @click="showDeleteDialog = true" class="btn-danger btn-sm">Delete</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main content -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="card">
                        <div class="card-header"><h3 class="font-semibold text-gray-900">Report Details</h3></div>
                        <div class="card-body space-y-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="text-gray-500">Category:</span><p class="font-medium">{{ report.category }}</p></div>
                                <div><span class="text-gray-500">Date:</span><p class="font-medium">{{ report.incident_date }}</p></div>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Report:</span>
                                <div class="mt-1 text-sm text-gray-700 prose prose-sm max-w-none" v-html="report.description"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div v-if="report.attachments?.length" class="card">
                        <div class="card-header"><h3 class="font-semibold text-gray-900">Attachments ({{ report.attachments.length }})</h3></div>
                        <div class="card-body">
                            <ul class="divide-y divide-gray-100">
                                <li v-for="att in report.attachments" :key="att.id" class="flex items-center justify-between py-2.5">
                                    <div class="flex items-center gap-2 text-sm min-w-0">
                                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                                        <span class="truncate">{{ att.original_name }}</span>
                                        <span class="text-gray-400 flex-shrink-0">({{ att.file_size_human }})</span>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0 ml-3">
                                        <a v-if="att.view_url" :href="att.view_url" target="_blank" title="View" class="inline-flex items-center justify-center h-8 w-8 rounded-md text-gray-500 hover:text-primary-600 hover:bg-primary-50 transition-colors">
                                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </a>
                                        <a :href="att.download_url" target="_blank" title="Download" class="inline-flex items-center justify-center h-8 w-8 rounded-md text-gray-500 hover:text-primary-600 hover:bg-primary-50 transition-colors">
                                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- AI Analysis -->
                    <div class="card">
                        <div class="card-header flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                                <h3 class="font-semibold text-gray-900">AI Analysis</h3>
                            </div>
                            <button
                                v-if="auth.hasPermission('ai.trigger')"
                                @click="triggerAI"
                                :disabled="analyzingAI"
                                class="btn-secondary btn-sm text-xs"
                            >
                                {{ analyzingAI ? 'Analyzing...' : (report.ai_analysis ? 'Re-analyze' : 'Trigger Analysis') }}
                            </button>
                        </div>
                        <div v-if="report.ai_analysis" class="card-body space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-3 rounded-lg bg-gray-50">
                                    <p class="text-xs text-gray-500 mb-1">Risk Level</p>
                                    <Badge :color="report.ai_analysis.risk_level?.color">{{ report.ai_analysis.risk_level?.label }}</Badge>
                                </div>
                                <div class="text-center p-3 rounded-lg bg-gray-50">
                                    <p class="text-xs text-gray-500 mb-1">Urgency</p>
                                    <p class="text-lg font-bold text-gray-900">{{ report.ai_analysis.urgency_score }}/10</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-1">Summary</p>
                                <p class="text-sm text-gray-700">{{ report.ai_analysis.summary }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-1">Recommended Action</p>
                                <p class="text-sm text-gray-700">{{ report.ai_analysis.recommended_action }}</p>
                            </div>
                            <div v-if="report.ai_analysis.related_issue">
                                <p class="text-xs font-medium text-gray-500 mb-1">Related Issue</p>
                                <p class="text-sm text-gray-700">{{ report.ai_analysis.related_issue }}</p>
                            </div>
                            <p class="text-xs text-gray-400">Analyzed {{ formatDateTime(report.ai_analysis.analyzed_at) }}</p>
                        </div>
                        <div v-else class="card-body">
                            <p class="text-sm text-gray-400 text-center py-4">No AI analysis yet. {{ auth.hasPermission('ai.trigger') ? 'Click "Trigger Analysis" to start.' : 'An admin can trigger analysis.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="card">
                        <div class="card-header"><h3 class="font-semibold text-gray-900">Info</h3></div>
                        <div class="card-body text-sm space-y-3">
                            <div><span class="text-gray-500">Submitted by:</span><p class="font-medium">{{ report.user?.name }}</p></div>
                            <div><span class="text-gray-500">Created:</span><p class="font-medium">{{ formatDateTime(report.created_at) }}</p></div>
                            <div><span class="text-gray-500">Updated:</span><p class="font-medium">{{ formatDateTime(report.updated_at) }}</p></div>
                        </div>
                    </div>

                </div>
            </div>
        </template>

        <ConfirmDialog v-model="showDeleteDialog" title="Delete Report" message="Are you sure you want to delete this report? This action cannot be undone." confirm-text="Delete" :danger="true" @confirm="handleDelete" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../../composables/useAuth';
import { useNotification } from '../../composables/useNotification';
import { formatDateTime } from '../../utils/formatters';
import reportsApi from '../../api/reports';
import Badge from '../../components/common/Badge.vue';
import LoadingSpinner from '../../components/common/LoadingSpinner.vue';
import ConfirmDialog from '../../components/common/ConfirmDialog.vue';

const props = defineProps({ id: [String, Number] });
const route = useRoute();
const router = useRouter();
const auth = useAuth();
const notify = useNotification();

const report = ref(null);
const loading = ref(true);
const showDeleteDialog = ref(false);
const analyzingAI = ref(false);

const reportId = computed(() => props.id || route.params.id);
const canEdit = computed(() => {
    if (!report.value) return false;
    if (auth.hasPermission('reports.edit-any')) return true;
    return auth.hasPermission('reports.edit-own') && report.value.user?.id === auth.user.value?.id;
});
const canDelete = computed(() => {
    if (!report.value) return false;
    if (auth.hasPermission('reports.delete-any')) return true;
    return auth.hasPermission('reports.delete-own') && report.value.user?.id === auth.user.value?.id;
});

async function fetchReport() {
    loading.value = true;
    try {
        const { data } = await reportsApi.get(reportId.value);
        report.value = data.data;
    } catch { router.push({ name: 'reports.index' }); }
    loading.value = false;
}

async function handleDelete() {
    try {
        await reportsApi.delete(reportId.value);
        notify.success('Report deleted.');
        router.push({ name: 'reports.index' });
    } catch { notify.error('Failed to delete report.'); }
    showDeleteDialog.value = false;
}

async function triggerAI() {
    analyzingAI.value = true;
    try {
        const { data } = await reportsApi.triggerAnalysis(reportId.value);
        if (data.status === 'completed') {
            report.value = data.data;
            notify.success(data.message);
        } else {
            notify.error(data.message || 'AI analysis failed.');
        }
    } catch (e) {
        notify.error(e.response?.data?.message || 'Failed to trigger AI analysis.');
    }
    analyzingAI.value = false;
}

onMounted(fetchReport);
</script>
