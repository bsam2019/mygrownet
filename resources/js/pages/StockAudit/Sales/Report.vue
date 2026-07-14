<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { ref } from 'vue';

interface SaleItem {
    id: number;
    item_name: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Sale {
    id: number;
    receipt_number: string;
    sale_date: string;
    payment_method: string;
    total: number;
    items: SaleItem[];
}

interface Props {
    sales: Sale[];
    summary: {
        total_sales: number;
        total_transactions: number;
        by_method: Record<string, number>;
        cash_sales: number;
    };
    from: string;
    to: string;
}

const props = defineProps<Props>();

const dateFrom = ref(props.from);
const dateTo = ref(props.to);

const { formatCurrency } = useCurrency();

const searchReport = () => {
    router.get(route('stock-audit.sales.report'), { from: dateFrom.value, to: dateTo.value });
};

const downloadPdf = () => {
    const params = new URLSearchParams({ from: dateFrom.value, to: dateTo.value });
    window.open(route('stock-audit.sales.report') + '/pdf?' + params.toString(), '_blank');
};
</script>

<template>
    <StockAuditLayout title="Sales Report">
        <Head title="Sales Report - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link :href="route('stock-audit.sales.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Sales</Link>
                        <h1 class="mt-2 text-2xl font-bold text-gray-900">Sales Report</h1>
                    </div>
                    <button @click="downloadPdf" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Download PDF
                    </button>
                </div>

                <!-- Date Filter -->
                <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From</label>
                            <input v-model="dateFrom" type="date" class="mt-1 rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">To</label>
                            <input v-model="dateTo" type="date" class="mt-1 rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <button @click="searchReport" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Search</button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Total Sales</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatCurrency(summary.total_sales) }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Transactions</p>
                        <p class="text-xl font-bold text-gray-900">{{ summary.total_transactions }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Cash Sales</p>
                        <p class="text-xl font-bold text-emerald-600">{{ formatCurrency(summary.cash_sales) }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Avg per Transaction</p>
                        <p class="text-xl font-bold text-gray-900">{{ summary.total_transactions > 0 ? formatCurrency(summary.total_sales / summary.total_transactions) : formatCurrency(0) }}</p>
                    </div>
                </div>

                <!-- Sales Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Receipt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Method</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <Link :href="route('stock-audit.sales.show', sale.id)" class="font-medium text-emerald-600 hover:text-emerald-700">{{ sale.receipt_number }}</Link>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ sale.sale_date }}</td>
                                <td class="px-6 py-4 text-sm capitalize text-gray-700">{{ sale.payment_method.replace('_', ' ') }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formatCurrency(sale.total) }}</td>
                            </tr>
                            <tr v-if="sales.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">No sales found for this period</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
