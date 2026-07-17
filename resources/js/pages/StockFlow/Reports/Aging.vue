<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

interface AgingEntry {
    id: number;
    name: string;
    sku: string | null;
    expiry_date: string;
    quantity: number;
    value: number;
    days_remaining: number;
}

interface AgingBucket {
    label: string;
    items: AgingEntry[];
    count: number;
    value: number;
}

interface Props {
    buckets: Record<string, AgingBucket>;
}

defineProps<Props>();

const { formatCurrency } = useCurrency();

const bucketOrder = ['expired', '0-30', '31-60', '61-90', '90+'];
const bucketColors: Record<string, string> = {
    expired: 'bg-red-50 border-red-200',
    '0-30': 'bg-red-50 border-red-200',
    '31-60': 'bg-amber-50 border-amber-200',
    '61-90': 'bg-yellow-50 border-yellow-200',
    '90+': 'bg-green-50 border-green-200',
};
const headerColors: Record<string, string> = {
    expired: 'text-red-800',
    '0-30': 'text-red-800',
    '31-60': 'text-amber-800',
    '61-90': 'text-yellow-800',
    '90+': 'text-green-800',
};
</script>

<template>
    <StockFlowLayout title="Stock Aging Report">
        <Head title="Stock Aging - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('stockflow.sub.reports.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Reports</Link>
                </div>

                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Stock Aging Report</h1>
                    <p class="text-sm text-gray-500">Items grouped by days until expiry</p>
                </div>

                <div class="space-y-6">
                    <div v-for="key in bucketOrder" :key="key" :class="['rounded-xl border p-4', bucketColors[key] || 'bg-white border-gray-200']">
                        <div class="flex items-center justify-between mb-3">
                            <h3 :class="['text-base font-semibold', headerColors[key] || 'text-gray-900']">
                                {{ buckets[key]?.label || key }}
                            </h3>
                            <div class="text-sm" :class="headerColors[key] || 'text-gray-500'">
                                {{ buckets[key]?.count || 0 }} items &middot; {{ formatCurrency(buckets[key]?.value || 0) }}
                            </div>
                        </div>

                        <table v-if="buckets[key]?.items?.length" class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-gray-500">
                                    <th class="pb-1 text-left font-medium">Item</th>
                                    <th class="pb-1 text-left font-medium">SKU</th>
                                    <th class="pb-1 text-right font-medium">Expiry</th>
                                    <th class="pb-1 text-right font-medium">Days Left</th>
                                    <th class="pb-1 text-right font-medium">Qty</th>
                                    <th class="pb-1 text-right font-medium">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="entry in buckets[key].items" :key="entry.id" :class="entry.days_remaining < 0 ? 'text-red-600 font-medium' : ''">
                                    <td class="py-1">{{ entry.name }}</td>
                                    <td class="py-1 text-gray-500">{{ entry.sku || '-' }}</td>
                                    <td class="py-1 text-right">{{ entry.expiry_date }}</td>
                                    <td class="py-1 text-right">{{ entry.days_remaining }}</td>
                                    <td class="py-1 text-right">{{ entry.quantity }}</td>
                                    <td class="py-1 text-right">{{ formatCurrency(entry.value) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-sm text-gray-400">No items in this bucket.</p>
                    </div>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
