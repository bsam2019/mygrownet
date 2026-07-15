<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import LoadingSkeleton from '@/components/StockFlow/LoadingSkeleton.vue';
import Pagination from '@/components/StockFlow/Pagination.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const { route } = useStockflowRoute();

interface PurchaseOrderItem {
    id: number;
    sa_item_id: number;
    quantity_ordered: number;
    quantity_received: number;
    unit_cost: number;
    total_cost: number;
}

interface PurchaseOrder {
    id: number;
    order_number: string;
    order_date: string;
    status: string;
    subtotal: number;
    total: number;
    notes: string | null;
    items: PurchaseOrderItem[];
    created_at: string;
}

interface Props {
    orders: {
        data: PurchaseOrder[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
}

defineProps<Props>();

const { formatCurrency } = useCurrency();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    partial: 'bg-blue-100 text-blue-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};
</script>

<template>
    <StockFlowLayout title="Purchase Orders">
        <Head title="Purchase Orders - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Purchase Orders</h1>
                    <Link :href="route('stockflow.sub.purchases.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        New Purchase Order
                    </Link>
                </div>

                <LoadingSkeleton v-if="!orders.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Order #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Items</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <Link :href="route('stockflow.sub.purchases.show', order.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                            {{ order.order_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ order.order_date }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[statusColors[order.status] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-700">{{ order.items?.length || 0 }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formatCurrency(order.total) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('stockflow.sub.purchases.show', order.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!orders.data?.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">No purchase orders yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="orders.links" :meta="orders.meta" />
                </template>
            </div>
        </div>
    </StockFlowLayout>
</template>
