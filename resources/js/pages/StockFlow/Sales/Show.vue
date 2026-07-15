<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import CommentSection from '@/components/StockFlow/CommentSection.vue';
import { useCurrency } from '@/composables/useCurrency';

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
    sale_time: string;
    payment_method: string;
    subtotal: number;
    total: number;
    amount_tendered: number;
    change_due: number;
    notes: string | null;
    sold_by: string;
    items: SaleItem[];
    created_at: string;
}

interface Props {
    sale: Sale;
}

defineProps<Props>();

const { formatCurrency } = useCurrency();
</script>

<template>
    <StockFlowLayout :title="`Sale ${sale.receipt_number}`">
        <Head :title="`Sale ${sale.receipt_number} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.sales.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Sales</Link>
                </div>

                <!-- Receipt Header -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ sale.receipt_number }}</h1>
                            <p class="text-sm text-gray-500">{{ sale.sale_date }} at {{ sale.sale_time }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700 capitalize">
                            {{ sale.payment_method.replace('_', ' ') }}
                        </span>
                    </div>

                    <!-- Items -->
                    <table class="mt-4 w-full">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="pb-2 text-left font-medium">Item</th>
                                <th class="pb-2 text-right font-medium">Qty</th>
                                <th class="pb-2 text-right font-medium">Price</th>
                                <th class="pb-2 text-right font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in sale.items" :key="item.id">
                                <td class="py-2 text-sm text-gray-900">{{ item.item_name }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ item.quantity }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="py-2 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <div class="ml-auto w-64 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="text-gray-900">{{ formatCurrency(sale.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tendered</span>
                                <span class="text-gray-900">{{ formatCurrency(sale.amount_tendered) }}</span>
                            </div>
                            <div v-if="sale.change_due > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Change</span>
                                <span class="text-emerald-600">{{ formatCurrency(sale.change_due) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900">{{ formatCurrency(sale.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="sale.notes" class="mt-4 border-t border-gray-200 pt-4">
                        <p class="text-xs text-gray-500">Notes</p>
                        <p class="mt-1 text-sm text-gray-700">{{ sale.notes }}</p>
                    </div>
                </div>

                <!-- Comments -->
                <div class="mt-6">
                    <CommentSection type="sale" :id="sale.id" />
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
