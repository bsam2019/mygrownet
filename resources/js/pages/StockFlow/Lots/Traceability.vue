<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

interface PurchaseRecord {
    id: number;
    order_number: string;
    order_date: string;
    quantity: number;
}

interface SaleRecord {
    id: number;
    receipt_number: string;
    sale_date: string;
    quantity: number;
    customer: string | null;
}

interface LotDetail {
    id: number;
    lot_number: string;
    item_name: string;
    expiry_date: string | null;
    initial_quantity: number;
    current_quantity: number;
    status: string;
}

interface Props {
    lot: LotDetail;
    purchases: PurchaseRecord[];
    sales: SaleRecord[];
}

defineProps<Props>();
</script>

<template>
    <StockFlowLayout :title="`Traceability - ${lot.lot_number}`">
        <Head :title="`Lot Traceability - ${lot.lot_number} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('stockflow.sub.lots.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Lots</Link>
                </div>

                <!-- Lot Info -->
                <div class="rounded-xl bg-white p-6 shadow-sm mb-6">
                    <h1 class="text-xl font-bold text-gray-900 mb-4">Lot: {{ lot.lot_number }}</h1>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Item</span>
                            <p class="font-medium text-gray-900">{{ lot.item_name }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Status</span>
                            <p class="font-medium" :class="lot.status === 'active' ? 'text-green-600' : 'text-red-600'">{{ lot.status }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Initial Qty</span>
                            <p class="font-medium text-gray-900">{{ lot.initial_quantity }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Current Qty</span>
                            <p class="font-medium text-gray-900">{{ lot.current_quantity }}</p>
                        </div>
                        <div v-if="lot.expiry_date">
                            <span class="text-gray-500">Expiry</span>
                            <p class="font-medium text-gray-900">{{ lot.expiry_date }}</p>
                        </div>
                    </div>
                </div>

                <!-- Purchase Chain -->
                <div class="rounded-xl bg-white p-6 shadow-sm mb-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Purchase Orders (Inbound)</h2>
                    <table v-if="purchases.length" class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 border-b">
                                <th class="pb-2 text-left font-medium">Order #</th>
                                <th class="pb-2 text-left font-medium">Date</th>
                                <th class="pb-2 text-right font-medium">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in purchases" :key="p.id" class="border-b border-gray-50">
                                <td class="py-2 font-medium text-emerald-600">{{ p.order_number }}</td>
                                <td class="py-2 text-gray-700">{{ p.order_date }}</td>
                                <td class="py-2 text-right text-gray-700">{{ p.quantity }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-sm text-gray-400">No purchase orders received this lot.</p>
                </div>

                <!-- Sale Chain -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Sales (Outbound)</h2>
                    <table v-if="sales.length" class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 border-b">
                                <th class="pb-2 text-left font-medium">Receipt #</th>
                                <th class="pb-2 text-left font-medium">Date</th>
                                <th class="pb-2 text-right font-medium">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in sales" :key="s.id" class="border-b border-gray-50">
                                <td class="py-2 font-medium text-emerald-600">{{ s.receipt_number }}</td>
                                <td class="py-2 text-gray-700">{{ s.sale_date }}</td>
                                <td class="py-2 text-right text-gray-700">{{ s.quantity }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-sm text-gray-400">No sales from this lot.</p>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
