<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { ShoppingBagIcon } from '@heroicons/vue/24/outline';

interface Order {
    id: number;
    order_number: string;
    status: string;
    status_label: string;
    status_color: string;
    total: number;
    formatted_total: string;
    created_at: string;
    seller: {
        business_name: string;
    };
    items: Array<{
        product: { name: string; primary_image_url: string | null };
        quantity: number;
    }>;
}

defineProps<{
    orders: { data: Order[]; links: any };
    filters: { status: string };
}>();

const getStatusColor = (color: string) => ({
    'yellow': 'bg-yellow-100 text-yellow-800',
    'blue': 'bg-blue-100 text-blue-800',
    'purple': 'bg-purple-100 text-purple-800',
    'teal': 'bg-teal-100 text-teal-800',
    'green': 'bg-green-100 text-green-800',
    'gray': 'bg-gray-100 text-gray-800',
    'red': 'bg-red-100 text-red-800',
    'orange': 'bg-orange-100 text-orange-800',
}[color] || 'bg-gray-100 text-gray-800');

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="My Orders - Marketplace" />
    
    <MarketplaceLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h1>

            <!-- Empty State -->
            <div v-if="orders.data.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200">
                <ShoppingBagIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h2>
                <p class="text-gray-500 mb-6">Start shopping to see your orders here.</p>
                <Link 
                    :href="route('marketplace.home')"
                    class="inline-flex px-6 py-3 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                >
                    Browse Products
                </Link>
            </div>

            <!-- Orders List -->
            <div v-else class="space-y-4">
                <Link
                    v-for="order in orders.data"
                    :key="order.id"
                    :href="route('marketplace.orders.show', order.id)"
                    class="block bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ order.order_number }}</p>
                            <p class="text-sm text-gray-500">{{ order.seller.business_name }}</p>
                        </div>
                        <span :class="['text-xs px-2 py-1 rounded-full font-medium', getStatusColor(order.status_color)]">
                            {{ order.status_label }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 mb-3">
                        <div 
                            v-for="(item, index) in order.items.slice(0, 3)" 
                            :key="index"
                            class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden"
                        >
                            <img 
                                v-if="item.product.primary_image_url"
                                :src="item.product.primary_image_url"
                                :alt="item.product.name"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <span v-if="order.items.length > 3" class="text-sm text-gray-500">
                            +{{ order.items.length - 3 }} more
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ formatDate(order.created_at) }}</span>
                        <span class="font-semibold text-gray-900">{{ order.formatted_total }}</span>
                    </div>
                </Link>
            </div>
        </div>
    </MarketplaceLayout>
</template>
