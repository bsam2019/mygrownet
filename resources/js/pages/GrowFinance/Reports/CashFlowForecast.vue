<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    ArrowLeftIcon,
    ChevronDownIcon,
} from '@heroicons/vue/24/outline';

interface HistoricalMonth {
    month: string;
    operating: number;
    investing: number;
    financing: number;
    net: number;
}

interface ForecastMonth {
    month: string;
    projected_operating: number;
    projected_investing: number;
    projected_financing: number;
    projected_net: number;
    projected_cash_balance: number;
    confidence_interval_lower: number;
    confidence_interval_upper: number;
    confidence_score: number;
}

interface ForecastData {
    current_cash: number;
    historical: HistoricalMonth[];
    forecast: ForecastMonth[];
    metadata: {
        as_of: string;
        months_ahead: number;
        avg_monthly_operating: number;
        avg_monthly_investing: number;
        avg_monthly_financing: number;
        volatility_operating: number;
        volatility_investing: number;
        volatility_financing: number;
    };
}

const props = defineProps<{
    forecast: ForecastData;
    monthsAhead: number;
}>();

const selectedMonths = ref(props.monthsAhead);
const showHistorical = ref(false);

const currentUrl = computed(() => {
    const params = new URLSearchParams();
    params.set('months', String(selectedMonths.value));
    return route('growfinance.forecast.index') + '?' + params.toString();
});

const displayedForecast = computed(() => props.forecast.forecast);

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

const lastForecast = computed(() => {
    const f = props.forecast.forecast;
    return f.length > 0 ? f[f.length - 1] : null;
});
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Cash Flow Forecast" />

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
                        <h1 class="text-2xl font-bold text-gray-900">Cash Flow Forecast</h1>
                        <p class="text-sm text-gray-500">As of {{ formatMonth(forecast.metadata.as_of) }}</p>
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
                    <p class="text-emerald-100 text-sm">Current Cash Position</p>
                    <p class="text-3xl font-bold">{{ formatCurrency(forecast.current_cash) }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-4 text-white">
                    <p class="text-blue-100 text-sm">Projected Balance ({{ lastForecast ? formatMonth(lastForecast.month) : '' }})</p>
                    <p class="text-3xl font-bold">{{ lastForecast ? formatCurrency(lastForecast.projected_cash_balance) : '-' }}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-4 text-white">
                    <p class="text-purple-100 text-sm">Avg Confidence Score</p>
                    <p class="text-3xl font-bold">
                        {{ forecast.forecast.length > 0
                            ? Math.round(forecast.forecast.reduce((s, f) => s + f.confidence_score, 0) / forecast.forecast.length)
                            : 0 }}%
                    </p>
                </div>
            </div>

            <!-- Metadata Row -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                <div class="bg-white rounded-lg px-4 py-3 border border-gray-200">
                    <span class="text-gray-500">Avg Monthly Operating</span>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(forecast.metadata.avg_monthly_operating) }}</p>
                </div>
                <div class="bg-white rounded-lg px-4 py-3 border border-gray-200">
                    <span class="text-gray-500">Volatility (Operating)</span>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(forecast.metadata.volatility_operating) }}</p>
                </div>
                <div class="bg-white rounded-lg px-4 py-3 border border-gray-200">
                    <span class="text-gray-500">Avg Monthly Investing</span>
                    <p class="font-semibold text-gray-900">{{ formatCurrency(forecast.metadata.avg_monthly_investing) }}</p>
                </div>
            </div>

            <!-- Forecast Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Forecasted Cash Flows</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Operating</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Investing</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Financing</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ending Cash</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">CI Lower</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">CI Upper</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Confidence</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(f, i) in displayedForecast" :key="i" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">{{ formatMonth(f.month) }}</td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap" :class="f.projected_operating >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(f.projected_operating) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap" :class="f.projected_investing >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(f.projected_investing) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap" :class="f.projected_financing >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(f.projected_financing) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap" :class="f.projected_net >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(f.projected_net) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap" :class="f.projected_cash_balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(f.projected_cash_balance) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500 whitespace-nowrap">{{ formatCurrency(f.confidence_interval_lower) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500 whitespace-nowrap">{{ formatCurrency(f.confidence_interval_upper) }}</td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="f.confidence_score >= 70 ? 'bg-green-100 text-green-800' : f.confidence_score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'">
                                        {{ f.confidence_score }}%
                                    </span>
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
                    <h2 class="text-lg font-semibold text-gray-900">Historical Reference (Last 12 Months)</h2>
                    <ChevronDownIcon class="h-5 w-5 text-gray-400 transition-transform" :class="showHistorical ? 'rotate-180' : ''" />
                </button>
                <div v-show="showHistorical" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Operating</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Investing</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Financing</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(h, i) in forecast.historical" :key="i" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">{{ formatMonth(h.month) }}</td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap" :class="h.operating >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(h.operating) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap" :class="h.investing >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(h.investing) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap" :class="h.financing >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(h.financing) }}
                                </td>
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
