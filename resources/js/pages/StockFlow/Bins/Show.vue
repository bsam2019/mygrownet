<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { useCurrency } from '@/composables/useCurrency';

const { route } = useStockflowRoute();
const { formatCurrency } = useCurrency();

interface BinItem {
    id: number;
    name: string;
    sku: string | null;
    unit_price: number;
    system_quantity: number;
    unit: string;
    category: string | null;
    expiry_date: string | null;
}

interface Props {
    bin: { id: number; name: string; label: string | null; description: string | null; department_name: string };
    items: BinItem[];
}

defineProps<Props>();
</script>

<template>
    <StockFlowLayout :title="`Bin: ${bin.name}`">
        <Head :title="`${bin.name} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.bins.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Bins</Link>
                </div>

                <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ bin.name }}</h1>
                            <p v-if="bin.label" class="mt-1 text-sm text-gray-500">{{ bin.label }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">{{ bin.department_name }}</span>
                    </div>
                    <p v-if="bin.description" class="mt-3 text-sm text-gray-600">{{ bin.description }}</p>
                </div>

                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Items in this Bin ({{ items.length }})</h2>
                    </div>
                    <div v-if="items.length" class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-gray-50/50">
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Price</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Unit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <Link :href="route('stockflow.sub.items.show', item.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                            {{ item.name }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.sku || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.category || '-' }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium" :class="item.system_quantity <= 0 ? 'text-red-600' : 'text-gray-900'">{{ item.system_quantity }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-500">{{ item.unit }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-6 py-12 text-center">
                        <p class="text-gray-400">No items in this bin.</p>
                        <Link :href="route('stockflow.sub.items.create')" class="mt-2 inline-block text-sm text-emerald-600 hover:text-emerald-700">Create an item</Link>
                    </div>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
