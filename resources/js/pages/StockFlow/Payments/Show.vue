<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ transaction: any }>();
const { sf } = useStockflowRoute();
</script>

<template>
    <Head title="Payment Details" />
    <StockFlowLayout title="Payment Details">
        <div class="max-w-3xl mx-auto py-6 px-4">
            <Link :href="sf('payments.index')" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back</Link>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Transaction #{{ transaction.id }}</h2>
                <dl class="grid grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500">Gateway</dt><dd class="capitalize">{{ transaction.gateway }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Amount</dt><dd>{{ transaction.amount }} {{ transaction.currency }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Status</dt><dd class="capitalize">{{ transaction.status }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Transaction ID</dt><dd>{{ transaction.transaction_id || 'N/A' }}</dd></div>
                </dl>
            </div>
        </div>
    </StockFlowLayout>
</template>
