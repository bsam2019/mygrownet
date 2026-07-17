<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

interface TurnoverItem {
    id: number;
    name: string;
    sku: string | null;
    total_sold: number;
    current_stock: number;
    avg_inventory: number;
    turnover: number;
    category: string;
}

interface Props {
    items: TurnoverItem[];
}

defineProps<Props>();

const { formatCurrency } = useCurrency();

const categoryColors: Record<string, string> = {
    'Fast mover': 'bg-green-100 text-green-800',
    'Medium': 'bg-yellow-100 text-yellow-800',
    'Slow mover': 'bg-red-100 text-red-800',
};
</script>

<template>
    <StockFlowLayout title="Inventory Turnover">
        <Head title="Inventory Turnover - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('stockflow.sub.reports.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Reports</Link>
                </div>

                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Turnover</h1>
                    <p class="text-sm text-gray-500">Items sorted by turnover rate (last 6 months)</p>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">SKU</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total Sold</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Current Stock</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Avg Inventory</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Turnover</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ item.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ item.sku || '-' }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ item.total_sold }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ item.current_stock }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ item.avg_inventory }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ item.turnover }}</td>
                                <td class="px-6 py-4">
                                    <span :class="[categoryColors[item.category] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium']">
                                        {{ item.category }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No items found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
