<template>
    <GrowBizLayout>
        <PullToRefresh ref="pullToRefreshRef" @refresh="handleRefresh">
            <div class="px-4 py-4 pb-6">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>
                    <p class="text-gray-500 mt-1">Track your team's performance</p>
                </div>

                <!-- Error State -->
                <div v-if="error" class="mb-4 p-4 bg-red-50 rounded-xl border border-red-200">
                    <p class="text-red-600 text-sm">{{ error }}</p>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-4 text-white shadow-lg">
                        <p class="text-sm text-blue-100 mb-1">Total Tasks</p>
                        <p class="text-3xl font-bold">{{ taskAnalytics?.summary?.total || 0 }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg">
                        <p class="text-sm text-emerald-100 mb-1">Completion Rate</p>
                        <p class="text-3xl font-bold">{{ taskAnalytics?.summary?.completion_rate || 0 }}%</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-4 text-white shadow-lg">
                        <p class="text-sm text-amber-100 mb-1">On-Time Rate</p>
                        <p class="text-3xl font-bold">{{ taskAnalytics?.summary?.on_time_rate || 0 }}%</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-500 to-rose-500 rounded-2xl p-4 text-white shadow-lg">
                        <p class="text-sm text-red-100 mb-1">Overdue</p>
                        <p class="text-3xl font-bold">{{ taskAnalytics?.summary?.overdue || 0 }}</p>
                    </div>
                </div>

                <!-- Period Summary -->
                <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">This Week vs This Month</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 mb-1">This Week</p>
                            <p class="text-lg font-bold text-blue-600">{{ weekSummary?.tasks_completed || 0 }} completed</p>
                            <p class="text-sm text-gray-500">{{ weekSummary?.hours_logged || 0 }}h logged</p>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 mb-1">This Month</p>
                            <p class="text-lg font-bold text-emerald-600">{{ monthSummary?.tasks_completed || 0 }} completed</p>
                            <p class="text-sm text-gray-500">{{ monthSummary?.hours_logged || 0 }}h logged</p>
                        </div>
                    </div>
                </div>

                <!-- Tasks by Status -->
                <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Tasks by Status</h3>
                    <div class="space-y-3">
                        <div v-for="(count, status) in taskAnalytics?.by_status" :key="status" class="flex items-center">
                            <div class="w-24 text-sm text-gray-600 capitalize">{{ formatStatus(status) }}</div>
                            <div class="flex-1 h-6 bg-gray-100 rounded-full overflow-hidden">
                                <div 
                                    :class="getStatusBarClass(status)"
                                    class="h-full rounded-full transition-all duration-500"
                                    :style="{ width: getPercentage(count, taskAnalytics?.summary?.total) + '%' }"
                                ></div>
                            </div>
                            <div class="w-12 text-right text-sm font-medium text-gray-900">{{ count }}</div>
                        </div>
                    </div>
                </div>

                <!-- Tasks by Priority -->
                <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Tasks by Priority</h3>
                    <div class="grid grid-cols-4 gap-2">
                        <div class="text-center p-3 bg-gray-50 rounded-xl">
                            <p class="text-xl font-bold text-gray-500">{{ taskAnalytics?.by_priority?.low || 0 }}</p>
                            <p class="text-xs text-gray-500">Low</p>
                        </div>
                        <div class="text-center p-3 bg-blue-50 rounded-xl">
                            <p class="text-xl font-bold text-blue-600">{{ taskAnalytics?.by_priority?.medium || 0 }}</p>
                            <p class="text-xs text-gray-500">Medium</p>
                        </div>
                        <div class="text-center p-3 bg-amber-50 rounded-xl">
                            <p class="text-xl font-bold text-amber-600">{{ taskAnalytics?.by_priority?.high || 0 }}</p>
                            <p class="text-xs text-gray-500">High</p>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded-xl">
                            <p class="text-xl font-bold text-red-600">{{ taskAnalytics?.by_priority?.urgent || 0 }}</p>
                            <p class="text-xs text-gray-500">Urgent</p>
                        </div>
                    </div>
                </div>

                <!-- Time Tracking -->
                <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Time Tracking</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-purple-50 rounded-xl">
                            <p class="text-xl font-bold text-purple-600">{{ taskAnalytics?.time_tracking?.total_estimated || 0 }}h</p>
                            <p class="text-xs text-gray-500">Estimated</p>
                        </div>
                        <div class="text-center p-3 bg-indigo-50 rounded-xl">
                            <p class="text-xl font-bold text-indigo-600">{{ taskAnalytics?.time_tracking?.total_actual || 0 }}h</p>
                            <p class="text-xs text-gray-500">Actual</p>
                        </div>
                        <div class="text-center p-3 bg-cyan-50 rounded-xl">
                            <p class="text-xl font-bold text-cyan-600">{{ taskAnalytics?.summary?.time_efficiency || 0 }}%</p>
                            <p class="text-xs text-gray-500">Efficiency</p>
                        </div>
                    </div>
                </div>

                <!-- Workload Distribution -->
                <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Team Workload</h3>
                    <div v-if="workloadDistribution.length > 0" class="space-y-3">
                        <div v-for="employee in workloadDistribution.slice(0, 5)" :key="employee.id" class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-sm font-medium">
                                {{ getInitials(employee.name) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ employee.name }}</p>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span>{{ employee.active_tasks }} active</span>
                                    <span>•</span>
                                    <span>{{ employee.completed }} done</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold" :class="getWorkloadColor(employee.active_tasks)">
                                    {{ employee.active_tasks }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-gray-500 text-sm">
                        No employees assigned yet
                    </div>
                </div>

                <!-- Productivity Trend (Simple) -->
                <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">14-Day Trend</h3>
                    <div v-if="productivityTrends.length > 0" class="flex items-end gap-1 h-24">
                        <div 
                            v-for="(day, index) in productivityTrends" 
                            :key="index"
                            class="flex-1 bg-emerald-500 rounded-t transition-all duration-300 hover:bg-emerald-600"
                            :style="{ height: getTrendHeight(day.completed) + '%', minHeight: day.completed > 0 ? '8px' : '2px' }"
                            :title="`${day.date}: ${day.completed} completed`"
                        ></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-400">
                        <span>{{ formatTrendDate(productivityTrends[0]?.date) }}</span>
                        <span>{{ formatTrendDate(productivityTrends[productivityTrends.length - 1]?.date) }}</span>
                    </div>
                </div>

                <!-- Due Date Overview -->
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <h3 class="font-semibold text-gray-900 mb-4">Due Date Overview</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-red-50 rounded-xl border border-red-100">
                            <p class="text-2xl font-bold text-red-600">{{ taskAnalytics?.due_dates?.overdue || 0 }}</p>
                            <p class="text-xs text-gray-500">Overdue</p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-100">
                            <p class="text-2xl font-bold text-amber-600">{{ taskAnalytics?.due_dates?.due_this_week || 0 }}</p>
                            <p class="text-xs text-gray-500">Due This Week</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                            <p class="text-2xl font-bold text-blue-600">{{ taskAnalytics?.due_dates?.due_this_month || 0 }}</p>
                            <p class="text-xs text-gray-500">Due This Month</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <p class="text-2xl font-bold text-gray-500">{{ taskAnalytics?.due_dates?.no_due_date || 0 }}</p>
                            <p class="text-xs text-gray-500">No Due Date</p>
                        </div>
                    </div>
                </div>
            </div>
        </PullToRefresh>
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import PullToRefresh from '@/Components/GrowBiz/PullToRefresh.vue';

interface TaskAnalytics {
    summary: {
        total: number;
        completion_rate: number;
        on_time_rate: number;
        time_efficiency: number;
        overdue: number;
    };
    by_status: Record<string, number>;
    by_priority: Record<string, number>;
    time_tracking: {
        total_estimated: number;
        total_actual: number;
        tasks_with_time: number;
    };
    due_dates: {
        overdue: number;
        due_this_week: number;
        due_this_month: number;
        no_due_date: number;
    };
    completion: {
        on_time: number;
        late: number;
        total_completed: number;
    };
}

interface WorkloadItem {
    id: number;
    name: string;
    active_tasks: number;
    pending: number;
    in_progress: number;
    completed: number;
    total: number;
}

interface TrendItem {
    date: string;
    created: number;
    completed: number;
    hours_logged: number;
}

interface PeriodSummary {
    period: string;
    period_start: string;
    tasks_created: number;
    tasks_completed: number;
    hours_logged: number;
}

interface Props {
    taskAnalytics: TaskAnalytics | null;
    employeePerformance: any[];
    workloadDistribution: WorkloadItem[];
    productivityTrends: TrendItem[];
    weekSummary: PeriodSummary | null;
    monthSummary: PeriodSummary | null;
    error?: string;
}

const props = defineProps<Props>();

const pullToRefreshRef = ref<InstanceType<typeof PullToRefresh> | null>(null);

const handleRefresh = () => {
    router.reload({
        onFinish: () => {
            pullToRefreshRef.value?.finishRefresh();
        }
    });
};

const formatStatus = (status: string): string => {
    return status.replace('_', ' ');
};

const getStatusBarClass = (status: string): string => {
    const classes: Record<string, string> = {
        pending: 'bg-gray-400',
        in_progress: 'bg-amber-500',
        on_hold: 'bg-purple-500',
        completed: 'bg-emerald-500',
        cancelled: 'bg-red-400',
    };
    return classes[status] || 'bg-gray-400';
};

const getPercentage = (count: number, total: number | undefined): number => {
    if (!total || total === 0) return 0;
    return Math.round((count / total) * 100);
};

const getInitials = (name: string): string => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getWorkloadColor = (count: number): string => {
    if (count >= 5) return 'text-red-600';
    if (count >= 3) return 'text-amber-600';
    return 'text-emerald-600';
};

const getTrendHeight = (completed: number): number => {
    const max = Math.max(...props.productivityTrends.map(t => t.completed), 1);
    return (completed / max) * 100;
};

const formatTrendDate = (date: string | undefined): string => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>
