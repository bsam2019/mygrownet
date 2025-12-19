<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    CubeIcon,
    ShoppingCartIcon,
    CurrencyDollarIcon,
    StarIcon,
    ClockIcon,
    ArrowRightIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

interface Seller {
    id: number;
    business_name: string;
    trust_level: string;
    trust_badge: string;
    trust_label: string;
    kyc_status: string;
    is_active: boolean;
    rating: number;
    total_orders: number;
}

interface Stats {
    total_products: number;
    active_products: number;
    pending_orders: number;
    total_orders: number;
    rating: number;
    pending_balance: number;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    status_label: string;
    total: number;
    formatted_total: string;
    created_at: string;
    buyer: {
        name: string;
    };
}

defineProps<{
    seller: Seller;
    stats: Stats;
    recentOrders: Order[];
}>();

const formatPrice = (amount: number) => {
    return 'K' + (amount / 100).toFixed(2);
};

const getStatusColor = (status: string) => {
    return {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-blue-100 text-blue-800',
        'shipped': 'bg-purple-100 text-purple-800',
        'delivered': 'bg-teal-100 text-teal-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-gray-100 text-gray-800',
        'disputed': 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Seller Dashboard - Marketplace" />
    
    <MarketplaceLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Seller Dashboard</h1>
                    <p class="text-gray-500">Welcome back, {{ seller.business_name }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span :class="[
                        'px-3 py-1 text-sm font-medium rounded-full',
                        seller.kyc_status === 'approved' ? 'bg-green-100 text-green-800' : 
                        seller.kyc_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
                    ]">
                        {{ seller.trust_badge }} {{ seller.trust_label }}
                    </span>
                </div>
            </div>

            <!-- KYC Pending Alert -->
            <div v-if="seller.kyc_status === 'pending'" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl flex items-start gap-3">
                <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600 flex-shrink-0" aria-hidden="true" />
                <div>
                    <h3 class="font-semibold text-yellow-800">Verification Pending</h3>
                    <p class="text-sm text-yellow-700">Your account is under review. You'll be able to list products once verified.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <CubeIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.active_products }}</p>
                            <p class="text-sm text-gray-500">Active Products</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <ShoppingCartIcon class="h-5 w-5 text-orange-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.pending_orders }}</p>
                            <p class="text-sm text-gray-500">Pending Orders</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <CurrencyDollarIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.pending_balance) }}</p>
                            <p class="text-sm text-gray-500">Pending Balance</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <StarIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.rating.toFixed(1) }}</p>
                            <p class="text-sm text-gray-500">Rating ({{ stats.total_orders }} orders)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <Link 
                    :href="route('marketplace.seller.products.create')"
                    class="flex items-center gap-3 p-4 bg-orange-500 text-white rounded-xl hover:bg-orange-600 transition-colors"
                >
                    <CubeIcon class="h-6 w-6" aria-hidden="true" />
                    <span class="font-medium">Add Product</span>
                </Link>
                <Link 
                    :href="route('marketplace.seller.orders.index')"
                    class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                >
                    <ShoppingCartIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                    <span class="font-medium text-gray-900">View Orders</span>
                </Link>
                <Link 
                    :href="route('marketplace.seller.products.index')"
                    class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                >
                    <CubeIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                    <span class="font-medium text-gray-900">Manage Products</span>
                </Link>
                <Link 
                    :href="route('marketplace.seller.profile')"
                    class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                >
                    <StarIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                    <span class="font-medium text-gray-900">Edit Profile</span>
                </Link>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Orders</h2>
                    <Link 
                        :href="route('marketplace.seller.orders.index')"
                        class="text-sm text-orange-600 hover:text-orange-700 flex items-center gap-1"
                    >
                        View All
                        <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>

                <div v-if="recentOrders.length === 0" class="p-8 text-center">
                    <ShoppingCartIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No orders yet</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <Link
                        v-for="order in recentOrders"
                        :key="order.id"
                        :href="route('marketplace.seller.orders.show', order.id)"
                        class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                            <p class="text-sm text-gray-500">{{ order.buyer.name }}</p>
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
        </div>
    </MarketplaceLayout>
</template>
