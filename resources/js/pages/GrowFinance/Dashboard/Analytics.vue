<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import {
    ChartBarIcon,
    ExclamationCircleIcon,
    ArrowTrendingUpIcon,
    BanknotesIcon,
    LightBulbIcon,
    ArrowPathIcon,
    CurrencyDollarIcon,
    MagnifyingGlassIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const props = computed(() => page.props as any);

const widgets = computed(() => props.value.widgets || {});
const ratios = computed(() => props.value.ratios || {});
const anomalies = computed(() => props.value.anomalies || {});
const forecast = computed(() => props.value.forecast || {});
const predictions = computed(() => props.value.predictions || {});
const recommendations = computed(() => props.value.recommendations || {});

const cashPosition = computed(() => widgets.value.cash_position || { total: 0 });
const revenueTrend = computed(() => widgets.value.revenue_trend || []);
const arApSummary = computed(() => widgets.value.ar_ap_summary || { total_ar: 0, total_ap: 0 });

const formatCurrency = (val: number) => {
    return 'K' + (val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const netIncome = computed(() => {
    const trend = revenueTrend.value;
    if (trend.length === 0) return 0;
    return trend.reduce((sum: number, m: any) => sum + (m.net || 0), 0);
});

const totalRevenue = computed(() => {
    const trend = revenueTrend.value;
    if (trend.length === 0) return 0;
    return trend.reduce((sum: number, m: any) => sum + (m.income || 0), 0);
});

const arApRatio = computed(() => {
    const ar = arApSummary.value.total_ar || 0;
    const ap = arApSummary.value.total_ap || 0;
    return ap > 0 ? (ar / ap).toFixed(2) : 'N/A';
});

const ratioCards = computed(() => [
    { key: 'current_ratio', label: 'Current Ratio', value: ratios.value.current_ratio, color: (ratios.value.current_ratio ?? 2) >= 1.5 ? 'text-emerald-600' : 'text-amber-600' },
    { key: 'quick_ratio', label: 'Quick Ratio', value: ratios.value.quick_ratio, color: (ratios.value.quick_ratio ?? 1.5) >= 1 ? 'text-emerald-600' : 'text-amber-600' },
    { key: 'return_on_equity', label: 'ROE', value: ratios.value.return_on_equity, suffix: '%', color: (ratios.value.return_on_equity ?? 0) > 0 ? 'text-emerald-600' : 'text-red-600' },
    { key: 'profit_margin', label: 'Profit Margin', value: ratios.value.profit_margin, suffix: '%', color: (ratios.value.profit_margin ?? 0) > 0 ? 'text-emerald-600' : 'text-red-600' },
    { key: 'debt_to_equity', label: 'D/E Ratio', value: ratios.value.debt_to_equity, color: (ratios.value.debt_to_equity ?? 0) <= 2 ? 'text-emerald-600' : 'text-amber-600' },
]);

const formatRatio = (val: number | undefined, suffix?: string) => {
    if (val === undefined || val === null) return '—';
    return (val as number).toFixed(2) + (suffix || '');
};

const forecastMonths = computed(() => {
    return (forecast.value.forecast || []).slice(0, 3);
});

const predictionMonths = computed(() => {
    return (predictions.value.predictions || []).slice(0, 6);
});

const anomalyTotal = computed(() => anomalies.value.total ?? 0);
const anomalyHigh = computed(() => anomalies.value.high ?? 0);
const anomalyMedium = computed(() => anomalies.value.medium ?? 0);
const anomalyLow = computed(() => anomalies.value.low ?? 0);

const allRecommendations = computed(() => {
    const r = recommendations.value;
    const items: any[] = [];
    (r.alerts || []).forEach((a: any) => items.push(a));
    (r.cash_flow_warnings || []).forEach((w: any) => items.push(w));
    (r.budget_alerts || []).forEach((b: any) => items.push(b));
    (r.anomaly_notifications || []).forEach((n: any) => items.push(n));
    (r.suggestions || []).forEach((s: any) => items.push(s));
    return items.slice(0, 6);
});

const severityIcon = (severity: string) => {
    if (severity === 'critical') return '🔴';
    if (severity === 'warning' || severity === 'high') return '🟡';
    return '🔵';
};
</script>

<template>
    <GrowFinanceLayout>
        <div class="p-4 lg:p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Financial Analytics Dashboard</h1>
                    <p class="text-sm text-gray-500">Comprehensive financial intelligence at a glance</p>
                </div>
            </div>

            <!-- KPI Cards Row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 mb-1">Net Income (YTD)</p>
                    <p class="text-2xl font-bold" :class="netIncome >= 0 ? 'text-emerald-600' : 'text-red-600'">
                        {{ formatCurrency(netIncome) }}
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 mb-1">Total Revenue (YTD)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalRevenue) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 mb-1">Current Cash</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ formatCurrency(cashPosition.total) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 mb-1">AR / AP Ratio</p>
                    <p class="text-2xl font-bold text-gray-900">{{ arApRatio }}</p>
                </div>
            </div>

            <!-- Financial Ratios Row -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Financial Ratios</h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <div v-for="r in ratioCards" :key="r.key" class="text-center p-2">
                        <p class="text-xs text-gray-500">{{ r.label }}</p>
                        <p class="text-lg font-bold" :class="r.color">{{ formatRatio(r.value, r.suffix) }}</p>
                    </div>
                </div>
            </div>

            <!-- Forecast + Predictions Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Cash Flow Forecast Mini-Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Cash Flow Forecast (3 months)</h2>
                    <div v-if="forecastMonths.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-400 text-xs uppercase">
                                    <th class="pb-2 font-medium">Month</th>
                                    <th class="pb-2 font-medium text-right">Projected</th>
                                    <th class="pb-2 font-medium text-right">Lower</th>
                                    <th class="pb-2 font-medium text-right">Upper</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in forecastMonths" :key="m.month" class="border-t border-gray-100">
                                    <td class="py-2 text-gray-700">{{ m.month }}</td>
                                    <td class="py-2 text-right font-medium" :class="m.projected_cash_balance >= 0 ? 'text-gray-900' : 'text-red-600'">
                                        {{ formatCurrency(m.projected_cash_balance) }}
                                    </td>
                                    <td class="py-2 text-right text-gray-500">{{ formatCurrency(m.confidence_interval_lower) }}</td>
                                    <td class="py-2 text-right text-gray-500">{{ formatCurrency(m.confidence_interval_upper) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-sm text-gray-400 py-4 text-center">No forecast data available</p>
                </div>

                <!-- Revenue Prediction Mini-Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Revenue Prediction (6 months)</h2>
                    <div v-if="predictionMonths.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-400 text-xs uppercase">
                                    <th class="pb-2 font-medium">Month</th>
                                    <th class="pb-2 font-medium text-right">Income</th>
                                    <th class="pb-2 font-medium text-right">Expenses</th>
                                    <th class="pb-2 font-medium text-right">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in predictionMonths" :key="m.month" class="border-t border-gray-100">
                                    <td class="py-2 text-gray-700">{{ m.month }}</td>
                                    <td class="py-2 text-right text-gray-900">{{ formatCurrency(m.projected_income) }}</td>
                                    <td class="py-2 text-right text-red-600">{{ formatCurrency(m.projected_expenses) }}</td>
                                    <td class="py-2 text-right font-medium" :class="m.projected_net >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                        {{ formatCurrency(m.projected_net) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-sm text-gray-400 py-4 text-center">No prediction data available</p>
                </div>
            </div>

            <!-- Anomaly Summary + Recommendations Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Anomaly Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Anomaly Summary (30 days)</h2>
                        <ExclamationCircleIcon class="h-5 w-5 text-amber-500" />
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-center">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ anomalyTotal }}</p>
                            <p class="text-xs text-gray-500">Total</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-red-600">{{ anomalyHigh }}</p>
                            <p class="text-xs text-gray-500">High</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-amber-600">{{ anomalyMedium }}</p>
                            <p class="text-xs text-gray-500">Medium</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-600">{{ anomalyLow }}</p>
                            <p class="text-xs text-gray-500">Low</p>
                        </div>
                    </div>
                </div>

                <!-- Recommendations Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Recommendations</h2>
                        <LightBulbIcon class="h-5 w-5 text-amber-500" />
                    </div>
                    <div v-if="allRecommendations.length > 0" class="space-y-2">
                        <div v-for="(rec, i) in allRecommendations" :key="i"
                            class="flex items-start gap-2 text-sm p-2 rounded-lg"
                            :class="rec.severity === 'critical' ? 'bg-red-50' : rec.severity === 'warning' || rec.severity === 'high' ? 'bg-amber-50' : 'bg-blue-50'"
                        >
                            <span class="text-base flex-shrink-0 mt-0.5">{{ severityIcon(rec.severity) }}</span>
                            <div>
                                <p class="font-medium text-gray-900">{{ rec.title }}</p>
                                <p class="text-gray-600 text-xs">{{ rec.message }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 py-4 text-center">No recommendations available</p>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Quick Actions</h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <Link :href="route('growfinance.forecast.index')"
                        class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 transition-colors text-center">
                        <ChartBarIcon class="h-6 w-6 text-emerald-600" />
                        <span class="text-xs font-medium text-gray-700">Cash Flow Forecast</span>
                    </Link>
                    <Link :href="route('growfinance.predictions.index')"
                        class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors text-center">
                        <ArrowTrendingUpIcon class="h-6 w-6 text-blue-600" />
                        <span class="text-xs font-medium text-gray-700">Revenue Prediction</span>
                    </Link>
                    <Link :href="route('growfinance.anomalies.index')"
                        class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors text-center">
                        <ExclamationCircleIcon class="h-6 w-6 text-amber-600" />
                        <span class="text-xs font-medium text-gray-700">Anomaly Detection</span>
                    </Link>
                    <Link :href="route('growfinance.analytics.index')"
                        class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors text-center">
                        <SparklesIcon class="h-6 w-6 text-purple-600" />
                        <span class="text-xs font-medium text-gray-700">What-If Scenarios</span>
                    </Link>
                    <Link :href="route('growfinance.recommendations.index')"
                        class="flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors text-center">
                        <LightBulbIcon class="h-6 w-6 text-indigo-600" />
                        <span class="text-xs font-medium text-gray-700">Recommendations</span>
                    </Link>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
