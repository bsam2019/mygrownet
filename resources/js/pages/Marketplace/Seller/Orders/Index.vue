<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { ShoppingCartIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Order {
    id: number;
    order_number: string;
    status: string;
    status_label: string;
    total: number;
    formatted_total: string;
    created_at: string;
    buyer: { name: string };
    items: Array<{ product: { name: string } }>;
}

defineProps<{
    orders: { data: Order[]; links: any };
    filters: { status: string };
}>();

const getStatusColor = (status: string) => ({
    'pending': 'bg-yellow-100 text-yellow-800',
    'paid': 'bg-blue-100 text-blue-800',
    'shipped': 'bg-purple-100 text-purple-800',
    'delivered': 'bg-teal-100 text-teal-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-gray-100 text-gray-800',
    'disputed': 'bg-red-100 text-red-800',
}[status] || 'bg-gray-100 text-gray-800');

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-ZM', {
    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
});
</script>

<template>
    <Head title="Orders - Seller Dashboard" />
    <MarketplaceLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('marketplace.seller.dashboard')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
            </div>

            <div v-if="orders.data.length === 0" class="text-center py-16 bg-white rounded-xl border">
                <ShoppingCartIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h2>
                <p class="text-gray-500">Orders will appear here when customers purchase your products.</p>
            </div>

            <div v-else class="bg-white rounded-xl border divide-y">
                <Link v-for="order in orders.data" :key="order.id"
                    :href="route('marketplace.seller.orders.show', order.id)"
                    class="flex items-center justify-between p-4 hover:bg-gray-50">
                    <div>
                        <p class="font-semibold text-gray-900">{{ order.order_number }}</p>
                        <p class="text-sm text-gray-500">{{ order.buyer.name }} • {{ formatDate(order.created_at) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ order.items.length }} item(s)</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">{{ order.formatted_total }}</p>
                        <span :class="['text-xs px-2 py-1 rounded-full', getStatusColor(order.status)]">
                            {{ order.status_label }}
                        </span>
                    </div>
                </Link>
            </div>
        </div>
    </MarketplaceLayout>
</template>
