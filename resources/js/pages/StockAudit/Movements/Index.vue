<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';

interface StockMovement {
    id: number;
    sa_item_id: number;
    type: string;
    quantity: number;
    quantity_before: number;
    quantity_after: number;
    unit_price: number;
    total_value: number;
    reason: string;
    created_by: number;
    created_at: string;
}

interface Props {
    movements: StockMovement[];
}

defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 2 }).format(amount);
};

const typeColors: Record<string, string> = {
    addition: 'bg-green-100 text-green-800',
    deduction: 'bg-red-100 text-red-800',
    sale_out: 'bg-blue-100 text-blue-800',
    purchase_in: 'bg-emerald-100 text-emerald-800',
    adjustment_in: 'bg-amber-100 text-amber-800',
    adjustment_out: 'bg-orange-100 text-orange-800',
    damage_out: 'bg-red-100 text-red-800',
    expired_out: 'bg-gray-100 text-gray-800',
    return_in: 'bg-purple-100 text-purple-800',
    opening_balance: 'bg-indigo-100 text-indigo-800',
};
</script>

<template>
    <StockAuditLayout title="Stock Movements">
        <Head title="Stock Movements - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Stock Movements</h1>
                    <p class="mt-1 text-sm text-gray-500">Append-only ledger of all stock changes</p>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Item ID</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Before</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">After</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Reason</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="m in movements" :key="m.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span :class="[typeColors[m.type] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                        {{ m.type.replace(/_/g, ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ m.sa_item_id }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ m.quantity_before }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium" :class="m.quantity >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ m.quantity >= 0 ? '+' : '' }}{{ m.quantity }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ m.quantity_after }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ formatCurrency(m.total_value) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">{{ m.reason }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">{{ m.created_at }}</td>
                            </tr>
                            <tr v-if="movements.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No stock movements recorded</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
