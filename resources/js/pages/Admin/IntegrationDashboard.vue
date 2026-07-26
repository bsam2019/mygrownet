<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

interface DashboardData {
    events: { published_24h: number; failed_24h: number; failure_rate: number };
    queue: { default_depth: number; events_queue_depth: number; integrations_depth: number };
    dead_letter: { pending: number; replayed: number; failed: number; total: number };
    outbox: { pending: number; failed: number };
    inbox: { received: number; processed: number; failed: number; total: number };
    contracts: { success_rate: number; slowest_calls: any[]; total_calls: number };
    applications: Record<string, any>;
    timestamp: string;
}

const props = defineProps<{ dashboard: DashboardData }>();

const statusColor = (status: string): string => {
    const colors: Record<string, string> = {
        healthy: 'bg-green-500', degraded: 'bg-yellow-500',
        maintenance: 'bg-blue-500', unavailable: 'bg-red-500', offline: 'bg-gray-400',
    };
    return colors[status] || 'bg-gray-400';
};

const formatRate = (rate: number): string => `${rate}%`;

function refresh(): void {
    router.get(route('admin.integration-dashboard'), {}, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout>
        <Head title="Integration Dashboard" />
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Integration Dashboard</h1>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500">Last updated: {{ dashboard.timestamp }}</span>
                        <button @click="refresh" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">Refresh</button>
                    </div>
                </div>

                <!-- Events & Contracts -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-500">Events Published (24h)</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ dashboard.events.published_24h }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-500">Events Failed (24h)</h3>
                        <p class="text-2xl font-bold text-red-600">{{ dashboard.events.failed_24h }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-500">Failure Rate</h3>
                        <p class="text-2xl font-bold" :class="dashboard.events.failure_rate > 5 ? 'text-red-600' : 'text-green-600'">{{ formatRate(dashboard.events.failure_rate) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-500">Contract Calls</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ dashboard.contracts.total_calls }}</p>
                    </div>
                </div>

                <!-- Queue & Dead Letter -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-500">Default Queue</h3>
                        <p class="text-2xl font-bold" :class="dashboard.queue.default_depth > 1000 ? 'text-red-600' : 'text-gray-900'">{{ dashboard.queue.default_depth }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-500">Events Queue</h3>
                        <p class="text-2xl font-bold" :class="dashboard.queue.events_queue_depth > 1000 ? 'text-red-600' : 'text-gray-900'">{{ dashboard.queue.events_queue_depth }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-500">DLQ Pending</h3>
                        <p class="text-2xl font-bold" :class="dashboard.dead_letter.pending > 0 ? 'text-red-600' : 'text-green-600'">{{ dashboard.dead_letter.pending }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-500">Outbox Pending</h3>
                        <p class="text-2xl font-bold" :class="dashboard.outbox.pending > 0 ? 'text-yellow-600' : 'text-gray-900'">{{ dashboard.outbox.pending }}</p>
                    </div>
                </div>

                <!-- Application Health -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="px-4 py-3 border-b">
                        <h2 class="text-lg font-medium">Application Health</h2>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Application</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Registry</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Last Heartbeat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(app, id) in dashboard.applications" :key="id">
                                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ id }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full" :class="statusColor(app.status?.value || app.status)"></span>
                                        {{ app.status?.value || app.status || 'unknown' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ app.registry_healthy ? 'healthy' : 'unknown' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ app.last_heartbeat || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Contract Slow Calls -->
                <div class="bg-white rounded-lg shadow mb-6" v-if="dashboard.contracts.slowest_calls.length > 0">
                    <div class="px-4 py-3 border-b">
                        <h2 class="text-lg font-medium">Slowest Contract Calls</h2>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contract</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Avg Duration</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Max Duration</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Calls</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(call, i) in dashboard.contracts.slowest_calls" :key="i">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ call.contract }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ call.method }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ call.avg_duration_ms }}ms</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ call.max_duration_ms }}ms</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ call.call_count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
