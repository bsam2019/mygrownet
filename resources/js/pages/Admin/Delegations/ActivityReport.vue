<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { ChartBarIcon, UserGroupIcon, ShieldCheckIcon, ArrowTrendingUpIcon } from '@heroicons/vue/24/outline';

interface Props {
    actionStats: Record<string, number>;
    topEmployees: { employee_id: number; usage_count: number; employee: { first_name: string; last_name: string } | null }[];
    topPermissions: { permission_key: string; usage_count: number }[];
    dailyTrend: { date: string; count: number }[];
    period: string;
}

const props = defineProps<Props>();
const selectedPeriod = ref(props.period);

const changePeriod = () => {
    router.get(route('admin.delegations.activity-report'), { period: selectedPeriod.value }, { preserveState: true });
};

const totalActions = Object.values(props.actionStats).reduce((a, b) => a + b, 0);

const getActionClass = (action: string) => ({
    'granted': 'text-green-600',
    'revoked': 'text-red-600',
    'used': 'text-blue-600',
    'approval_requested': 'text-amber-600',
    'approved': 'text-green-600',
    'rejected': 'text-red-600',
}[action] || 'text-gray-600');

const formatPermission = (key: string) => {
    const parts = key.split('.');
    return parts[parts.length - 1].replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<template>
    <Head title="Delegation Activity Report" />
    <AdminLayout :breadcrumbs="[{ title: 'Delegations', href: route('admin.delegations.index') }, { title: 'Activity Report' }]">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Activity Report</h1>
                    <p class="text-gray-500">Delegation usage analytics and trends</p>
                </div>
                <div class="flex gap-3">
                    <select v-model="selectedPeriod" @change="changePeriod" class="border border-gray-300 rounded-lg px-3 py-2">
                        <option value="week">Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                        <option value="quarter">Last 90 Days</option>
                    </select>
                    <Link :href="route('admin.delegations.logs')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        View Logs
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg"><ChartBarIcon class="h-5 w-5 text-blue-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ totalActions }}</p>
                            <p class="text-sm text-gray-500">Total Actions</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg"><ShieldCheckIcon class="h-5 w-5 text-green-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ actionStats.granted || 0 }}</p>
                            <p class="text-sm text-gray-500">Permissions Granted</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg"><ArrowTrendingUpIcon class="h-5 w-5 text-purple-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ actionStats.used || 0 }}</p>
                            <p class="text-sm text-gray-500">Permission Uses</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg"><UserGroupIcon class="h-5 w-5 text-amber-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ topEmployees.length }}</p>
                            <p class="text-sm text-gray-500">Active Employees</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Action Breakdown -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Action Breakdown</h2>
                    <div class="space-y-3">
                        <div v-for="(count, action) in actionStats" :key="action" class="flex items-center justify-between">
                            <span :class="['font-medium capitalize', getActionClass(action as string)]">{{ (action as string).replace('_', ' ') }}</span>
                            <div class="flex items-center gap-3">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" :style="{ width: `${(count / totalActions) * 100}%` }"></div>
                                </div>
                                <span class="text-sm text-gray-600 w-12 text-right">{{ count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Employees -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Most Active Employees</h2>
                    <div class="space-y-3">
                        <div v-for="(emp, i) in topEmployees" :key="emp.employee_id" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-400 w-6">{{ i + 1 }}.</span>
                                <span class="font-medium text-gray-900">{{ emp.employee?.first_name }} {{ emp.employee?.last_name }}</span>
                            </div>
                            <span class="text-sm text-blue-600 font-medium">{{ emp.usage_count }} uses</span>
                        </div>
                        <div v-if="topEmployees.length === 0" class="text-center text-gray-500 py-4">No activity data</div>
                    </div>
                </div>

                <!-- Top Permissions -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Most Used Permissions</h2>
                    <div class="space-y-3">
                        <div v-for="(perm, i) in topPermissions" :key="perm.permission_key" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-400 w-6">{{ i + 1 }}.</span>
                                <span class="font-medium text-gray-900">{{ formatPermission(perm.permission_key) }}</span>
                            </div>
                            <span class="text-sm text-blue-600 font-medium">{{ perm.usage_count }} uses</span>
                        </div>
                        <div v-if="topPermissions.length === 0" class="text-center text-gray-500 py-4">No usage data</div>
                    </div>
                </div>

                <!-- Daily Trend -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Daily Activity Trend</h2>
                    <div class="space-y-2">
                        <div v-for="day in dailyTrend.slice(-14)" :key="day.date" class="flex items-center gap-3">
                            <span class="text-sm text-gray-500 w-16">{{ day.date }}</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" :style="{ width: `${Math.min((day.count / Math.max(...dailyTrend.map(d => d.count), 1)) * 100, 100)}%` }"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-8 text-right">{{ day.count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
