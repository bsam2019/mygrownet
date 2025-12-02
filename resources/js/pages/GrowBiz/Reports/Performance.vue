<template>
    <GrowBizLayout>
        <PullToRefresh ref="pullToRefreshRef" @refresh="handleRefresh">
            <div class="px-4 py-4 pb-6">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Performance</h1>
                    <p class="text-gray-500 mt-1">Team performance metrics</p>
                </div>

                <!-- Error State -->
                <div v-if="error" class="mb-4 p-4 bg-red-50 rounded-xl border border-red-200">
                    <p class="text-red-600 text-sm">{{ error }}</p>
                </div>

                <!-- Period Selector -->
                <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                    <button 
                        v-for="p in periods" 
                        :key="p.value"
                        @click="changePeriod(p.value)"
                        :class="[
                            'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                            period === p.value 
                                ? 'bg-emerald-600 text-white' 
                                : 'bg-gray-100 text-gray-600 active:bg-gray-200'
                        ]"
                    >
                        {{ p.label }}
                    </button>
                </div>

                <!-- Summary Card -->
                <div v-if="summary" class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg mb-6">
                    <p class="text-sm text-emerald-100 mb-2">{{ getPeriodLabel() }} Summary</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-2xl font-bold">{{ summary.tasks_created }}</p>
                            <p class="text-xs text-emerald-100">Created</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ summary.tasks_completed }}</p>
                            <p class="text-xs text-emerald-100">Completed</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ summary.hours_logged }}h</p>
                            <p class="text-xs text-emerald-100">Hours</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-white rounded-2xl shadow-sm p-4">
                        <p class="text-sm text-gray-500 mb-1">Total Tasks</p>
                        <p class="text-2xl font-bold text-gray-900">{{ taskStats?.total || 0 }}</p>
                        <p class="text-xs text-emerald-600 mt-1">{{ taskStats?.completion_rate || 0 }}% complete</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-4">
                        <p class="text-sm text-gray-500 mb-1">Team Size</p>
                        <p class="text-2xl font-bold text-gray-900">{{ employeeStats?.total || 0 }}</p>
                        <p class="text-xs text-emerald-600 mt-1">{{ employeeStats?.active || 0 }} active</p>
                    </div>
                </div>

                <!-- Employee Performance List -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Employee Performance</h3>
                    </div>
                    
                    <div v-if="employeePerformance.length > 0">
                        <div 
                            v-for="employee in employeePerformance" 
                            :key="employee.id"
                            class="p-4 border-b border-gray-100 last:border-b-0"
                        >
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-medium">
                                    {{ getInitials(employee.name) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ employee.name }}</p>
                                    <p class="text-sm text-gray-500">{{ employee.position || 'Team Member' }}</p>
                                </div>
                                <div class="text-right">
                                    <span 
                                        class="text-lg font-bold"
                                        :class="getPerformanceColor(employee.tasks?.completion_rate)"
                                    >
                                        {{ employee.tasks?.completion_rate || 0 }}%
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Performance Metrics -->
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="p-2 bg-gray-50 rounded-lg">
                                    <p class="text-sm font-bold text-gray-900">{{ employee.tasks?.total || 0 }}</p>
                                    <p class="text-xs text-gray-500">Total</p>
                                </div>
                                <div class="p-2 bg-emerald-50 rounded-lg">
                                    <p class="text-sm font-bold text-emerald-600">{{ employee.tasks?.completed || 0 }}</p>
                                    <p class="text-xs text-gray-500">Done</p>
                                </div>
                                <div class="p-2 bg-amber-50 rounded-lg">
                                    <p class="text-sm font-bold text-amber-600">{{ employee.tasks?.in_progress || 0 }}</p>
                                    <p class="text-xs text-gray-500">Active</p>
                                </div>
                                <div class="p-2 bg-blue-50 rounded-lg">
                                    <p class="text-sm font-bold text-blue-600">{{ employee.hours_logged || 0 }}h</p>
                                    <p class="text-xs text-gray-500">Hours</p>
                                </div>
                            </div>

                            <!-- On-Time Rate -->
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span>On-Time Completion</span>
                                    <span>{{ employee.on_time_rate || 0 }}%</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                        :style="{ width: (employee.on_time_rate || 0) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="p-8 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                            <UsersIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                        </div>
                        <p class="text-gray-500 text-sm">No employee data available</p>
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
import { UsersIcon } from '@heroicons/vue/24/outline';

interface EmployeePerformance {
    id: number;
    name: string;
    position: string | null;
    department: string | null;
    tasks: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
        completion_rate: number;
    };
    hours_logged: number;
    on_time_completions: number;
    late_completions: number;
    on_time_rate: number;
}

interface PeriodSummary {
    period: string;
    period_start: string;
    tasks_created: number;
    tasks_completed: number;
    hours_logged: number;
}

interface Props {
    employeePerformance: EmployeePerformance[];
    summary: PeriodSummary | null;
    taskStats: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
        overdue: number;
        completion_rate: number;
    } | null;
    employeeStats: {
        total: number;
        active: number;
        inactive: number;
        on_leave: number;
    } | null;
    period: string;
    error?: string;
}

const props = defineProps<Props>();

const pullToRefreshRef = ref<InstanceType<typeof PullToRefresh> | null>(null);

const periods = [
    { value: 'day', label: 'Today' },
    { value: 'week', label: 'This Week' },
    { value: 'month', label: 'This Month' },
    { value: 'quarter', label: 'Quarter' },
];

const handleRefresh = () => {
    router.reload({
        onFinish: () => {
            pullToRefreshRef.value?.finishRefresh();
        }
    });
};

const changePeriod = (newPeriod: string) => {
    router.get(route('growbiz.reports.performance'), { period: newPeriod }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getPeriodLabel = (): string => {
    const found = periods.find(p => p.value === props.period);
    return found?.label || 'This Week';
};

const getInitials = (name: string): string => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getPerformanceColor = (rate: number | undefined): string => {
    if (!rate) return 'text-gray-500';
    if (rate >= 80) return 'text-emerald-600';
    if (rate >= 60) return 'text-amber-600';
    return 'text-red-600';
};
</script>
