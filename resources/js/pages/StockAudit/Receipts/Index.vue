<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import LoadingSkeleton from '@/components/StockAudit/LoadingSkeleton.vue';
import Pagination from '@/components/StockAudit/Pagination.vue';

interface ReceiptItem {
    id: number;
    item_description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Receipt {
    id: number;
    receipt_number: string;
    customer_name: string | null;
    receipt_date: string;
    payment_method: string;
    total: number;
    amount_received: number;
    reference_number: string | null;
    items: ReceiptItem[];
    created_at: string;
}

interface Props {
    receipts: {
        data: Receipt[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
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
    <StockAuditLayout title="Receipts">
        <Head title="Receipts - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Receipts</h1>
                        <p class="mt-1 text-sm text-gray-500">View payment receipts</p>
                    </div>
                </div>

                <LoadingSkeleton v-if="!receipts.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Receipt #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Method</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Received</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="receipt in receipts.data" :key="receipt.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <Link :href="route('stockflow.sub.receipts.show', receipt.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                            {{ receipt.receipt_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ receipt.customer_name || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ receipt.receipt_date }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[methodColors[receipt.payment_method] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                            {{ receipt.payment_method.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formatCurrency(receipt.total) }}</td>
                                    <td class="px-6 py-4 text-right font-medium text-emerald-600">{{ formatCurrency(receipt.amount_received) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('stockflow.sub.receipts.show', receipt.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!receipts.data?.length">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">No receipts yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="receipts.links" :meta="receipts.meta" />
                </template>
            </div>
        </div>
    </StockAuditLayout>
</template>
