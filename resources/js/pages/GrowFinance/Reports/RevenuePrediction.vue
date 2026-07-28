<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    ArrowLeftIcon,
    ChevronDownIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    MinusIcon,
} from '@heroicons/vue/24/outline';

interface HistoricalMonth {
    month: string;
    income: number;
    expenses: number;
    net: number;
}

interface PredictionMonth {
    month: string;
    projected_income: number;
    projected_expenses: number;
    projected_net: number;
    income_range_lower: number;
    income_range_upper: number;
    expense_range_lower: number;
    expense_range_upper: number;
}

interface PredictionData {
    historical: HistoricalMonth[];
    predictions: PredictionMonth[];
    metadata: {
        as_of: string;
        months_ahead: number;
        income_trend: number;
        expense_trend: number;
        avg_income: number;
        avg_expenses: number;
        income_volatility: number;
        expense_volatility: number;
    };
}

const props = defineProps<{
    prediction: PredictionData;
    monthsAhead: number;
}>();

const selectedMonths = ref(props.monthsAhead);
const showHistorical = ref(false);

const displayedPredictions = computed(() => props.prediction.predictions);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const formatMonth = (month: string) => {
    const [y, m] = month.split('-');
    const d = new Date(parseInt(y), parseInt(m) - 1);
    return d.toLocaleDateString('en-ZM', { year: 'numeric', month: 'short' });
};

const incomeTrendIcon = computed(() => {
    const t = props.prediction.metadata.income_trend;
    if (t > 50) return ArrowTrendingUpIcon;
    if (t < -50) return ArrowTrendingDownIcon;
    return MinusIcon;
});

const incomeTrendColor = computed(() => {
    const t = props.prediction.metadata.income_trend;
    if (t > 50) return 'text-green-600';
    if (t < -50) return 'text-red-600';
    return 'text-gray-500';
});

const expenseTrendIcon = computed(() => {
    const t = props.prediction.metadata.expense_trend;
    if (t > 50) return ArrowTrendingUpIcon;
    if (t < -50) return ArrowTrendingDownIcon;
    return MinusIcon;
});

const expenseTrendColor = computed(() => {
    const t = props.prediction.metadata.expense_trend;
    if (t > 50) return 'text-red-600';
    if (t < -50) return 'text-green-600';
    return 'text-gray-500';
});

const lastPrediction = computed(() => {
    const p = props.prediction.predictions;
    return p.length > 0 ? p[p.length - 1] : null;
});
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Revenue & Expense Prediction" />

        <div class="p-4 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('growfinance.dashboard')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Revenue & Expense Prediction</h1>
                        <p class="text-sm text-gray-500">As of {{ formatMonth(prediction.metadata.as_of) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-500">Months:</label>
                    <select
                        v-model="selectedMonths"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                        <option :value="3">3 months</option>
                        <option :value="6">6 months</option>
                        <option :value="12">12 months</option>
                    </select>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white">
                    <p class="text-emerald-100 text-sm flex items-center gap-1">
                        Income Trend
                        <component :is="incomeTrendIcon" class="h-4 w-4" :class="incomeTrendColor" />
                    </p>
                    <p class="text-3xl font-bold">{{ formatCurrency(prediction.metadata.avg_income) }}/mo</p>
                    <p class="text-emerald-100 text-xs mt-1">Trend: {{ formatCurrency(prediction.metadata.income_trend) }}/mo</p>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-4 text-white">
                    <p class="text-red-100 text-sm flex items-center gap-1">
                        Expense Trend
                        <component :is="expenseTrendIcon" class="h-4 w-4" :class="expenseTrendColor" />
                    </p>
                    <p class="text-3xl font-bold">{{ formatCurrency(prediction.metadata.avg_expenses) }}/mo</p>
                    <p class="text-red-100 text-xs mt-1">Trend: {{ formatCurrency(prediction.metadata.expense_trend) }}/mo</p>
                </div>
                <div :class="[
                    'rounded-2xl p-4 text-white',
                    lastPrediction && lastPrediction.projected_net >= 0
                        ? 'bg-gradient-to-br from-blue-500 to-blue-600'
                        : 'bg-gradient-to-br from-gray-500 to-gray-600'
                ]">
                    <p class="opacity-80 text-sm">Projected Net ({{ lastPrediction ? formatMonth(lastPrediction.month) : '' }})</p>
                    <p class="text-3xl font-bold">{{ lastPrediction ? formatCurrency(lastPrediction.projected_net) : '-' }}</p>
                </div>
            </div>

            <!-- Volatility Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div class="bg-white rounded-lg px-4 py-3 border border-gray-200">
                    <span class="text-gray-500">Avg Monthly Income</span>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(prediction.metadata.avg_income) }}</p>
                </div>
                <div class="bg-white rounded-lg px-4 py-3 border border-gray-200">
                    <span class="text-gray-500">Avg Monthly Expenses</span>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(prediction.metadata.avg_expenses) }}</p>
                </div>
                <div class="bg-white rounded-lg px-4 py-3 border border-gray-200">
                    <span class="text-gray-500">Income Volatility</span>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(prediction.metadata.income_volatility) }}</p>
                </div>
                <div class="bg-white rounded-lg px-4 py-3 border border-gray-200">
                    <span class="text-gray-500">Expense Volatility</span>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(prediction.metadata.expense_volatility) }}</p>
                </div>
            </div>

            <!-- Prediction Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Predicted P&L</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Projected Income</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Income Range</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Projected Expenses</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Expense Range</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Projected Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(p, i) in displayedPredictions" :key="i" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">{{ formatMonth(p.month) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600 font-medium whitespace-nowrap">
                                    {{ formatCurrency(p.projected_income) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500 whitespace-nowrap">
                                    {{ formatCurrency(p.income_range_lower) }} – {{ formatCurrency(p.income_range_upper) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-red-600 font-medium whitespace-nowrap">
                                    {{ formatCurrency(p.projected_expenses) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500 whitespace-nowrap">
                                    {{ formatCurrency(p.expense_range_lower) }} – {{ formatCurrency(p.expense_range_upper) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap" :class="p.projected_net >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(p.projected_net) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Historical Reference -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button
                    @click="showHistorical = !showHistorical"
                    class="w-full px-6 py-4 flex items-center justify-between border-b border-gray-200 hover:bg-gray-50 transition-colors"
                >
                    <h2 class="text-lg font-semibold text-gray-900">Historical P&L (Last 12 Months)</h2>
                    <ChevronDownIcon class="h-5 w-5 text-gray-400 transition-transform" :class="showHistorical ? 'rotate-180' : ''" />
                </button>
                <div v-show="showHistorical" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Income</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Expenses</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(h, i) in prediction.historical" :key="i" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">{{ formatMonth(h.month) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600 whitespace-nowrap">{{ formatCurrency(h.income) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-red-600 whitespace-nowrap">{{ formatCurrency(h.expenses) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap" :class="h.net >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(h.net) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
