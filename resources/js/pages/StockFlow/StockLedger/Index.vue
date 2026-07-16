<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ item: any, movements: any[] }>();
const { sf } = useStockflowRoute();

let balance = 0;
const withBalance = props.movements.map((m: any) => {
    balance += (m.type === 'purchase_in' || m.type === 'return_in' || m.type === 'opening_balance' || m.type === 'adjustment_in') ? m.quantity : -Math.abs(m.quantity);
    return { ...m, running_balance: balance };
});
</script>

<template>
    <Head :title="'Stock Ledger - ' + item.name" />
    <StockFlowLayout :title="'Stock Ledger: ' + item.name">
        <div class="max-w-5xl mx-auto py-6 px-4">
            <Link :href="sf('items.index')" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Items</Link>
            <div class="bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-4 gap-4 text-sm">
                <div><span class="text-gray-500">SKU:</span> {{ item.sku || '-' }}</div>
                <div><span class="text-gray-500">Barcode:</span> {{ item.barcode || '-' }}</div>
                <div><span class="text-gray-500">Brand:</span> {{ item.brand || '-' }}</div>
                <div><span class="text-gray-500">Category:</span> {{ item.category || '-' }}</div>
                <div><span class="text-gray-500">Unit Price:</span> {{ item.unit_price }}</div>
                <div><span class="text-gray-500">Wholesale:</span> {{ item.wholesale_price || '-' }}</div>
                <div><span class="text-gray-500">VIP:</span> {{ item.vip_price || '-' }}</div>
                <div><span class="text-gray-500">Current Stock:</span> <strong>{{ item.system_quantity }}</strong></div>
            </div>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left text-sm">Date</th><th class="px-4 py-3 text-left text-sm">Type</th><th class="px-4 py-3 text-left text-sm">In</th><th class="px-4 py-3 text-left text-sm">Out</th><th class="px-4 py-3 text-left text-sm">Balance</th><th class="px-4 py-3 text-left text-sm">Reason</th></tr></thead>
                    <tbody>
                        <tr v-for="m in withBalance" :key="m.id" class="border-t text-sm">
                            <td class="px-4 py-2">{{ m.created_at }}</td>
                            <td class="px-4 py-2 capitalize">{{ m.type.replace(/_/g, ' ') }}</td>
                            <td class="px-4 py-2 text-green-600">{{ (m.type === 'purchase_in' || m.type === 'return_in' || m.type === 'opening_balance' || m.type === 'adjustment_in') ? m.quantity : '-' }}</td>
                            <td class="px-4 py-2 text-red-600">{{ (m.type === 'sale_out' || m.type === 'damage_out' || m.type === 'expired_out' || m.type === 'adjustment_out' || m.type === 'physical_count') ? Math.abs(m.quantity) : '-' }}</td>
                            <td class="px-4 py-2 font-medium">{{ m.running_balance }}</td>
                            <td class="px-4 py-2">{{ m.reason || '-' }}</td>
                        </tr>
                        <tr v-if="!movements.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">No stock movements recorded.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
