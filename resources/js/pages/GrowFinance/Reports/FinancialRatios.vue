<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Financial Ratios &amp; Trends</h1>
                <p class="text-gray-500 text-sm">{{ filters.from }} to {{ filters.to }}</p>
            </div>

            <!-- Date Range -->
            <div class="flex gap-3 mb-6">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input type="date" v-model="fromDate"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        @change="refresh" />
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input type="date" v-model="toDate"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        @change="refresh" />
                </div>
            </div>

            <!-- Ratio Cards -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div v-for="(value, key) in ratioCards" :key="key" class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-xs text-gray-500 mb-1">{{ keyLabels[key] || key }}</p>
                    <p class="text-2xl font-bold" :class="getColorClass(value)">{{ formatRatio(key, value) }}</p>
                </div>
            </div>

            <!-- Trend Chart (Monthly Table) -->
            <div class="bg-white rounded-2xl shadow-sm p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Monthly Trend ({{ trendMonths }} months)</h3>
                    <div class="flex gap-2">
                        <button @click="trendMonths = 6; loadTrend()"
                            class="px-3 py-1 text-xs rounded-lg font-medium"
                            :class="trendMonths === 6 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        >6M</button>
                        <button @click="trendMonths = 12; loadTrend()"
                            class="px-3 py-1 text-xs rounded-lg font-medium"
                            :class="trendMonths === 12 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        >12M</button>
                    </div>
                </div>

                <div v-if="trendData.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-left text-gray-500 text-xs uppercase">
                                <th class="py-2 pr-4">Month</th>
                                <th class="py-2 pr-4 text-right">Income</th>
                                <th class="py-2 pr-4 text-right">Expenses</th>
                                <th class="py-2 pr-4 text-right">Net Income</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in trendData" :key="row.month" class="border-b border-gray-50">
                                <td class="py-2 pr-4 font-medium">{{ row.month }}</td>
                                <td class="py-2 pr-4 text-right text-emerald-600">{{ formatMoney(row.total_income) }}</td>
                                <td class="py-2 pr-4 text-right text-red-600">{{ formatMoney(row.total_expenses) }}</td>
                                <td class="py-2 pr-4 text-right font-semibold" :class="row.net_income >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ formatMoney(row.net_income) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center text-gray-400 text-sm py-4">
                    Loading trend data...
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';

interface RatioData {
    current_ratio: number;
    quick_ratio: number;
    return_on_equity: number;
    return_on_assets: number;
    profit_margin: number;
    debt_to_equity: number;
    period: {
        from: string;
        to: string;
        as_of: string;
    };
}

interface TrendRow {
    month: string;
    total_income: number;
    total_expenses: number;
    net_income: number;
}

interface Props {
    ratios: RatioData;
    filters: {
        from: string;
        to: string;
        as_of: string;
    };
}

const props = defineProps<Props>();

const fromDate = ref(props.filters.from);
const toDate = ref(props.filters.to);
const trendMonths = ref(12);
const trendData = ref<TrendRow[]>([]);

const keyLabels: Record<string, string> = {
    current_ratio: 'Current Ratio',
    quick_ratio: 'Quick Ratio',
    return_on_equity: 'Return on Equity',
    return_on_assets: 'Return on Assets',
    profit_margin: 'Profit Margin',
    debt_to_equity: 'Debt to Equity',
};

const ratioCards = computed(() => {
    const { period, ...ratios } = props.ratios;
    return ratios;
});

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatRatio = (key: string, value: number): string => {
    if (key === 'profit_margin' || key === 'return_on_equity' || key === 'return_on_assets') {
        return value.toFixed(2) + '%';
    }
    return value.toFixed(2);
};

const getColorClass = (value: number): string => {
    if (value <= 0) return 'text-red-600';
    if (value < 1) return 'text-amber-600';
    return 'text-emerald-600';
};

const refresh = () => {
    router.get(route('growfinance.ratios.index'), {
        from: fromDate.value,
        to: toDate.value,
    }, { preserveState: true, preserveScroll: true });
};

const loadTrend = () => {
    router.get(route('growfinance.ratios.trend'), {
        months: trendMonths.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page: any) => {
            trendData.value = page.props.trend || [];
        },
    });
};

onMounted(() => {
    loadTrend();
});
</script>
