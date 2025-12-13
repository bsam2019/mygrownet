<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import { ArrowTrendingUpIcon, ArrowTrendingDownIcon } from '@heroicons/vue/24/outline';

interface Movement { id: number; type: string; quantity: number; stock_before: number; stock_after: number; notes: string | null; created_at: string; item: { name: string }; }
const props = defineProps<{ movements: { data: Movement[] } }>();

const formatDate = (d: string) => new Date(d).toLocaleDateString('en-ZM', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
const getTypeColor = (type: string) => {
    const colors: Record<string, string> = { purchase: 'bg-green-100 text-green-800', sale: 'bg-red-100 text-red-800', adjustment: 'bg-blue-100 text-blue-800', return: 'bg-amber-100 text-amber-800', damage: 'bg-red-100 text-red-800', initial: 'bg-gray-100 text-gray-800' };
    return colors[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <InventoryLayout title="Movements">
        <Head title="Stock Movements" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <h1 class="mb-6 text-2xl font-bold text-gray-900">Stock Movements</h1>
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="m in movements.data" :key="m.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(m.created_at) }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ m.item.name }}</td>
                                <td class="px-6 py-4"><span :class="[getTypeColor(m.type), 'rounded-full px-2 py-1 text-xs font-medium capitalize']">{{ m.type }}</span></td>
                                <td class="px-6 py-4 text-right">
                                    <span :class="m.quantity > 0 ? 'text-green-600' : 'text-red-600'" class="font-semibold">{{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">{{ m.stock_before }} → {{ m.stock_after }}</td>
                            </tr>
                            <tr v-if="movements.data.length === 0"><td colspan="5" class="px-6 py-12 text-center text-gray-500">No movements recorded</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </InventoryLayout>
</template>
