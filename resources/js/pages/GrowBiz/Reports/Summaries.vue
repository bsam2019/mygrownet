<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import {
    CalendarDaysIcon,
    CalendarIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    ArrowDownTrayIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    FireIcon,
    TrophyIcon,
    DocumentArrowDownIcon,
} from '@heroicons/vue/24/outline';

interface DailySummary {
    date: string;
    date_formatted: string;
    tasks_created: number;
    tasks_completed: number;
    tasks_started: number;
    hours_logged: number;
    overdue_tasks: number;
    due_today: number;
    high_priority_pending: number;
    completed_tasks: Array<{ id: number; title: string; priority: string }>;
    started_tasks: Array<{ id: number; title: string; priority: string }>;
    overdue_list: Array<{ id: number; title: string; due_date: string; priority: string }>;
    due_today_list: Array<{ id: number; title: string; status: string; priority: string }>;
}

interface WeeklySummary {
    week_start: string;
    week_end: string;
    week_formatted: string;
    tasks_created: number;
    tasks_completed: number;
    tasks_in_progress: number;
    total_hours_logged: number;
    completion_rate: number;
    on_time_rate: number;
    overdue_tasks: number;
    daily_breakdown: Array<{ date: string; day_name: string; created: number; completed: number; hours: number }>;
    top_performers: Array<{ id: number; name: string; position: string; tasks_completed: number }>;
    priority_breakdown: Record<string, { created: number; completed: number }>;
    completed_on_time: number;
    completed_late: number;
}

const props = defineProps<{
    dailySummary: DailySummary | null;
    weeklySummary: WeeklySummary | null;
    type: string;
    selectedDate: string;
    error?: string;
}>();

const activeTab = ref<'daily' | 'weekly'>(props.type === 'weekly' ? 'weekly' : 'daily');
const selectedDate = ref(props.selectedDate);
const isLoading = ref(false);

const navigateDay = (direction: number) => {
    const date = new Date(selectedDate.value);
    date.setDate(date.getDate() + direction);
    selectedDate.value = date.toISOString().split('T')[0];
    loadDailySummary();
};

const loadDailySummary = () => {
    isLoading.value = true;
    router.get(route('growbiz.reports.summaries'), {
        type: 'daily',
        date: selectedDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => { isLoading.value = false; },
    });
};

const exportWeeklySummary = () => {
    window.location.href = route('growbiz.reports.export.weekly-summary');
};

const exportTasks = () => {
    window.location.href = route('growbiz.reports.export.tasks');
};

const exportEmployees = () => {
    window.location.href = route('growbiz.reports.export.employees');
};

const exportPerformance = () => {
    window.location.href = route('growbiz.reports.export.performance');
};

const priorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        urgent: 'text-red-600 bg-red-50',
        high: 'text-orange-600 bg-orange-50',
        medium: 'text-blue-600 bg-blue-50',
        low: 'text-gray-600 bg-gray-50',
    };
    return colors[priority] || colors.medium;
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    });
};

const isToday = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return selectedDate.value === today;
});
</script>

<template>
    <GrowBizLayout>
        <Head title="Summaries - GrowBiz" />

        <div class="max-w-4xl mx-auto px-4 py-6 pb-24">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Summaries & Reports</h1>
                <p class="text-gray-600 mt-1">View daily and weekly summaries, export your data</p>
            </div>

            <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700">{{ error }}</p>
            </div>

            <div class="flex gap-2 mb-6">
                <button
                    @click="activeTab = 'daily'"
                    :class="['flex-1 py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2',
                        activeTab === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                >
                    <CalendarIcon class="w-5 h-5" aria-hidden="true" />
                    Daily
                </button>
                <button
                    @click="activeTab = 'weekly'"
                    :class="['flex-1 py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2',
                        activeTab === 'weekly' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                >
                    <CalendarDaysIcon class="w-5 h-5" aria-hidden="true" />
                    Weekly
                </button>
            </div>

            <!-- Daily Summary Tab -->
            <div v-if="activeTab === 'daily'" class="space-y-4">
                <div class="flex items-center justify-between bg-white rounded-lg p-4 shadow-sm">
                    <button @click="navigateDay(-1)" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Previous day">
                        <ChevronLeftIcon class="w-5 h-5" aria-hidden="true" />
                    </button>
                    <div class="text-center">
                        <input type="date" v-model="selectedDate" @change="loadDailySummary"
                            class="text-lg font-semibold text-gray-900 border-0 text-center focus:ring-0 cursor-pointer" />
                        <p v-if="isToday" class="text-sm text-blue-600">Today</p>
                    </div>
                    <button @click="navigateDay(1)" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Next day">
                        <ChevronRightIcon class="w-5 h-5" aria-hidden="true" />
                    </button>
                </div>

                <div v-if="isLoading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="text-gray-500 mt-2">Loading summary...</p>
                </div>

                <div v-else-if="dailySummary" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                                <CheckCircleIcon class="w-4 h-4" aria-hidden="true" />
                                Completed
                            </div>
                            <p class="text-2xl font-bold text-green-600">{{ dailySummary.tasks_completed }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                                <ClockIcon class="w-4 h-4" aria-hidden="true" />
                                Hours Logged
                            </div>
                            <p class="text-2xl font-bold text-blue-600">{{ dailySummary.hours_logged }}h</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                                <ExclamationTriangleIcon class="w-4 h-4" aria-hidden="true" />
                                Overdue
                            </div>
                            <p class="text-2xl font-bold" :class="dailySummary.overdue_tasks > 0 ? 'text-red-600' : 'text-gray-400'">
                                {{ dailySummary.overdue_tasks }}
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                                <FireIcon class="w-4 h-4" aria-hidden="true" />
                                High Priority
                            </div>
                            <p class="text-2xl font-bold" :class="dailySummary.high_priority_pending > 0 ? 'text-orange-600' : 'text-gray-400'">
                                {{ dailySummary.high_priority_pending }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-3">Activity Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tasks Created</span>
                                <span class="font-medium">{{ dailySummary.tasks_created }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tasks Started</span>
                                <span class="font-medium">{{ dailySummary.tasks_started }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tasks Completed</span>
                                <span class="font-medium text-green-600">{{ dailySummary.tasks_completed }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Due Today</span>
                                <span class="font-medium" :class="dailySummary.due_today > 0 ? 'text-amber-600' : ''">{{ dailySummary.due_today }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="dailySummary.due_today_list.length > 0" class="bg-white rounded-lg p-4 shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <CalendarIcon class="w-5 h-5 text-amber-500" aria-hidden="true" />
                            Due Today
                        </h3>
                        <ul class="space-y-2">
                            <li v-for="task in dailySummary.due_today_list" :key="task.id" class="flex items-center justify-between">
                                <span class="text-gray-700">{{ task.title }}</span>
                                <span :class="['text-xs px-2 py-1 rounded-full', priorityColor(task.priority)]">{{ task.priority }}</span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="dailySummary.overdue_list.length > 0" class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <h3 class="font-semibold text-red-800 mb-3 flex items-center gap-2">
                            <ExclamationTriangleIcon class="w-5 h-5" aria-hidden="true" />
                            Overdue Tasks
                        </h3>
                        <ul class="space-y-2">
                            <li v-for="task in dailySummary.overdue_list" :key="task.id" class="flex items-center justify-between">
                                <span class="text-red-700">{{ task.title }}</span>
                                <span class="text-xs text-red-600">Due {{ formatDate(task.due_date) }}</span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="dailySummary.completed_tasks.length > 0" class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <h3 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5" aria-hidden="true" />
                            Completed Today
                        </h3>
                        <ul class="space-y-2">
                            <li v-for="task in dailySummary.completed_tasks" :key="task.id" class="text-green-700">✓ {{ task.title }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Weekly Summary Tab -->
            <div v-if="activeTab === 'weekly' && weeklySummary" class="space-y-4">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">{{ weeklySummary.week_formatted }}</h2>
                    <p class="text-sm text-gray-500">Weekly performance overview</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-sm text-gray-500">Tasks Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ weeklySummary.tasks_completed }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-sm text-gray-500">Completion Rate</p>
                        <p class="text-2xl font-bold text-blue-600">{{ weeklySummary.completion_rate }}%</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-sm text-gray-500">On-Time Rate</p>
                        <p class="text-2xl font-bold" :class="weeklySummary.on_time_rate >= 80 ? 'text-green-600' : 'text-amber-600'">
                            {{ weeklySummary.on_time_rate }}%
                        </p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-sm text-gray-500">Hours Logged</p>
                        <p class="text-2xl font-bold text-purple-600">{{ weeklySummary.total_hours_logged }}h</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-3">Week Overview</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tasks Created</span>
                            <span class="font-medium">{{ weeklySummary.tasks_created }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tasks Completed</span>
                            <span class="font-medium text-green-600">{{ weeklySummary.tasks_completed }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Currently In Progress</span>
                            <span class="font-medium text-blue-600">{{ weeklySummary.tasks_in_progress }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Completed On Time</span>
                            <span class="font-medium text-green-600">{{ weeklySummary.completed_on_time }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Completed Late</span>
                            <span class="font-medium" :class="weeklySummary.completed_late > 0 ? 'text-red-600' : ''">{{ weeklySummary.completed_late }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Overdue Tasks</span>
                            <span class="font-medium" :class="weeklySummary.overdue_tasks > 0 ? 'text-red-600' : ''">{{ weeklySummary.overdue_tasks }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-3">Daily Breakdown</h3>
                    <div class="space-y-2">
                        <div v-for="day in weeklySummary.daily_breakdown" :key="day.date"
                            class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <span class="text-gray-700 font-medium">{{ day.day_name }}</span>
                            <div class="flex gap-4 text-sm">
                                <span class="text-gray-500"><span class="text-blue-600 font-medium">{{ day.created }}</span> created</span>
                                <span class="text-gray-500"><span class="text-green-600 font-medium">{{ day.completed }}</span> done</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-3">By Priority</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="(data, priority) in weeklySummary.priority_breakdown" :key="priority" class="p-3 rounded-lg bg-gray-50">
                            <p class="text-sm font-medium capitalize" :class="priorityColor(priority as string)">{{ priority }}</p>
                            <div class="flex gap-3 mt-1 text-xs text-gray-500">
                                <span>{{ data.created }} created</span>
                                <span>{{ data.completed }} done</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="weeklySummary.top_performers.length > 0" class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <TrophyIcon class="w-5 h-5 text-amber-500" aria-hidden="true" />
                        Top Performers
                    </h3>
                    <div class="space-y-3">
                        <div v-for="(performer, index) in weeklySummary.top_performers" :key="performer.id" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-medium">{{ index + 1 }}</span>
                                <div>
                                    <p class="font-medium text-gray-900">{{ performer.name }}</p>
                                    <p class="text-xs text-gray-500">{{ performer.position }}</p>
                                </div>
                            </div>
                            <span class="text-green-600 font-semibold">{{ performer.tasks_completed }} tasks</span>
                        </div>
                    </div>
                </div>

                <button @click="exportWeeklySummary"
                    class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <ArrowDownTrayIcon class="w-5 h-5" aria-hidden="true" />
                    Export Weekly Summary
                </button>
            </div>

            <!-- Export Section -->
            <div class="mt-8 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Export Data</h2>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="exportTasks" class="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-left">
                        <DocumentArrowDownIcon class="w-8 h-8 text-blue-600 mb-2" aria-hidden="true" />
                        <p class="font-medium text-gray-900">Tasks</p>
                        <p class="text-xs text-gray-500">Export all tasks to CSV</p>
                    </button>
                    <button @click="exportEmployees" class="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-left">
                        <DocumentArrowDownIcon class="w-8 h-8 text-green-600 mb-2" aria-hidden="true" />
                        <p class="font-medium text-gray-900">Employees</p>
                        <p class="text-xs text-gray-500">Export team data to CSV</p>
                    </button>
                    <button @click="exportPerformance" class="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-left">
                        <DocumentArrowDownIcon class="w-8 h-8 text-purple-600 mb-2" aria-hidden="true" />
                        <p class="font-medium text-gray-900">Performance</p>
                        <p class="text-xs text-gray-500">Export performance report</p>
                    </button>
                    <button @click="exportWeeklySummary" class="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-left">
                        <DocumentArrowDownIcon class="w-8 h-8 text-amber-600 mb-2" aria-hidden="true" />
                        <p class="font-medium text-gray-900">Weekly Report</p>
                        <p class="text-xs text-gray-500">Export weekly summary</p>
                    </button>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>
