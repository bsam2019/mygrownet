<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

interface TransferItem {
    id: number;
    sa_item_id: number;
    item_name: string | null;
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
    updated_at: string;
}

interface Props {
    transfer: Transfer;
}

const props = defineProps<Props>();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    in_transit: 'bg-blue-100 text-blue-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

const handleReceive = () => {
    if (confirm('Mark this transfer as received?')) {
        router.post(route('stockflow.sub.transfers.receive', props.transfer.id));
    }
};

const handleCancel = () => {
    if (confirm('Cancel this transfer?')) {
        router.post(route('stockflow.sub.transfers.cancel', props.transfer.id));
    }
};
</script>

<template>
    <StockFlowLayout :title="`Transfer ${transfer.transfer_number}`">
        <Head :title="`Transfer ${transfer.transfer_number} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.transfers.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Transfers</Link>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ transfer.transfer_number }}</h1>
                            <p class="text-sm text-gray-500">Created {{ transfer.created_at }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="[statusColors[transfer.status] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize']">
                                {{ transfer.status.replace('_', ' ') }}
                            </span>
                            <button v-if="transfer.status === 'in_transit'" @click="handleReceive" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                                Receive
                            </button>
                            <button v-if="transfer.status === 'draft' || transfer.status === 'in_transit'" @click="handleCancel" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">From Warehouse:</span>
                            <p class="font-medium text-gray-900">{{ transfer.from_warehouse_id }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">To Warehouse:</span>
                            <p class="font-medium text-gray-900">{{ transfer.to_warehouse_id }}</p>
                        </div>
                    </div>

                    <table class="mt-6 w-full">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="pb-2 text-left font-medium">Item</th>
                                <th class="pb-2 text-right font-medium">Quantity</th>
                                <th class="pb-2 text-right font-medium">Unit Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in transfer.items" :key="item.id">
                                <td class="py-2 text-sm text-gray-900">{{ item.item_name || `Item #${item.sa_item_id}` }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ item.quantity }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ item.unit_cost ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="transfer.notes" class="mt-4 border-t border-gray-200 pt-4">
                        <p class="text-xs text-gray-500">Notes</p>
                        <p class="mt-1 text-sm text-gray-700">{{ transfer.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
