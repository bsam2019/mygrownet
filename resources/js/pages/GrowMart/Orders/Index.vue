<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { ClipboardDocumentListIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline';
import Pagination from '@/components/Pagination.vue';

interface OrderItem {
    product_name: string;
    quantity: number;
    unit_price_formatted: string;
    subtotal_formatted: string;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    delivery_method: string;
    total: number;
    total_formatted: string;
    item_count: number;
    items?: OrderItem[];
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    orders: {
        data: Order[];
        links?: PaginationLink[];
        meta?: {
            links?: PaginationLink[];
        };
    };
    cartCount: number;
}

const props = defineProps<Props>();

const statusColor = (status: string): string => {
    const map: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        processing: 'bg-indigo-100 text-indigo-800',
        out_for_delivery: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
};

const deliveryLabel = (method: string): string => {
    const map: Record<string, string> = { own_vehicle: 'Own Vehicle', yango: 'Yango Delivery', pickup: 'Store Pickup' };
    return map[method] || method;
};
</script>

<template>
    <Head title="My Orders - GrowMart" />

    <GrowMartLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h1>

            <div v-if="orders.data.length === 0" class="text-center py-12">
                <ClipboardDocumentListIcon class="mx-auto h-16 w-16 text-gray-300" />
                <h2 class="mt-4 text-lg font-medium text-gray-900">No orders yet</h2>
                <p class="mt-1 text-sm text-gray-500">Start shopping to place your first order</p>
                <Link :href="route('growmart.products.index')" class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700">
                    Start Shopping
                </Link>
            </div>

            <div v-else class="space-y-3">
                <Link
                    v-for="order in orders.data" :key="order.id"
                    :href="route('growmart.orders.show', order.id)"
                    class="block bg-white rounded-lg border border-gray-200 p-4 hover:border-emerald-300 hover:shadow-sm transition-all"
                >
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                            <p class="text-xs text-gray-500">{{ order.created_at }}</p>
                        </div>
                        <span :class="statusColor(order.status)" class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">{{ order.status.replace('_', ' ') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">{{ order.item_count }} item(s) • {{ deliveryLabel(order.delivery_method) }}</span>
                        <span class="font-semibold text-gray-900">{{ order.total_formatted }}</span>
                    </div>
                </Link>
            </div>

            <Pagination v-if="orders.links || orders.meta?.links" :links="orders.links || orders.meta?.links || []" />
        </div>
    </GrowMartLayout>
</template>
