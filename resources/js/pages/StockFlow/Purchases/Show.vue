<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import CommentSection from '@/components/StockFlow/CommentSection.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref, computed } from 'vue';

const { route } = useStockflowRoute();

interface PurchaseOrderItem {
    id: number;
    sa_item_id: number;
    item_name?: string;
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
    updated_at: string;
}

interface Props {
    order: PurchaseOrder;
}

const props = defineProps<Props>();

const showReceive = ref(false);
const receiveItems = ref<Record<number, { quantity_received: number; unit_cost: number }>>({});

const errors = ref<Record<string, string>>({});

const { formatCurrency } = useCurrency();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    partial: 'bg-blue-100 text-blue-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

const canReceive = computed(() => {
    return props.order.status === 'pending' || props.order.status === 'partial';
});

const initReceive = () => {
    const items: Record<number, { quantity_received: number; unit_cost: number }> = {};
    props.order.items.forEach(item => {
        items[item.sa_item_id] = {
            quantity_received: item.quantity_ordered - item.quantity_received,
            unit_cost: item.unit_cost,
        };
    });
    receiveItems.value = items;
    showReceive.value = true;
};

const submitReceive = () => {
    const items = Object.entries(receiveItems.value).map(([sa_item_id, data]) => ({
        sa_item_id: parseInt(sa_item_id),
        quantity_received: data.quantity_received,
        unit_cost: data.unit_cost,
    }));

    router.post(route('stockflow.sub.purchases.receive', props.order.id), { items }, {
        onSuccess: () => { showReceive.value = false; },
        onError: (err) => { errors.value = err; },
    });
};
</script>

<template>
    <StockFlowLayout :title="`PO ${order.order_number}`">
        <Head :title="`PO ${order.order_number} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.purchases.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Purchases</Link>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ order.order_number }}</h1>
                            <p class="text-sm text-gray-500">{{ order.order_date }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="[statusColors[order.status] || 'bg-gray-100 text-gray-800', 'rounded-full px-3 py-1 text-sm font-medium capitalize']">
                                {{ order.status }}
                            </span>
                            <button v-if="canReceive" @click="initReceive" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Receive Stock
                            </button>
                        </div>
                    </div>

                    <!-- Receive Form -->
                    <div v-if="showReceive" class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <h3 class="font-semibold text-blue-800">Receive Stock</h3>
                        <div v-for="item in order.items" :key="item.id" class="mt-3 grid gap-3 sm:grid-cols-3">
                            <p class="text-sm font-medium text-gray-700 pt-2">{{ item.item_name || 'Item #' + item.sa_item_id }} (Ordered: {{ item.quantity_ordered }}, Received: {{ item.quantity_received }})</p>
                            <div>
                                <label class="block text-xs text-gray-500">Qty Receiving</label>
                                <input v-model.number="receiveItems[item.sa_item_id].quantity_received" type="number" step="0.01" min="0" class="mt-1 w-full rounded border-blue-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">Unit Cost</label>
                                <input v-model.number="receiveItems[item.sa_item_id].unit_cost" type="number" step="0.01" min="0" class="mt-1 w-full rounded border-blue-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>
                        <div class="mt-4 flex gap-3">
                            <button @click="submitReceive" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Confirm Receive</button>
                            <button @click="showReceive = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <table class="mt-6 w-full">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="pb-2 text-left font-medium">Item</th>
                                <th class="pb-2 text-right font-medium">Ordered</th>
                                <th class="pb-2 text-right font-medium">Received</th>
                                <th class="pb-2 text-right font-medium">Unit Cost</th>
                                <th class="pb-2 text-right font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="py-2 text-sm text-gray-900">{{ item.item_name || 'Item #' + item.sa_item_id }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ item.quantity_ordered }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ item.quantity_received }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ formatCurrency(item.unit_cost) }}</td>
                                <td class="py-2 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.total_cost) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <div class="ml-auto w-48 text-right">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(order.total) }}</p>
                        </div>
                    </div>

                    <div v-if="order.notes" class="mt-4 border-t border-gray-200 pt-4">
                        <p class="text-xs text-gray-500">Notes</p>
                        <p class="mt-1 text-sm text-gray-700">{{ order.notes }}</p>
                    </div>
                </div>

                <!-- Comments -->
                <div class="mt-6">
                    <CommentSection type="purchase_order" :id="order.id" />
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
