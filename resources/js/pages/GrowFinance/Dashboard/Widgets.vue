<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';

const page = usePage();
const widgets = computed(() => (page.props as any).widgets || {});

const cashPosition = computed(() => widgets.value.cash_position || { total: 0, accounts: [] });
const revenueTrend = computed(() => widgets.value.revenue_trend || []);
const expenseBreakdown = computed(() => widgets.value.expense_breakdown || []);
const arApSummary = computed(() => widgets.value.ar_ap_summary || { total_ar: 0, total_ap: 0, customer_count: 0, vendor_count: 0 });
const cashFlow = computed(() => widgets.value.cash_flow || {});
const budgetVariance = computed(() => widgets.value.budget_variance || []);

const formatCurrency = (val: number) => {
    return 'K' + (val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const trendNetClass = (net: number) => net >= 0 ? 'text-emerald-600' : 'text-red-600';
const varianceColor = (pct: number) => {
    if (pct <= 0) return 'text-emerald-600';
    if (pct <= 10) return 'text-amber-600';
    return 'text-red-600';
};
const varianceBg = (pct: number) => {
    if (pct <= 0) return 'bg-emerald-100';
    if (pct <= 10) return 'bg-amber-100';
    return 'bg-red-100';
};
</script>

<template>
    <GrowFinanceLayout>
        <div class="p-4 lg:p-6 space-y-6">
            <div class="flex items-center justify-between mb-2">
                <h1 class="text-xl font-bold text-gray-900">Dashboard Widgets</h1>
                <span class="text-sm text-gray-500">Real-time financial overview</span>
            </div>

            <!-- 2x2 Card Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cash Position Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Cash Position</h2>
                    <div class="text-3xl font-bold text-gray-900 mb-4">{{ formatCurrency(cashPosition.total) }}</div>
                    <div v-if="cashPosition.accounts.length" class="overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-400 text-xs uppercase">
                                    <th class="pb-1 font-medium">Account</th>
                                    <th class="pb-1 font-medium text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="acc in cashPosition.accounts" :key="acc.account_code" class="border-t border-gray-100">
                                    <td class="py-1.5 text-gray-700">{{ acc.account_code }} - {{ acc.account_name }}</td>
                                    <td class="py-1.5 text-right font-medium text-gray-900">{{ formatCurrency(acc.balance) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic">No cash accounts found.</div>
                </div>

                <!-- Revenue Trend Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Revenue Trend</h2>
                    <div v-if="revenueTrend.length" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-400 text-xs uppercase">
                                    <th class="pb-1 font-medium">Month</th>
                                    <th class="pb-1 font-medium text-right">Income</th>
                                    <th class="pb-1 font-medium text-right">Expenses</th>
                                    <th class="pb-1 font-medium text-right">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in revenueTrend" :key="m.month" class="border-t border-gray-100">
                                    <td class="py-1.5 text-gray-700">{{ m.month }}</td>
                                    <td class="py-1.5 text-right text-emerald-600 font-medium">{{ formatCurrency(m.income) }}</td>
                                    <td class="py-1.5 text-right text-red-600 font-medium">{{ formatCurrency(m.expenses) }}</td>
                                    <td class="py-1.5 text-right font-semibold" :class="trendNetClass(m.net)">{{ formatCurrency(m.net) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic">No revenue data for this year.</div>
                </div>

                <!-- Expense Breakdown Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Expense Breakdown</h2>
                    <div v-if="expenseBreakdown.length" class="space-y-3">
                        <div v-for="cat in expenseBreakdown.slice(0, 10)" :key="cat.account_code" class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700 truncate">{{ cat.account_name }}</span>
                                <span class="font-medium text-gray-900 ml-2">{{ formatCurrency(cat.amount) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full bg-emerald-500 transition-all" :style="{ width: Math.min(cat.percentage, 100) + '%' }"></div>
                            </div>
                            <div class="text-xs text-gray-400 text-right">{{ cat.percentage }}%</div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic">No expense data for this year.</div>
                </div>

                <!-- AR/AP Summary Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">AR / AP Summary</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="text-xs text-blue-600 font-semibold uppercase mb-1">Total AR</div>
                            <div class="text-xl font-bold text-blue-700">{{ formatCurrency(arApSummary.total_ar) }}</div>
                            <div class="text-xs text-blue-500 mt-1">{{ arApSummary.customer_count }} customers</div>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-4 text-center">
                            <div class="text-xs text-amber-600 font-semibold uppercase mb-1">Total AP</div>
                            <div class="text-xl font-bold text-amber-700">{{ formatCurrency(arApSummary.total_ap) }}</div>
                            <div class="text-xs text-amber-500 mt-1">{{ arApSummary.vendor_count }} vendors</div>
                        </div>
                    </div>
                </div>

                <!-- Cash Flow Card (spans full row but stays in 2-col grid using col-span) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:col-span-2">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Cash Flow</h2>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="text-xs text-gray-500 font-medium">Opening</div>
                            <div class="text-lg font-bold text-gray-900">{{ formatCurrency(cashFlow.opening_balance) }}</div>
                        </div>
                        <div class="text-center p-3 bg-emerald-50 rounded-lg">
                            <div class="text-xs text-emerald-600 font-medium">Inflows</div>
                            <div class="text-lg font-bold text-emerald-700">{{ formatCurrency(cashFlow.inflows) }}</div>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded-lg">
                            <div class="text-xs text-red-600 font-medium">Outflows</div>
                            <div class="text-lg font-bold text-red-700">{{ formatCurrency(cashFlow.outflows) }}</div>
                        </div>
                        <div class="text-center p-3 bg-indigo-50 rounded-lg">
                            <div class="text-xs text-indigo-600 font-medium">Net</div>
                            <div class="text-lg font-bold text-indigo-700">{{ formatCurrency(cashFlow.net_cash_flow) }}</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="text-xs text-gray-500 font-medium">Closing</div>
                            <div class="text-lg font-bold text-gray-900">{{ formatCurrency(cashFlow.closing_balance) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Budget Variance Card (spans full row) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:col-span-2">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Budget Variance</h2>
                    <div v-if="budgetVariance.length" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-400 text-xs uppercase border-b border-gray-200">
                                    <th class="pb-2 font-medium">Account</th>
                                    <th class="pb-2 font-medium text-right">Budgeted</th>
                                    <th class="pb-2 font-medium text-right">Actual</th>
                                    <th class="pb-2 font-medium text-right">Variance</th>
                                    <th class="pb-2 font-medium text-right">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="b in budgetVariance" :key="b.account_code" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 text-gray-700">{{ b.account_name }}</td>
                                    <td class="py-2 text-right text-gray-700">{{ formatCurrency(b.budgeted) }}</td>
                                    <td class="py-2 text-right text-gray-700">{{ formatCurrency(b.actual) }}</td>
                                    <td class="py-2 text-right font-medium" :class="varianceColor(b.variance_pct)">{{ formatCurrency(b.variance) }}</td>
                                    <td class="py-2 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="varianceBg(b.variance_pct) + ' ' + varianceColor(b.variance_pct)">
                                            {{ b.variance_pct >= 0 ? '+' : '' }}{{ b.variance_pct }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic">No budgets defined for this period.</div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
