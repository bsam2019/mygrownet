<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import LoadingSkeleton from '@/components/StockFlow/LoadingSkeleton.vue';
import Pagination from '@/components/StockFlow/Pagination.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const { route } = useStockflowRoute();

interface TransferItem {
    id: number;
    sa_item_id: number;
    quantity: number;
    unit_cost: number | null;
}

interface Transfer {
    id: number;
    transfer_number: string;
    from_warehouse_id: number;
    to_warehouse_id: number;
    status: string;
    notes: string | null;
    items: TransferItem[];
    created_at: string;
}

interface Props {
    transfers: {
        data: Transfer[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
}

defineProps<Props>();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    in_transit: 'bg-blue-100 text-blue-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};
</script>

<template>
    <StockFlowLayout title="Transfers">
        <Head title="Transfers - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Warehouse Transfers</h1>
                    <Link :href="route('stockflow.sub.transfers.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        New Transfer
                    </Link>
                </div>

                <LoadingSkeleton v-if="!transfers.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Transfer #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">From</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Items</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="transfer in transfers.data" :key="transfer.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <Link :href="route('stockflow.sub.transfers.show', transfer.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                            {{ transfer.transfer_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ transfer.from_warehouse_id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ transfer.to_warehouse_id }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[statusColors[transfer.status] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                            {{ transfer.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-700">{{ transfer.items?.length || 0 }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-500">{{ transfer.created_at }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('stockflow.sub.transfers.show', transfer.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!transfers.data?.length">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">No transfers yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="transfers.links" :meta="transfers.meta" />
                </template>
            </div>
        </div>
    </StockFlowLayout>
</template>
