<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';

interface Seller {
    id: number;
    business_name: string;
    description: string | null;
    province: string;
    district: string | null;
    trust_level: string;
    kyc_status: string;
    total_orders: number;
    rating: number | null;
    created_at: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    compare_price: number | null;
    images: string[];
}

interface Props {
    seller: Seller;
    products: {
        data: Product[];
        links: any[];
    };
}

const props = defineProps<Props>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(price / 100);
};

const getTrustBadge = (level: string) => {
    const badges: Record<string, { icon: string; label: string; color: string; bg: string }> = {
        'new': { icon: '🆕', label: 'New Seller', color: 'text-gray-600', bg: 'bg-gray-100' },
        'verified': { icon: '✓', label: 'Verified Seller', color: 'text-blue-600', bg: 'bg-blue-100' },
        'trusted': { icon: '⭐', label: 'Trusted Seller', color: 'text-amber-600', bg: 'bg-amber-100' },
        'top': { icon: '👑', label: 'Top Seller', color: 'text-purple-600', bg: 'bg-purple-100' },
    };
    return badges[level] || badges['new'];
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'long',
    });
};
</script>

<template>
    <Head :title="`${seller.business_name} - Marketplace`" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Seller Header -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <!-- Seller Avatar -->
                        <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center text-4xl">
                            🏪
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-2xl md:text-3xl font-bold">{{ seller.business_name }}</h1>
                                <span
                                    :class="[getTrustBadge(seller.trust_level).bg, getTrustBadge(seller.trust_level).color]"
                                    class="px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1"
                                >
                                    {{ getTrustBadge(seller.trust_level).icon }}
                                    {{ getTrustBadge(seller.trust_level).label }}
                                </span>
                            </div>
                            
                            <p class="text-amber-100 mb-4">
                                📍 {{ seller.district ? `${seller.district}, ` : '' }}{{ seller.province }}
                            </p>
                            
                            <div class="flex flex-wrap gap-6 text-sm">
                                <div>
                                    <span class="text-amber-200">Member since</span>
                                    <span class="ml-2 font-medium">{{ formatDate(seller.created_at) }}</span>
                                </div>
                                <div>
                                    <span class="text-amber-200">Orders completed</span>
                                    <span class="ml-2 font-medium">{{ seller.total_orders }}</span>
                                </div>
                                <div v-if="seller.rating">
                                    <span class="text-amber-200">Rating</span>
                                    <span class="ml-2 font-medium">⭐ {{ seller.rating.toFixed(1) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <p v-if="seller.description" class="mt-6 text-amber-50 max-w-3xl">
                        {{ seller.description }}
                    </p>
                </div>
            </div>

            <!-- Products Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    Products ({{ products.data?.length || 0 }})
                </h2>

                <div v-if="products.data?.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                    <Link
                        v-for="product in products.data"
                        :key="product.id"
                        :href="route('marketplace.product', product.slug)"
                        class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow group"
                    >
                        <!-- Product Image -->
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            <img
                                v-if="product.images?.length"
                                :src="product.images[0]"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            
                            <!-- Discount Badge -->
                            <div
                                v-if="product.compare_price && product.compare_price > product.price"
                                class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded"
                            >
                                -{{ Math.round((1 - product.price / product.compare_price) * 100) }}%
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 line-clamp-2 mb-2 group-hover:text-amber-600 transition-colors">
                                {{ product.name }}
                            </h3>

                            <!-- Price -->
                            <div class="flex items-baseline gap-2">
                                <span class="text-lg font-bold text-amber-600">
                                    {{ formatPrice(product.price) }}
                                </span>
                                <span
                                    v-if="product.compare_price && product.compare_price > product.price"
                                    class="text-sm text-gray-400 line-through"
                                >
                                    {{ formatPrice(product.compare_price) }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="text-6xl mb-4">📦</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products yet</h3>
                    <p class="text-gray-500">
                        This seller hasn't listed any products yet.
                    </p>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
