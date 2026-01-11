<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import {
    UserGroupIcon,
    CubeIcon,
    ShoppingBagIcon,
    ExclamationTriangleIcon,
    StarIcon,
    CurrencyDollarIcon,
    CheckCircleIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface Stats {
    pending_sellers: number;
    pending_products: number;
    active_sellers: number;
    total_products: number;
    total_orders: number;
    open_disputes: number;
    total_reviews: number;
    total_revenue: number;
}

interface Seller {
    id: number;
    business_name: string;
    kyc_status: string;
    created_at: string;
    user: {
        name: string;
        email: string;
    };
}

interface Product {
    id: number;
    name: string;
    status: string;
    created_at: string;
    seller: {
        business_name: string;
    };
}

interface Dispute {
    id: number;
    type: string;
    status: string;
    created_at: string;
    buyer: {
        name: string;
    };
    seller: {
        business_name: string;
    };
}

const props = defineProps<{
    stats: Stats;
    recentSellers: Seller[];
    recentProducts: Product[];
    recentDisputes: Dispute[];
}>();

const formatPrice = (amount: number) => {
    return 'K' + (amount / 100).toFixed(2);
};
</script>

<template>
    <Head title="Marketplace Admin - Dashboard" />
    
    <MarketplaceAdminLayout 
        title="Marketplace Dashboard" 
        :stats="{
            pending_sellers: stats.pending_sellers,
            pending_products: stats.pending_products,
            open_disputes: stats.open_disputes,
            total_reviews: stats.total_reviews
        }"
    >
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Pending Sellers -->
                <Link :href="route('admin.marketplace.sellers.index', { status: 'pending' })" class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <UserGroupIcon class="h-8 w-8 text-orange-600" aria-hidden="true" />
                        <span v-if="stats.pending_sellers > 0" class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded-full">
                            {{ stats.pending_sellers }}
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ stats.pending_sellers }}</p>
                    <p class="text-sm text-gray-600">Pending Sellers</p>
                </Link>

                <!-- Pending Products -->
                <Link :href="route('admin.marketplace.products.index', { status: 'pending' })" class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <CubeIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                        <span v-if="stats.pending_products > 0" class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                            {{ stats.pending_products }}
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ stats.pending_products }}</p>
                    <p class="text-sm text-gray-600">Pending Products</p>
                </Link>

                <!-- Open Disputes -->
                <Link :href="route('admin.marketplace.disputes.index', { status: 'open' })" class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <ExclamationTriangleIcon class="h-8 w-8 text-red-600" aria-hidden="true" />
                        <span v-if="stats.open_disputes > 0" class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                            {{ stats.open_disputes }}
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ stats.open_disputes }}</p>
                    <p class="text-sm text-gray-600">Open Disputes</p>
                </Link>

                <!-- Total Revenue -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <CurrencyDollarIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.total_revenue) }}</p>
                    <p class="text-sm text-gray-600">Total Revenue</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <p class="text-sm text-gray-600">Active Sellers</p>
                    <p class="text-xl font-bold text-gray-900">{{ stats.active_sellers }}</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <p class="text-sm text-gray-600">Total Products</p>
                    <p class="text-xl font-bold text-gray-900">{{ stats.total_products }}</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <p class="text-sm text-gray-600">Total Orders</p>
                    <p class="text-xl font-bold text-gray-900">{{ stats.total_orders }}</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <p class="text-sm text-gray-600">Total Reviews</p>
                    <p class="text-xl font-bold text-gray-900">{{ stats.total_reviews }}</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Sellers -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Recent Seller Applications</h2>
                        <Link :href="route('admin.marketplace.sellers.index')" class="text-sm text-orange-600 hover:text-orange-700">
                            View All
                        </Link>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-if="recentSellers.length === 0" class="p-8 text-center text-gray-500 text-sm">
                            No pending applications
                        </div>
                        <Link
                            v-for="seller in recentSellers"
                            :key="seller.id"
                            :href="route('admin.marketplace.sellers.show', seller.id)"
                            class="p-4 hover:bg-gray-50 transition-colors block"
                        >
                            <p class="font-medium text-gray-900 text-sm">{{ seller.business_name }}</p>
                            <p class="text-xs text-gray-500">{{ seller.user.name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <ClockIcon class="h-3 w-3 text-gray-400" aria-hidden="true" />
                                <span class="text-xs text-gray-500">{{ seller.created_at }}</span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Pending Products</h2>
                        <Link :href="route('admin.marketplace.products.index')" class="text-sm text-orange-600 hover:text-orange-700">
                            View All
                        </Link>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-if="recentProducts.length === 0" class="p-8 text-center text-gray-500 text-sm">
                            No pending products
                        </div>
                        <div
                            v-for="product in recentProducts"
                            :key="product.id"
                            class="p-4 hover:bg-gray-50 transition-colors"
                        >
                            <p class="font-medium text-gray-900 text-sm">{{ product.name }}</p>
                            <p class="text-xs text-gray-500">by {{ product.seller.business_name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <ClockIcon class="h-3 w-3 text-gray-400" aria-hidden="true" />
                                <span class="text-xs text-gray-500">{{ product.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Disputes -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Open Disputes</h2>
                        <Link :href="route('admin.marketplace.disputes.index')" class="text-sm text-orange-600 hover:text-orange-700">
                            View All
                        </Link>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-if="recentDisputes.length === 0" class="p-8 text-center text-gray-500 text-sm">
                            No open disputes
                        </div>
                        <Link
                            v-for="dispute in recentDisputes"
                            :key="dispute.id"
                            :href="route('admin.marketplace.disputes.show', dispute.id)"
                            class="p-4 hover:bg-gray-50 transition-colors block"
                        >
                            <p class="font-medium text-gray-900 text-sm">{{ dispute.type }}</p>
                            <p class="text-xs text-gray-500">{{ dispute.buyer.name }} vs {{ dispute.seller.business_name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <ExclamationTriangleIcon class="h-3 w-3 text-red-500" aria-hidden="true" />
                                <span class="text-xs text-red-600">{{ dispute.status }}</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
    </MarketplaceAdminLayout>
</template>
