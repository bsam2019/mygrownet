<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

interface AbcItem {
    id: number;
    name: string;
    sku: string | null;
    annual_value: number;
    percentage: number;
    cumulative_percentage: number;
    class: string;
}

interface Props {
    items: AbcItem[];
    total_value: number;
}

defineProps<Props>();

const { formatCurrency } = useCurrency();

const classColors: Record<string, string> = {
    A: 'bg-red-100 text-red-800',
    B: 'bg-yellow-100 text-yellow-800',
    C: 'bg-green-100 text-green-800',
};

const exportCsv = () => {
    const headers = ['Class', 'Item Name', 'SKU', 'Annual Value', 'Percentage', 'Cumulative %'];
    const rows = (window as any).__abcItems?.map((i: AbcItem) => [
        i.class, i.name, i.sku || '', i.annual_value, i.percentage, i.cumulative_percentage,
    ]) ?? [];
    const csv = [headers.join(','), ...rows.map((r: any) => r.join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'abc-analysis.csv';
    a.click();
    URL.revokeObjectURL(url);
};
</script>

<template>
    <StockFlowLayout title="ABC Analysis">
        <Head title="ABC Analysis - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('stockflow.sub.reports.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Reports</Link>
                </div>

                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">ABC Analysis</h1>
                        <p class="text-sm text-gray-500">Total Inventory Value: {{ formatCurrency(total_value) }}</p>
                    </div>
                    <button @click="exportCsv" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                        Export CSV
                    </button>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">SKU</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Annual Value</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">%</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Cumulative %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span :class="[classColors[item.class] || 'bg-gray-100', 'inline-flex rounded-full px-2 py-1 text-xs font-bold']">
                                        {{ item.class }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ item.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ item.sku || '-' }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formatCurrency(item.annual_value) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ item.percentage }}%</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ item.cumulative_percentage }}%</td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No items found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
