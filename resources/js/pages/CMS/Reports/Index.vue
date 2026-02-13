<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ChartBarIcon, BanknotesIcon, DocumentTextIcon, BriefcaseIcon, CalculatorIcon, BookOpenIcon, ReceiptPercentIcon } from '@heroicons/vue/24/outline';
import { ArrowDownTrayIcon, ScaleIcon, ArrowTrendingUpIcon } from '@heroicons/vue/24/outline';
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue';

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
    salesSummary: any;
    paymentSummary: any;
    outstandingInvoices: any;
    jobProfitability: any;
    profitLoss: any;
    cashbook: any;
    expenseSummary: any;
    taxReport: any;
    comparative: any;
    filters: {
        start_date: string;
        end_date: string;
    };
}

const props = defineProps<Props>();

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilters = () => {
    router.get('/cms/reports', {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
    });
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const setDatePreset = (preset: string) => {
    const today = new Date();
    let start: Date, end: Date;

    switch (preset) {
        case 'this_week':
            start = new Date(today.setDate(today.getDate() - today.getDay()));
            end = new Date();
            break;
        case 'this_month':
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date();
            break;
        case 'this_quarter':
            const quarter = Math.floor(today.getMonth() / 3);
            start = new Date(today.getFullYear(), quarter * 3, 1);
            end = new Date();
            break;
        case 'this_year':
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date();
            break;
        case 'last_month':
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        default:
            return;
    }

    startDate.value = start.toISOString().split('T')[0];
    endDate.value = end.toISOString().split('T')[0];
    applyFilters();
};

const exportReport = (reportType: string) => {
    const url = route('cms.reports.export', {
        report_type: reportType,
        format: 'csv',
        start_date: startDate.value,
        end_date: endDate.value,
    });
    window.location.href = url;
};
</script>

<template>
    <Head title="Financial Reports - CMS" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Financial Reports</h1>
            <p class="mt-1 text-sm text-gray-600">View financial performance and analytics</p>
        </div>

        <!-- Date Filter -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <div class="flex flex-col gap-4">
                <!-- Date Presets -->
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="setDatePreset('this_week')"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
                    >
                        This Week
                    </button>
                    <button
                        @click="setDatePreset('this_month')"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
                    >
                        This Month
                    </button>
                    <button
                        @click="setDatePreset('last_month')"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
                    >
                        Last Month
                    </button>
                    <button
                        @click="setDatePreset('this_quarter')"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
                    >
                        This Quarter
                    </button>
                    <button
                        @click="setDatePreset('this_year')"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
                    >
                        This Year
                    </button>
                </div>

                <!-- Custom Date Range -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input
                            v-model="startDate"
                            type="date"
                            class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        />
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input
                            v-model="endDate"
                            type="date"
                            class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        />
                    </div>
                    <button
                        @click="applyFilters"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition"
                    >
                        Apply
                    </button>
                </div>
            </div>
        </div>

        <!-- Sales Summary -->
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2">
                <ChartBarIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                <h2 class="text-lg font-semibold text-gray-900">Sales Summary</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Total Invoices</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ salesSummary.total_invoices }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Total Value</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(salesSummary.total_value) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Total Paid</div>
                    <div class="mt-1 text-2xl font-bold text-green-600">{{ formatCurrency(salesSummary.total_paid) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Outstanding</div>
                    <div class="mt-1 text-2xl font-bold text-amber-600">{{ formatCurrency(salesSummary.total_outstanding) }}</div>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2">
                <BanknotesIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                <h2 class="text-lg font-semibold text-gray-900">Payment Summary</h2>
            </div>
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <div class="text-sm font-medium text-gray-600">Total Payments</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ paymentSummary.total_payments }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-600">Total Amount</div>
                        <div class="mt-1 text-2xl font-bold text-green-600">{{ formatCurrency(paymentSummary.total_amount) }}</div>
                    </div>
                </div>
                <div class="border-t pt-4">
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">By Payment Method</h3>
                    <div class="space-y-2">
                        <div v-for="(data, method) in paymentSummary.by_method" :key="method" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(data.total) }} ({{ data.count }} payments)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Profitability -->
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2">
                <BriefcaseIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                <h2 class="text-lg font-semibold text-gray-900">Job Profitability</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Completed Jobs</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ jobProfitability.total_jobs }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Revenue</div>
                    <div class="mt-1 text-2xl font-bold text-blue-600">{{ formatCurrency(jobProfitability.total_revenue) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Cost</div>
                    <div class="mt-1 text-2xl font-bold text-red-600">{{ formatCurrency(jobProfitability.total_cost) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Profit</div>
                    <div class="mt-1 text-2xl font-bold text-green-600">{{ formatCurrency(jobProfitability.total_profit) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Margin</div>
                    <div class="mt-1 text-2xl font-bold text-purple-600">{{ jobProfitability.profit_margin.toFixed(1) }}%</div>
                </div>
            </div>
        </div>

        <!-- Outstanding Invoices -->
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2">
                <DocumentTextIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                <h2 class="text-lg font-semibold text-gray-900">Outstanding Invoices</h2>
            </div>
            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Total Outstanding</div>
                    <div class="mt-1 text-2xl font-bold text-amber-600">{{ formatCurrency(outstandingInvoices.total_outstanding) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Overdue Invoices</div>
                    <div class="mt-1 text-2xl font-bold text-red-600">{{ outstandingInvoices.overdue_count }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Overdue Amount</div>
                    <div class="mt-1 text-2xl font-bold text-red-600">{{ formatCurrency(outstandingInvoices.overdue_amount) }}</div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Balance Due</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="invoice in outstandingInvoices.invoices" :key="invoice.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-blue-600">
                                <a :href="route('cms.invoices.show', invoice.id)">{{ invoice.invoice_number }}</a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ invoice.customer_name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ formatDate(invoice.due_date) }}
                                <span v-if="invoice.days_overdue > 0" class="ml-2 text-red-600">
                                    ({{ invoice.days_overdue }} days overdue)
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-amber-600">
                                {{ formatCurrency(invoice.balance_due) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    :class="[
                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                        invoice.days_overdue > 0 ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'
                                    ]"
                                >
                                    {{ invoice.days_overdue > 0 ? 'Overdue' : 'Outstanding' }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="outstandingInvoices.invoices.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-600">
                                No outstanding invoices
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Profit & Loss Statement -->
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2">
                <CalculatorIcon class="h-6 w-6 text-indigo-600" aria-hidden="true" />
                <h2 class="text-lg font-semibold text-gray-900">Profit & Loss Statement</h2>
            </div>
            <div class="rounded-lg bg-white p-6 shadow">
                <!-- Revenue Section -->
                <div class="mb-6">
                    <div class="flex justify-between border-b pb-2">
                        <span class="font-semibold text-gray-900">Revenue</span>
                        <span class="font-semibold text-gray-900">{{ formatCurrency(profitLoss.revenue) }}</span>
                    </div>
                </div>

                <!-- Cost of Goods Sold -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Cost of Goods Sold</span>
                        <span>{{ formatCurrency(profitLoss.cogs) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between border-t pt-2 font-semibold">
                        <span class="text-gray-900">Gross Profit</span>
                        <span :class="profitLoss.gross_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(profitLoss.gross_profit) }}
                            <span class="ml-2 text-sm font-normal text-gray-600">({{ profitLoss.gross_margin.toFixed(1) }}%)</span>
                        </span>
                    </div>
                </div>

                <!-- Operating Expenses -->
                <div class="mb-6">
                    <div class="mb-2 font-semibold text-gray-900">Operating Expenses</div>
                    <div class="space-y-1 pl-4">
                        <div v-for="(amount, category) in profitLoss.operating_expenses.by_category" :key="category" class="flex justify-between text-sm text-gray-600">
                            <span>{{ category }}</span>
                            <span>{{ formatCurrency(amount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Labor Costs</span>
                            <span>{{ formatCurrency(profitLoss.labor_costs) }}</span>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between border-t pt-2 text-sm font-medium text-gray-700">
                        <span>Total Operating Expenses</span>
                        <span>{{ formatCurrency(profitLoss.total_operating_expenses) }}</span>
                    </div>
                </div>

                <!-- Operating Profit -->
                <div class="mb-6">
                    <div class="flex justify-between border-t pt-2 font-semibold">
                        <span class="text-gray-900">Operating Profit</span>
                        <span :class="profitLoss.operating_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(profitLoss.operating_profit) }}
                            <span class="ml-2 text-sm font-normal text-gray-600">({{ profitLoss.operating_margin.toFixed(1) }}%)</span>
                        </span>
                    </div>
                </div>

                <!-- Net Profit -->
                <div class="border-t-2 pt-4">
                    <div class="flex justify-between text-lg font-bold">
                        <span class="text-gray-900">Net Profit</span>
                        <span :class="profitLoss.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(profitLoss.net_profit) }}
                            <span class="ml-2 text-base font-normal text-gray-600">({{ profitLoss.net_margin.toFixed(1) }}%)</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cashbook Report -->
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2">
                <BookOpenIcon class="h-6 w-6 text-teal-600" aria-hidden="true" />
                <h2 class="text-lg font-semibold text-gray-900">Cashbook Report</h2>
            </div>
            
            <!-- Summary Cards -->
            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Opening Balance</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(cashbook.opening_balance) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Cash In</div>
                    <div class="mt-1 text-2xl font-bold text-green-600">{{ formatCurrency(cashbook.total_cash_in) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Cash Out</div>
                    <div class="mt-1 text-2xl font-bold text-red-600">{{ formatCurrency(cashbook.total_cash_out) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Closing Balance</div>
                    <div class="mt-1 text-2xl font-bold" :class="cashbook.closing_balance >= 0 ? 'text-green-600' : 'text-red-600'">
                        {{ formatCurrency(cashbook.closing_balance) }}
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="sticky top-0 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Method</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Cash In</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Cash Out</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(transaction, index) in cashbook.transactions" :key="index" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ formatDate(transaction.date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ transaction.description }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ transaction.reference }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                    {{ transaction.method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-green-600">
                                    {{ transaction.type === 'in' ? formatCurrency(transaction.amount) : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-red-600">
                                    {{ transaction.type === 'out' ? formatCurrency(transaction.amount) : '-' }}
                                </td>
                            </tr>
                            <tr v-if="cashbook.transactions.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-600">
                                    No transactions in this period
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expense Summary Report -->
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2">
                <ReceiptPercentIcon class="h-6 w-6 text-orange-600" aria-hidden="true" />
                <h2 class="text-lg font-semibold text-gray-900">Expense Summary</h2>
            </div>

            <!-- Summary Cards -->
            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Total Expenses</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ expenseSummary.total_expenses }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Total Amount</div>
                    <div class="mt-1 text-2xl font-bold text-orange-600">{{ formatCurrency(expenseSummary.total_amount) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Approved</div>
                    <div class="mt-1 text-2xl font-bold text-green-600">{{ formatCurrency(expenseSummary.approved_amount) }}</div>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="text-sm font-medium text-gray-600">Pending</div>
                    <div class="mt-1 text-2xl font-bold text-amber-600">{{ formatCurrency(expenseSummary.pending_amount) }}</div>
                </div>
            </div>

            <!-- By Category -->
            <div class="mb-4 rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Expenses by Category</h3>
                <div class="space-y-3">
                    <div v-for="(data, category) in expenseSummary.by_category" :key="category" class="border-b pb-3 last:border-0">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-900">{{ category }}</span>
                            <span class="font-semibold text-gray-900">{{ formatCurrency(data.total) }}</span>
                        </div>
                        <div class="mt-1 flex justify-between text-sm text-gray-600">
                            <span>{{ data.count }} expenses</span>
                            <div class="space-x-4">
                                <span class="text-green-600">Approved: {{ formatCurrency(data.approved) }}</span>
                                <span v-if="data.pending > 0" class="text-amber-600">Pending: {{ formatCurrency(data.pending) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Expenses -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Top 10 Expenses</h3>
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Expense #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Job</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="expense in expenseSummary.top_expenses" :key="expense.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ formatDate(expense.date) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">{{ expense.expense_number }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ expense.category }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ expense.description }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ expense.job || '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-orange-600">
                                    {{ formatCurrency(expense.amount) }}
                                </td>
                            </tr>
                            <tr v-if="expenseSummary.top_expenses.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-600">
                                    No expenses in this period
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tax Report (VAT Summary) -->
        <div class="mb-6">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <ScaleIcon class="h-6 w-6 text-indigo-600" aria-hidden="true" />
                    <h2 class="text-lg font-semibold text-gray-900">Tax Report (VAT Summary)</h2>
                </div>
                <button
                    @click="exportReport('tax')"
                    class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition"
                >
                    <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                    Export CSV
                </button>
            </div>
            
            <div class="rounded-lg bg-white p-6 shadow">
                <div v-if="taxReport" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 p-4">
                        <div class="text-sm font-medium text-blue-700">VAT Collected</div>
                        <div class="mt-2 text-2xl font-bold text-blue-900">{{ formatCurrency(taxReport.vat_collected || 0) }}</div>
                        <div class="mt-1 text-xs text-blue-600">From invoices</div>
                    </div>
                    <div class="rounded-lg bg-red-50 p-4">
                        <div class="text-sm font-medium text-red-700">VAT Paid</div>
                        <div class="mt-2 text-2xl font-bold text-red-900">{{ formatCurrency(taxReport.vat_paid || 0) }}</div>
                        <div class="mt-1 text-xs text-red-600">On expenses</div>
                    </div>
                    <div class="rounded-lg" :class="(taxReport.net_vat_position || 0) >= 0 ? 'bg-green-50' : 'bg-amber-50'">
                        <div class="p-4">
                            <div class="text-sm font-medium" :class="(taxReport.net_vat_position || 0) >= 0 ? 'text-green-700' : 'text-amber-700'">
                                Net VAT Position
                            </div>
                            <div class="mt-2 text-2xl font-bold" :class="(taxReport.net_vat_position || 0) >= 0 ? 'text-green-900' : 'text-amber-900'">
                                {{ formatCurrency(taxReport.net_vat_position || 0) }}
                            </div>
                            <div class="mt-1 text-xs" :class="(taxReport.net_vat_position || 0) >= 0 ? 'text-green-600' : 'text-amber-600'">
                                {{ (taxReport.net_vat_position || 0) >= 0 ? 'Payable to ZRA' : 'Refundable' }}
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-sm font-medium text-gray-700">VAT Rate</div>
                        <div class="mt-2 text-2xl font-bold text-gray-900">{{ taxReport.vat_rate || 16 }}%</div>
                        <div class="mt-1 text-xs text-gray-600">Standard rate</div>
                    </div>
                </div>

                <div v-if="taxReport" class="mt-6 border-t pt-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-700">Taxable Amounts</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Taxable Revenue:</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(taxReport.taxable_revenue || 0) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Taxable Expenses:</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(taxReport.taxable_expenses || 0) }}</span>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 text-gray-500">
                    No tax data available for the selected period
                </div>
            </div>
        </div>

        <!-- Comparative Analysis -->
        <div v-if="comparative" class="mb-6">
            <div class="mb-3 flex items-center gap-2">
                <ArrowTrendingUpIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                <h2 class="text-lg font-semibold text-gray-900">Comparative Analysis</h2>
            </div>

            <!-- Month-over-Month Comparison -->
            <div class="mb-4 rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-base font-semibold text-gray-900">Month-over-Month Growth</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-sm font-medium text-gray-600">Revenue Growth</div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-gray-900">
                                {{ (comparative.month_over_month_growth?.revenue || 0).toFixed(1) }}%
                            </span>
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    (comparative.month_over_month_growth?.revenue || 0) >= 0 ? 'text-green-600' : 'text-red-600'
                                ]"
                            >
                                {{ (comparative.month_over_month_growth?.revenue || 0) >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            Previous: {{ formatCurrency(comparative.previous_period?.revenue || 0) }}
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-sm font-medium text-gray-600">Expense Growth</div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-gray-900">
                                {{ (comparative.month_over_month_growth?.expenses || 0).toFixed(1) }}%
                            </span>
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    (comparative.month_over_month_growth?.expenses || 0) >= 0 ? 'text-red-600' : 'text-green-600'
                                ]"
                            >
                                {{ (comparative.month_over_month_growth?.expenses || 0) >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            Previous: {{ formatCurrency(comparative.previous_period?.expenses || 0) }}
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-sm font-medium text-gray-600">Profit Growth</div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-gray-900">
                                {{ (comparative.month_over_month_growth?.profit || 0).toFixed(1) }}%
                            </span>
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    (comparative.month_over_month_growth?.profit || 0) >= 0 ? 'text-green-600' : 'text-red-600'
                                ]"
                            >
                                {{ (comparative.month_over_month_growth?.profit || 0) >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            Previous: {{ formatCurrency(comparative.previous_period?.profit || 0) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Year-over-Year Comparison -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-base font-semibold text-gray-900">Year-over-Year Growth</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-sm font-medium text-gray-600">Revenue Growth</div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-gray-900">
                                {{ (comparative.year_over_year_growth?.revenue || 0).toFixed(1) }}%
                            </span>
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    (comparative.year_over_year_growth?.revenue || 0) >= 0 ? 'text-green-600' : 'text-red-600'
                                ]"
                            >
                                {{ (comparative.year_over_year_growth?.revenue || 0) >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            Last Year: {{ formatCurrency(comparative.year_over_year?.revenue || 0) }}
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-sm font-medium text-gray-600">Expense Growth</div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-gray-900">
                                {{ (comparative.year_over_year_growth?.expenses || 0).toFixed(1) }}%
                            </span>
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    (comparative.year_over_year_growth?.expenses || 0) >= 0 ? 'text-red-600' : 'text-green-600'
                                ]"
                            >
                                {{ (comparative.year_over_year_growth?.expenses || 0) >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            Last Year: {{ formatCurrency(comparative.year_over_year?.expenses || 0) }}
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-sm font-medium text-gray-600">Profit Growth</div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-gray-900">
                                {{ (comparative.year_over_year_growth?.profit || 0).toFixed(1) }}%
                            </span>
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    (comparative.year_over_year_growth?.profit || 0) >= 0 ? 'text-green-600' : 'text-red-600'
                                ]"
                            >
                                {{ (comparative.year_over_year_growth?.profit || 0) >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            Last Year: {{ formatCurrency(comparative.year_over_year?.profit || 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
