<template>
    <div>
        <div class="mb-6">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, {{ auth.user.value?.name }}</p>
        </div>

        <LoadingSpinner v-if="loading" full-page text="Loading dashboard..." />

        <template v-else>
            <!-- Super Admin Dashboard -->
            <template v-if="auth.isSuperAdmin.value">
                <!-- Stats row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <StatCard title="Total Reports" :value="stats.reports?.total || 0" color="blue"
                        icon="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    <StatCard title="AI Requests Today" :value="stats.ai_usage?.total_requests || 0" color="indigo"
                        icon="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Top Active Reporters -->
                    <div class="card">
                        <div class="card-header"><h3 class="text-base font-semibold text-gray-900">Top 5 Active Reporters</h3></div>
                        <div class="card-body">
                            <div class="space-y-3">
                                <div v-for="(reporter, index) in stats.top_reporters" :key="reporter.user_id" class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center justify-center h-6 w-6 rounded-full text-xs font-semibold" :class="index === 0 ? 'bg-yellow-100 text-yellow-800' : index === 1 ? 'bg-gray-100 text-gray-700' : index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-blue-50 text-blue-600'">{{ index + 1 }}</span>
                                        <span class="text-sm text-gray-700">{{ reporter.user_name }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ reporter.report_count }} reports</span>
                                </div>
                                <div v-if="!stats.top_reporters?.length" class="text-sm text-gray-400 text-center py-4">No reports yet</div>
                            </div>
                        </div>
                    </div>

                    <!-- Risk level breakdown -->
                    <div class="card">
                        <div class="card-header"><h3 class="text-base font-semibold text-gray-900">Reports by Risk Level</h3></div>
                        <div class="card-body">
                            <div class="space-y-3">
                                <div v-for="(count, level) in stats.reports?.by_risk_level" :key="level" class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" :class="riskColor(level)"></span>
                                        <span class="text-sm text-gray-600 capitalize">{{ level }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ count }}</span>
                                </div>
                                <div v-if="!Object.keys(stats.reports?.by_risk_level || {}).length" class="text-sm text-gray-400 text-center py-4">No AI analysis yet</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System health -->
                <div v-if="stats.system_health" class="card">
                    <div class="card-header"><h3 class="text-base font-semibold text-gray-900">System Health</h3></div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="flex items-center gap-3">
                                <span class="h-3 w-3 rounded-full" :class="stats.system_health.database ? 'bg-green-500' : 'bg-red-500'"></span>
                                <span class="text-sm text-gray-700">Database</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="h-3 w-3 rounded-full" :class="stats.system_health.queue ? 'bg-green-500' : 'bg-red-500'"></span>
                                <span class="text-sm text-gray-700">Queue</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="h-3 w-3 rounded-full" :class="stats.system_health.storage?.healthy ? 'bg-green-500' : 'bg-red-500'"></span>
                                <span class="text-sm text-gray-700">Storage ({{ stats.system_health.storage?.used_percent || 0 }}% used)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Admin Dashboard -->
            <template v-else-if="auth.isAdmin.value">
                <!-- Stats row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <StatCard title="Total Reports" :value="stats.reports?.total || 0" color="blue"
                        icon="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    <StatCard title="Total Users" :value="stats.total_users || 0" color="indigo"
                        icon="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    <router-link :to="{ name: 'users.index' }" class="block h-full">
                        <StatCard title="Pending Approvals" :value="stats.pending_approvals || 0" :color="stats.pending_approvals > 0 ? 'red' : 'green'"
                            icon="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </router-link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Top Active Reporters -->
                    <div class="card">
                        <div class="card-header"><h3 class="text-base font-semibold text-gray-900">Top 5 Active Reporters</h3></div>
                        <div class="card-body">
                            <div class="space-y-3">
                                <div v-for="(reporter, index) in stats.top_reporters" :key="reporter.user_id" class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center justify-center h-6 w-6 rounded-full text-xs font-semibold" :class="index === 0 ? 'bg-yellow-100 text-yellow-800' : index === 1 ? 'bg-gray-100 text-gray-700' : index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-blue-50 text-blue-600'">{{ index + 1 }}</span>
                                        <span class="text-sm text-gray-700">{{ reporter.user_name }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ reporter.report_count }} reports</span>
                                </div>
                                <div v-if="!stats.top_reporters?.length" class="text-sm text-gray-400 text-center py-4">No reports yet</div>
                            </div>
                        </div>
                    </div>

                    <!-- Risk level breakdown -->
                    <div class="card">
                        <div class="card-header"><h3 class="text-base font-semibold text-gray-900">Reports by Risk Level</h3></div>
                        <div class="card-body">
                            <div class="space-y-3">
                                <div v-for="(count, level) in stats.reports?.by_risk_level" :key="level" class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" :class="riskColor(level)"></span>
                                        <span class="text-sm text-gray-600 capitalize">{{ level }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ count }}</span>
                                </div>
                                <div v-if="!Object.keys(stats.reports?.by_risk_level || {}).length" class="text-sm text-gray-400 text-center py-4">No AI analysis yet</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Reports -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Recent Reports</h3>
                        <router-link :to="{ name: 'reports.index' }" class="text-sm text-primary-600 hover:text-primary-700 font-medium">View All</router-link>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="!recentReports.length" class="p-8 text-center text-sm text-gray-400">No reports yet.</div>
                        <div v-else class="divide-y divide-gray-100">
                            <router-link
                                v-for="report in recentReports"
                                :key="report.id"
                                :to="{ name: 'reports.show', params: { id: report.id } }"
                                class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ report.title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ report.category }} &middot; {{ report.incident_date }} &middot; by {{ report.user?.name }}</p>
                                </div>
                                <span v-if="report.ai_analysis?.risk_level" class="ml-3 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize"
                                    :class="riskBadge(report.ai_analysis.risk_level.value)">
                                    {{ report.ai_analysis.risk_level.label }}
                                </span>
                            </router-link>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Regular User Dashboard -->
            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <StatCard title="My Reports" :value="stats.reports?.total || 0" color="blue"
                        icon="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </div>

                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Recent Reports</h3>
                        <router-link :to="{ name: 'reports.create' }" class="btn-primary btn-sm">New Report</router-link>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="!recentReports.length" class="p-8 text-center text-sm text-gray-400">No reports yet. Create your first report!</div>
                        <div v-else class="divide-y divide-gray-100">
                            <router-link
                                v-for="report in recentReports"
                                :key="report.id"
                                :to="{ name: 'reports.show', params: { id: report.id } }"
                                class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors"
                            >
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ report.title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ report.category }} &middot; {{ report.incident_date }}</p>
                                </div>
                            </router-link>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '../../composables/useAuth';
import dashboardApi from '../../api/dashboard';
import StatCard from '../../components/common/StatCard.vue';
import LoadingSpinner from '../../components/common/LoadingSpinner.vue';

const auth = useAuth();
const loading = ref(true);
const stats = ref({});
const recentReports = ref([]);

const statusSummary = computed(() => {
    const pending = stats.value.reports?.by_status?.pending || 0;
    const inProgress = (stats.value.reports?.by_status?.in_progress || 0) + (stats.value.reports?.by_status?.under_review || 0);
    return `${pending} pending, ${inProgress} active`;
});

function riskColor(level) {
    return { low: 'bg-green-500', medium: 'bg-yellow-500', high: 'bg-orange-500', critical: 'bg-red-500' }[level] || 'bg-gray-400';
}

function riskBadge(level) {
    return {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        critical: 'bg-red-100 text-red-800',
    }[level] || 'bg-gray-100 text-gray-800';
}

function statusColor(status) {
    return {
        pending: 'bg-yellow-500',
        under_review: 'bg-blue-500',
        in_progress: 'bg-indigo-500',
        resolved: 'bg-green-500',
        rejected: 'bg-red-500',
    }[status] || 'bg-gray-400';
}

function formatStatus(status) {
    return status.replace(/_/g, ' ');
}

onMounted(async () => {
    try {
        const { data } = await dashboardApi.getStats();
        stats.value = data.data;
        // Admin recent reports come as paginated data
        if (data.data.recent_reports?.data) {
            recentReports.value = data.data.recent_reports.data;
        }
        // Regular user reports
        if (data.data.my_reports?.data) {
            recentReports.value = data.data.my_reports.data;
        }
    } catch {}
    loading.value = false;
});
</script>
