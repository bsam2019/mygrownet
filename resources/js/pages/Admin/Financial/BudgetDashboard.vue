<template>
    <AdminLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Budget Management</h1>
                            <p class="mt-2 text-sm text-gray-600">
                                Compare budgeted amounts vs actual spending from transactions
                            </p>
                        </div>
                        
                        <!-- Period Selector -->
                        <div class="flex items-center gap-4">
                            <select
                                v-model="selectedPeriod"
                                @change="handlePeriodChange"
                                class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                                <option value="custom">Custom Period...</option>
                            </select>
                            
                            <!-- Custom Period Inputs -->
                            <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-2">
                                <input
                                    v-model="customStartDate"
                                    type="date"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <span class="text-gray-500">to</span>
                                <input
                                    v-model="customEndDate"
                                    type="date"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <button
                                    @click="applyCustomPeriod"
                                    class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                                >
                                    Apply
                                </button>
                            </div>
                            
                            <button
                                @click="exportPDF"
                                :disabled="loading || !comparison.has_budget"
                                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 flex items-center gap-2"
                            >
                                <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                                Export PDF
                            </button>
                            
                            <button
                                @click="loadData"
                                :disabled="loading"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
                            >
                                <ArrowPathIcon class="h-5 w-5" :class="{ 'animate-spin': loading }" aria-hidden="true" />
                                Refresh
                            </button>
                            
                            <!-- Link to CMS Budget Management -->
                            <a
                                :href="route('cms.budgets.index')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2"
                            >
                                <PlusCircleIcon class="h-5 w-5" aria-hidden="true" />
                                Manage Budgets
                            </a>
                        </div>
                    </div>
                </div>

                <!-- No Budget State -->
                <div v-if="!comparison.has_budget" class="bg-white rounded-lg shadow p-12 text-center">
                    <ChartBarIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" aria-hidden="true" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Budget</h3>
                    <p class="text-gray-600 mb-6">{{ comparison.message }}</p>
                    <a
                        :href="route('cms.budgets.index')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <PlusCircleIcon class="h-5 w-5" aria-hidden="true" />
                        Create Budget in CMS
                    </a>
                </div>

                <!-- Budget Dashboard -->
                <div v-else class="space-y-6">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <MetricCard
                            title="Total Budgeted"
                            :value="formatCurrency(comparison.summary.total_budgeted)"
                            icon="currency-dollar"
                            color="blue"
                        />
                        <MetricCard
                            title="Total Actual"
                            :value="formatCurrency(comparison.summary.total_actual)"
                            icon="chart-bar"
                            :color="comparison.summary.total_variance >= 0 ? 'red' : 'green'"
                        />
                        <MetricCard
                            title="Variance"
                            :value="formatCurrency(Math.abs(comparison.summary.total_variance))"
                            :subtitle="comparison.summary.total_variance >= 0 ? 'Over Budget' : 'Under Budget'"
                            icon="arrow-trending-up"
                            :color="comparison.summary.total_variance >= 0 ? 'red' : 'green'"
                        />
                        <MetricCard
                            title="Budget Used"
                            :value="`${comparison.summary.percentage_used}%`"
                            icon="chart-pie"
                            :color="getPercentageColor(comparison.summary.percentage_used)"
                        />
                    </div>

                    <!-- Performance Metrics -->
                    <div v-if="metrics.has_budget" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Over Budget</p>
                                    <p class="text-2xl font-bold text-red-600">{{ metrics.metrics.over_budget_count }}</p>
                                </div>
                                <ExclamationTriangleIcon class="h-8 w-8 text-red-500" aria-hidden="true" />
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">On Track</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ metrics.metrics.on_track_count }}</p>
                                </div>
                                <CheckCircleIcon class="h-8 w-8 text-blue-500" aria-hidden="true" />
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Under Budget</p>
                                    <p class="text-2xl font-bold text-green-600">{{ metrics.metrics.under_budget_count }}</p>
                                </div>
                                <ArrowTrendingDownIcon class="h-8 w-8 text-green-500" aria-hidden="true" />
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Unbudgeted</p>
                                    <p class="text-2xl font-bold text-amber-600">{{ metrics.metrics.unbudgeted_expense_count }}</p>
                                </div>
                                <QuestionMarkCircleIcon class="h-8 w-8 text-amber-500" aria-hidden="true" />
                            </div>
                        </div>
                    </div>

                    <!-- Critical Overages Alert -->
                    <div v-if="metrics.has_budget && metrics.metrics.critical_overages.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <div class="flex items-start gap-3">
                            <ExclamationTriangleIcon class="h-6 w-6 text-red-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-red-900 mb-2">Critical Budget Overages</h3>
                                <p class="text-sm text-red-700 mb-4">The following categories are significantly over budget (>120%):</p>
                                <div class="space-y-2">
                                    <div v-for="overage in metrics.metrics.critical_overages" :key="overage.category" class="flex items-center justify-between bg-white rounded p-3">
                                        <span class="font-medium text-gray-900">{{ overage.category }}</span>
                                        <div class="text-right">
                                            <span class="text-red-600 font-bold">{{ overage.percentage_used }}%</span>
                                            <span class="text-sm text-gray-600 ml-2">({{ formatCurrency(overage.variance) }} over)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Comparison Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Budget vs Actual Breakdown</h3>
                            <span class="text-sm text-gray-600">
                                {{ comparison.dates.start }} to {{ comparison.dates.end }}
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budgeted</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in comparison.comparisons" :key="item.category" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ item.category }}</div>
                                            <div v-if="item.notes" class="text-xs text-gray-500">{{ item.notes }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 py-1 text-xs font-medium rounded-full',
                                                item.item_type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                                            ]">
                                                {{ item.item_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                            {{ formatCurrency(item.budgeted) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" :class="getActualColor(item)">
                                            {{ formatCurrency(item.actual) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" :class="getVarianceColor(item)">
                                            {{ formatCurrency(Math.abs(item.variance)) }}
                                            <span class="text-xs">{{ item.variance >= 0 ? '↑' : '↓' }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center justify-center">
                                                <div class="w-full max-w-xs">
                                                    <div class="flex items-center justify-between text-xs mb-1">
                                                        <span :class="getPercentageTextColor(item.percentage_used)">
                                                            {{ item.percentage_used }}%
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div
                                                            class="h-2 rounded-full transition-all"
                                                            :class="getProgressBarColor(item.percentage_used)"
                                                            :style="{ width: `${Math.min(item.percentage_used, 100)}%` }"
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span :class="getStatusBadgeClass(item.status)">
                                                {{ getStatusLabel(item.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Unbudgeted Expenses -->
                    <div v-if="comparison.unbudgeted_expenses.length > 0" class="bg-amber-50 border border-amber-200 rounded-lg p-6">
                        <div class="flex items-start gap-3">
                            <QuestionMarkCircleIcon class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-amber-900 mb-2">Unbudgeted Expenses</h3>
                                <p class="text-sm text-amber-700 mb-4">The following expenses were not included in the budget:</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div v-for="expense in comparison.unbudgeted_expenses" :key="expense.category" class="bg-white rounded p-3 flex items-center justify-between">
                                        <span class="font-medium text-gray-900">{{ expense.category }}</span>
                                        <span class="text-amber-600 font-bold">{{ formatCurrency(expense.amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import MetricCard from '@/Components/Admin/MetricCard.vue';
import {
    ArrowPathIcon,
    PlusCircleIcon,
    ChartBarIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ArrowTrendingDownIcon,
    QuestionMarkCircleIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    comparison: any;
    metrics: any;
    selectedPeriod: string;
}

const props = defineProps<Props>();

const selectedPeriod = ref(props.selectedPeriod);
const customStartDate = ref('');
const customEndDate = ref('');
const loading = ref(false);
const comparison = ref(props.comparison);
const metrics = ref(props.metrics);

const loadData = async () => {
    loading.value = true;
    try {
        let url = route('admin.budget.comparison', { period: selectedPeriod.value });
        
        // Add custom dates if custom period is selected
        if (selectedPeriod.value === 'custom' && customStartDate.value && customEndDate.value) {
            url += `&start_date=${customStartDate.value}&end_date=${customEndDate.value}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        comparison.value = data.comparison;
        metrics.value = data.metrics;
    } catch (error) {
        console.error('Failed to load budget data:', error);
    } finally {
        loading.value = false;
    }
};

const handlePeriodChange = () => {
    if (selectedPeriod.value !== 'custom') {
        loadData();
    }
};

const applyCustomPeriod = () => {
    if (customStartDate.value && customEndDate.value) {
        loadData();
    } else {
        alert('Please select both start and end dates');
    }
};

const exportPDF = () => {
    loading.value = true;
    
    let url = route('admin.budget.export-pdf', { period: selectedPeriod.value });
    
    if (selectedPeriod.value === 'custom' && customStartDate.value && customEndDate.value) {
        url += `&start_date=${customStartDate.value}&end_date=${customEndDate.value}`;
    }
    
    // Open PDF in new window for download
    window.open(url, '_blank');
    
    loading.value = false;
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const getPercentageColor = (percentage: number): string => {
    if (percentage >= 100) return 'red';
    if (percentage >= 90) return 'amber';
    return 'green';
};

const getPercentageTextColor = (percentage: number): string => {
    if (percentage >= 100) return 'text-red-600 font-bold';
    if (percentage >= 90) return 'text-amber-600 font-semibold';
    return 'text-green-600';
};

const getProgressBarColor = (percentage: number): string => {
    if (percentage >= 100) return 'bg-red-500';
    if (percentage >= 90) return 'bg-amber-500';
    return 'bg-green-500';
};

const getActualColor = (item: any): string => {
    if (item.item_type === 'expense') {
        return item.status === 'over_budget' ? 'text-red-600' : 'text-gray-900';
    } else {
        return item.status === 'under_budget' ? 'text-red-600' : 'text-green-600';
    }
};

const getVarianceColor = (item: any): string => {
    if (item.item_type === 'expense') {
        return item.variance >= 0 ? 'text-red-600' : 'text-green-600';
    } else {
        return item.variance >= 0 ? 'text-green-600' : 'text-red-600';
    }
};

const getStatusBadgeClass = (status: string): string => {
    const baseClasses = 'px-2 py-1 text-xs font-medium rounded-full';
    switch (status) {
        case 'over_budget':
            return `${baseClasses} bg-red-100 text-red-800`;
        case 'on_track':
            return `${baseClasses} bg-blue-100 text-blue-800`;
        case 'under_budget':
            return `${baseClasses} bg-green-100 text-green-800`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
};

const getStatusLabel = (status: string): string => {
    switch (status) {
        case 'over_budget':
            return 'Over Budget';
        case 'on_track':
            return 'On Track';
        case 'under_budget':
            return 'Under Budget';
        default:
            return status;
    }
};

onMounted(() => {
    // Data is already loaded from props
});
</script>
