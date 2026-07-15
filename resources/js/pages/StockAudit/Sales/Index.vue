<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import LoadingSkeleton from '@/components/StockAudit/LoadingSkeleton.vue';
import Pagination from '@/components/StockAudit/Pagination.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

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
    amount_tendered: number;
    change_due: number;
    items: SaleItem[];
    created_at: string;
}

interface Props {
    sales: {
        data: Sale[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
    todayTotal: number;
}

defineProps<Props>();

const { formatCurrency } = useCurrency();

const methodColors: Record<string, string> = {
    cash: 'bg-green-100 text-green-800',
    mobile_money: 'bg-blue-100 text-blue-800',
    card: 'bg-purple-100 text-purple-800',
    credit: 'bg-amber-100 text-amber-800',
    transfer: 'bg-indigo-100 text-indigo-800',
};
</script>

<template>
    <StockAuditLayout title="Sales">
        <Head title="Sales - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sales</h1>
                        <p class="mt-1 text-sm text-gray-500">Today's total: <span class="font-semibold text-emerald-600">{{ formatCurrency(todayTotal) }}</span></p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('stockflow.sub.sales.report')" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Reports
                        </Link>
                        <Link :href="route('stockflow.sub.sales.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            New Sale
                        </Link>
                    </div>
                </div>

                <LoadingSkeleton v-if="!sales.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Receipt</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Method</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Items</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <Link :href="route('stockflow.sub.sales.show', sale.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                            {{ sale.receipt_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ sale.sale_date }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[methodColors[sale.payment_method] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                            {{ sale.payment_method.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-700">{{ sale.items?.length || 0 }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formatCurrency(sale.total) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('stockflow.sub.sales.show', sale.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!sales.data?.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">No sales recorded yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="sales.links" :meta="sales.meta" />
                </template>
            </div>
        </div>
    </StockAuditLayout>
</template>
