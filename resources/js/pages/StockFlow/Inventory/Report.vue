<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref } from 'vue';

const { route } = useStockflowRoute();

interface InventoryItem {
    id: number;
    name: string;
    sku: string | null;
    category: string | null;
    system_quantity: number;
    unit_price: number;
    reorder_level: number | null;
}

interface Props {
    items: InventoryItem[];
    summary: {
        total_items: number;
        total_value: number;
        low_stock: number;
        out_of_stock: number;
    };
    reportDate: string;
}

const props = defineProps<Props>();
const { formatCurrency } = useCurrency();

const downloadPdf = () => {
    window.open(route('stockflow.sub.inventory.report') + '/pdf', '_blank');
};
</script>

<template>
    <StockFlowLayout title="Inventory Report">
        <Head title="Inventory Report - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link :href="route('stockflow.sub.items.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Items</Link>
                        <h1 class="mt-2 text-2xl font-bold text-gray-900">Inventory Report</h1>
                        <p class="text-sm text-gray-500">As of {{ reportDate }}</p>
                    </div>
                    <button @click="downloadPdf" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Download PDF
                    </button>
                </div>

                <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Total Items</p>
                        <p class="text-xl font-bold text-gray-900">{{ summary.total_items }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Total Stock Value</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatCurrency(summary.total_value) }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Low Stock</p>
                        <p class="text-xl font-bold text-amber-600">{{ summary.low_stock }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Out of Stock</p>
                        <p class="text-xl font-bold text-red-600">{{ summary.out_of_stock }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50" :class="{ 'bg-red-50': item.system_quantity <= 0, 'bg-amber-50': item.system_quantity > 0 && item.reorder_level !== null && item.system_quantity <= item.reorder_level }">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ item.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ item.sku || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ item.category || '-' }}</td>
                                <td class="px-6 py-4 text-right font-semibold" :class="{ 'text-red-600': item.system_quantity <= 0, 'text-amber-600': item.system_quantity > 0 && item.reorder_level !== null && item.system_quantity <= item.reorder_level }">{{ item.system_quantity }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formatCurrency(item.unit_price * item.system_quantity) }}</td>
                            </tr>
                            <tr v-if="items.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No items found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
