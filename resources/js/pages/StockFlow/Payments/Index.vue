<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ transactions: any[] }>();
const { sf } = useStockflowRoute();
</script>

<template>
    <Head title="Payment Transactions" />
    <StockFlowLayout title="Payment Transactions">
        <div class="max-w-5xl mx-auto py-6 px-4">
            <h1 class="text-2xl font-bold mb-6">Payment Transactions</h1>
            <div class="bg-white rounded-lg shadow">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">ID</th><th class="px-4 py-3 text-left">Gateway</th><th class="px-4 py-3 text-left">Amount</th><th class="px-4 py-3 text-left">Currency</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Date</th></tr></thead>
                    <tbody>
                        <tr v-for="t in transactions" :key="t.id" class="border-t">
                            <td class="px-4 py-3">{{ t.id }}</td>
                            <td class="px-4 py-3 capitalize">{{ t.gateway }}</td>
                            <td class="px-4 py-3">{{ t.amount.toLocaleString() }}</td>
                            <td class="px-4 py-3">{{ t.currency }}</td>
                            <td class="px-4 py-3"><span :class="t.status === 'completed' ? 'text-green-600' : t.status === 'failed' ? 'text-red-600' : 'text-yellow-600'" class="capitalize">{{ t.status }}</span></td>
                            <td class="px-4 py-3">{{ t.created_at }}</td>
                        </tr>
                        <tr v-if="!transactions.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">No payment transactions.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
