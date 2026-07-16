<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

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
    customer_phone: string | null;
    customer_email: string | null;
    receipt_date: string;
    payment_method: string;
    total: number;
    subtotal: number;
    amount_received: number;
    change_due: number;
    reference_number: string | null;
    notes: string | null;
    sa_sale_id: number | null;
    sa_invoice_id: number | null;
    items: ReceiptItem[];
    created_at: string;
}

interface Props {
    receipt: Receipt;
}

defineProps<Props>();

const { formatCurrency } = useCurrency();

const methodLabels: Record<string, string> = {
    cash: 'Cash',
    mobile_money: 'Mobile Money',
    card: 'Card',
    credit: 'Credit',
    transfer: 'Transfer',
};
</script>

<template>
    <StockFlowLayout :title="`Receipt ${receipt.receipt_number}`">
        <Head :title="`Receipt ${receipt.receipt_number} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.receipts.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Receipts</Link>
                    <h1 class="mt-1 text-xl font-bold text-gray-900">{{ receipt.receipt_number }}</h1>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <p class="text-sm text-gray-500">Date: {{ receipt.receipt_date }}</p>
                            <p class="text-sm text-gray-500">Method: {{ methodLabels[receipt.payment_method] || receipt.payment_method }}</p>
                            <p v-if="receipt.reference_number" class="text-sm text-gray-500">Ref: {{ receipt.reference_number }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                            Paid
                        </span>
                    </div>

                    <div v-if="receipt.customer_name" class="mt-4">
                        <h3 class="text-sm font-medium text-gray-500">Customer</h3>
                        <p class="text-gray-900">{{ receipt.customer_name }}</p>
                        <p v-if="receipt.customer_phone" class="text-sm text-gray-600">{{ receipt.customer_phone }}</p>
                        <p v-if="receipt.customer_email" class="text-sm text-gray-600">{{ receipt.customer_email }}</p>
                    </div>

                    <table class="mt-4 w-full">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="pb-2 text-left font-medium">Description</th>
                                <th class="pb-2 text-right font-medium">Qty</th>
                                <th class="pb-2 text-right font-medium">Price</th>
                                <th class="pb-2 text-right font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in receipt.items" :key="item.id">
                                <td class="py-2 text-sm text-gray-900">{{ item.item_description }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ item.quantity }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="py-2 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <div class="ml-auto w-64 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Total</span>
                                <span class="text-gray-900 font-semibold">{{ formatCurrency(receipt.total) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Amount Received</span>
                                <span class="text-emerald-600 font-semibold">{{ formatCurrency(receipt.amount_received) }}</span>
                            </div>
                            <div v-if="receipt.change_due > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Change</span>
                                <span class="text-amber-600">{{ formatCurrency(receipt.change_due) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="receipt.notes" class="mt-4 border-t border-gray-200 pt-4">
                        <p class="text-xs font-medium text-gray-500">Notes</p>
                        <p class="mt-1 text-sm text-gray-700">{{ receipt.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
