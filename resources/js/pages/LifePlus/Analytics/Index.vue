<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import {
    ArrowLeftIcon,
    ArrowDownTrayIcon,
    ChartBarIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

defineOptions({ layout: LifePlusLayout });

interface Analytics {
    expenses: {
        total: number;
        count: number;
        average_per_day: number;
        daily_trend: Record<string, number>;
        by_category: Record<string, { total: number; count: number }>;
    };
    tasks: {
        total: number;
        completed: number;
        pending: number;
        overdue: number;
        completion_rate: number;
    };
    habits: {
        total_habits: number;
        total_completions: number;
        overall_completion_rate: number;
        best_streak: number;
    };
    summary: {
        period_start: string;
        period_end: string;
        days_active: number;
    };
}

const props = defineProps<{
    analytics: Analytics;
    period: string;
}>();

const selectedPeriod = ref(props.period);

const changePeriod = (period: string) => {
    selectedPeriod.value = period;
    router.get(route('lifeplus.analytics.index'), { period }, { preserveState: true });
};

// Expense trend chart data
const expenseTrendData = computed(() => {
    const labels = Object.keys(props.analytics.expenses.daily_trend).slice(-14);
    const data = labels.map(d => props.analytics.expenses.daily_trend[d] || 0);

    return {
        labels: labels.map(d => new Date(d).toLocaleDateString('en', { month: 'short', day: 'numeric' })),
        datasets: [{
            label: 'Daily Spending',
            data,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4,
        }],
    };
});

// Task completion chart
const taskChartData = computed(() => ({
    labels: ['Completed', 'Pending', 'Overdue'],
    datasets: [{
        data: [
            props.analytics.tasks.completed,
            props.analytics.tasks.pending - props.analytics.tasks.overdue,
            props.analytics.tasks.overdue,
        ],
        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
};

const exportData = (type: string) => {
    router.post(route(`lifeplus.export.${type}`), {}, {
        onSuccess: () => {
            // Handle download
        },
    });
};
</script>

<template>
    <div class="p-4 space-y-6 pb-24">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.home')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to home"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1">Analytics</h1>
        </div>

        <!-- Period Selector -->
        <div class="flex gap-2 overflow-x-auto pb-2">
            <button 
                v-for="p in ['week', 'month', 'quarter', 'year']" 
                :key="p"
                @click="changePeriod(p)"
                :class="[
                    'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                    selectedPeriod === p 
                        ? 'bg-emerald-500 text-white' 
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
            >
                {{ p.charAt(0).toUpperCase() + p.slice(1) }}
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-emerald-50 rounded-2xl p-4">
                <p class="text-sm text-emerald-600">Total Spent</p>
                <p class="text-2xl font-bold text-emerald-700">K {{ analytics.expenses.total.toLocaleString() }}</p>
            </div>
            <div class="bg-blue-50 rounded-2xl p-4">
                <p class="text-sm text-blue-600">Tasks Done</p>
                <p class="text-2xl font-bold text-blue-700">{{ analytics.tasks.completed }}</p>
            </div>
            <div class="bg-purple-50 rounded-2xl p-4">
                <p class="text-sm text-purple-600">Habit Rate</p>
                <p class="text-2xl font-bold text-purple-700">{{ analytics.habits.overall_completion_rate }}%</p>
            </div>
            <div class="bg-amber-50 rounded-2xl p-4">
                <p class="text-sm text-amber-600">Days Active</p>
                <p class="text-2xl font-bold text-amber-700">{{ analytics.summary.days_active }}</p>
            </div>
        </div>

        <!-- Expense Trend Chart -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4">Spending Trend</h2>
            <div class="h-48">
                <Line :data="expenseTrendData" :options="chartOptions" />
            </div>
        </div>

        <!-- Task Status Chart -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4">Task Status</h2>
            <div class="flex items-center gap-6">
                <div class="w-32 h-32">
                    <Doughnut :data="taskChartData" :options="chartOptions" />
                </div>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-sm text-gray-600">Completed: {{ analytics.tasks.completed }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="text-sm text-gray-600">Pending: {{ analytics.tasks.pending }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="text-sm text-gray-600">Overdue: {{ analytics.tasks.overdue }}</span>
                    </div>
                </div>
            </div>
            <p class="text-center text-sm text-gray-500 mt-3">
                {{ analytics.tasks.completion_rate }}% completion rate
            </p>
        </div>

        <!-- Habit Stats -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4">Habit Performance</h2>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-purple-600">{{ analytics.habits.total_habits }}</p>
                    <p class="text-xs text-gray-500">Active Habits</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600">{{ analytics.habits.total_completions }}</p>
                    <p class="text-xs text-gray-500">Completions</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-600">{{ analytics.habits.best_streak }}</p>
                    <p class="text-xs text-gray-500">Best Streak</p>
                </div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="bg-gray-50 rounded-2xl p-4">
            <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                Export Data
            </h2>
            <div class="grid grid-cols-2 gap-2">
                <button 
                    @click="exportData('expenses')"
                    class="py-2 px-4 bg-white rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                >
                    📊 Expenses CSV
                </button>
                <button 
                    @click="exportData('tasks')"
                    class="py-2 px-4 bg-white rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                >
                    ✅ Tasks CSV
                </button>
                <button 
                    @click="exportData('notes')"
                    class="py-2 px-4 bg-white rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                >
                    📝 Notes TXT
                </button>
                <button 
                    @click="exportData('all')"
                    class="py-2 px-4 bg-emerald-500 text-white rounded-xl text-sm font-medium hover:bg-emerald-600 transition-colors"
                >
                    📦 Export All
                </button>
            </div>
        </div>
    </div>
</template>
