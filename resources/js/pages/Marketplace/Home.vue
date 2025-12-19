<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ShoppingBagIcon,
    TruckIcon,
    ShieldCheckIcon,
    CurrencyDollarIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    compare_price: number | null;
    primary_image_url: string | null;
    formatted_price: string;
    discount_percentage: number;
    seller: {
        id: number;
        business_name: string;
        trust_badge: string;
    };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
}

defineProps<{
    featuredProducts: Product[];
    latestProducts: { data: Product[] };
    categories: Category[];
    provinces: string[];
}>();

const trustFeatures = [
    { icon: ShieldCheckIcon, title: 'Verified Sellers', desc: 'All sellers are KYC verified' },
    { icon: CurrencyDollarIcon, title: 'Escrow Protection', desc: 'Funds held until delivery confirmed' },
    { icon: TruckIcon, title: 'Delivery Tracking', desc: 'Track your order every step' },
    { icon: ShoppingBagIcon, title: 'Buyer Protection', desc: '7-day confirmation window' },
];

const categoryIcons: Record<string, string> = {
    'Electronics': '📱',
    'Fashion': '👗',
    'Home & Garden': '🏠',
    'Health & Beauty': '💄',
    'Food & Groceries': '🍎',
    'Sports': '⚽',
    'Books': '📚',
    'Automotive': '🚗',
};
</script>

<template>
    <Head title="Marketplace - MyGrowNet" />
    
    <MarketplaceLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
                <div class="max-w-2xl">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">
                        Shop with Trust
                    </h1>
                    <p class="text-xl text-orange-100 mb-8">
                        Zambia's first escrow-protected marketplace. Buy from verified sellers with complete peace of mind.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <Link 
                            :href="route('marketplace.search')"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50 transition-colors"
                        >
                            Start Shopping
                            <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                        </Link>
                        <Link 
                            :href="route('marketplace.seller.register')"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition-colors"
                        >
                            Become a Seller
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trust Features -->
        <section class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div 
                        v-for="feature in trustFeatures" 
                        :key="feature.title"
                        class="flex items-center gap-3"
                    >
                        <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                            <component :is="feature.icon" class="h-6 w-6 text-orange-600" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm">{{ feature.title }}</h3>
                            <p class="text-xs text-gray-500">{{ feature.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Shop by Category</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="route('marketplace.category', category.slug)"
                        class="flex flex-col items-center p-4 bg-white rounded-xl border border-gray-200 hover:border-orange-300 hover:shadow-md transition-all"
                    >
                        <span class="text-3xl mb-2">{{ categoryIcons[category.name] || '📦' }}</span>
                        <span class="text-sm font-medium text-gray-700 text-center">{{ category.name }}</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="featuredProducts.length > 0" class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Featured Products</h2>
                    <Link 
                        :href="route('marketplace.search')"
                        class="text-orange-600 hover:text-orange-700 font-medium text-sm flex items-center gap-1"
                    >
                        View All
                        <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                    <Link
                        v-for="product in featuredProducts"
                        :key="product.id"
                        :href="route('marketplace.product', product.slug)"
                        class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            <img 
                                v-if="product.primary_image_url"
                                :src="product.primary_image_url"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <ShoppingBagIcon class="h-12 w-12" aria-hidden="true" />
                            </div>
                            <span 
                                v-if="product.discount_percentage > 0"
                                class="absolute top-2 left-2 px-2 py-1 bg-red-500 text-white text-xs font-bold rounded"
                            >
                                -{{ product.discount_percentage }}%
                            </span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 line-clamp-2 mb-1 group-hover:text-orange-600">
                                {{ product.name }}
                            </h3>
                            <div class="flex items-center gap-1 text-xs text-gray-500 mb-2">
                                <span>{{ product.seller.trust_badge }}</span>
                                <span>{{ product.seller.business_name }}</span>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-lg font-bold text-orange-600">{{ product.formatted_price }}</span>
                                <span 
                                    v-if="product.compare_price"
                                    class="text-sm text-gray-400 line-through"
                                >
                                    K{{ (product.compare_price / 100).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Latest Products -->
        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Latest Products</h2>
                    <Link 
                        :href="route('marketplace.search', { sort: 'newest' })"
                        class="text-orange-600 hover:text-orange-700 font-medium text-sm flex items-center gap-1"
                    >
                        View All
                        <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                    <Link
                        v-for="product in latestProducts.data"
                        :key="product.id"
                        :href="route('marketplace.product', product.slug)"
                        class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            <img 
                                v-if="product.primary_image_url"
                                :src="product.primary_image_url"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <ShoppingBagIcon class="h-10 w-10" aria-hidden="true" />
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1 group-hover:text-orange-600">
                                {{ product.name }}
                            </h3>
                            <span class="text-base font-bold text-orange-600">{{ product.formatted_price }}</span>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-gradient-to-r from-gray-900 to-gray-800 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold mb-4">Ready to Start Selling?</h2>
                <p class="text-gray-300 mb-8 max-w-2xl mx-auto">
                    Join thousands of sellers on MyGrowNet Marketplace. Get verified, list your products, and reach customers across Zambia.
                </p>
                <Link 
                    :href="route('marketplace.seller.register')"
                    class="inline-flex items-center gap-2 px-8 py-4 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-colors"
                >
                    Register as Seller
                    <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
            </div>
        </section>
    </MarketplaceLayout>
</template>
