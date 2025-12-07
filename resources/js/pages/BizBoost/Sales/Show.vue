<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, PrinterIcon } from '@heroicons/vue/24/outline';

interface SaleItem {
    id: number;
    product_name: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Sale {
    id: number;
    customer_name: string | null;
    customer_phone: string | null;
    total_amount: number;
    payment_method: string;
    status: string;
    notes: string | null;
    items: SaleItem[];
    created_at: string;
}

interface Props {
    sale: Sale;
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Sale #${sale.id} - BizBoost`" />
    <BizBoostLayout :title="`Sale #${sale.id}`">
        <div class="max-w-2xl">
            <div class="flex items-center justify-between mb-6">
                <Link
                    href="/bizboost/sales"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Sales
                </Link>
                <button
                    @click="printReceipt"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    <PrinterIcon class="h-4 w-4" aria-hidden="true" />
                    Print Receipt
                </button>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 print:shadow-none print:ring-0">
                <!-- Header -->
                <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-200">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sale #{{ sale.id }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ formatDate(sale.created_at) }}</p>
                    </div>
                    <span
                        :class="[
                            'text-sm px-3 py-1 rounded-full',
                            sale.status === 'completed' ? 'bg-green-100 text-green-700' :
                            sale.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                            'bg-red-100 text-red-700'
                        ]"
                    >
                        {{ sale.status }}
                    </span>
                </div>

                <!-- Customer Info -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Customer</h3>
                    <p class="text-gray-900">{{ sale.customer_name || 'Walk-in Customer' }}</p>
                    <p v-if="sale.customer_phone" class="text-sm text-gray-500">{{ sale.customer_phone }}</p>
                </div>

                <!-- Items -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-3">Items</h3>
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase border-b border-gray-200">
                                <th class="pb-2">Item</th>
                                <th class="pb-2 text-center">Qty</th>
                                <th class="pb-2 text-right">Price</th>
                                <th class="pb-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in sale.items" :key="item.id">
                                <td class="py-3 text-sm text-gray-900">{{ item.product_name }}</td>
                                <td class="py-3 text-sm text-gray-600 text-center">{{ item.quantity }}</td>
                                <td class="py-3 text-sm text-gray-600 text-right">K{{ item.unit_price.toLocaleString() }}</td>
                                <td class="py-3 text-sm font-medium text-gray-900 text-right">K{{ item.total.toLocaleString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p class="text-gray-900 capitalize">{{ sale.payment_method.replace('_', ' ') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Amount</p>
                            <p class="text-2xl font-bold text-violet-600">K{{ sale.total_amount.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="sale.notes" class="mt-6 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Notes</h3>
                    <p class="text-sm text-gray-600">{{ sale.notes }}</p>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print\:shadow-none,
    .print\:shadow-none * {
        visibility: visible;
    }
    .print\:shadow-none {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
