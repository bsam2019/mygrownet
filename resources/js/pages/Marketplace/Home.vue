<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { ref } from 'vue';
import {
    ShoppingBagIcon,
    TruckIcon,
    ShieldCheckIcon,
    CurrencyDollarIcon,
    ArrowRightIcon,
    MagnifyingGlassIcon,
    CheckBadgeIcon,
    ClockIcon,
    UserGroupIcon,
    BuildingStorefrontIcon,
    SparklesIcon,
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
    image_url: string | null;
    parent_id: number | null;
}

defineProps<{
    featuredProducts: Product[];
    latestProducts: { data: Product[] };
    categories: Category[];
    provinces: string[];
}>();

const searchQuery = ref('');

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.get(route('marketplace.search'), { q: searchQuery.value });
    }
};

const trustFeatures = [
    { icon: ShieldCheckIcon, title: 'Verified Sellers', desc: 'KYC verified businesses' },
    { icon: CurrencyDollarIcon, title: 'Escrow Protection', desc: 'Funds held until delivery' },
    { icon: TruckIcon, title: 'Delivery Tracking', desc: 'Track every step' },
    { icon: ClockIcon, title: '7-Day Protection', desc: 'Buyer confirmation window' },
];

const howItWorks = [
    { step: '1', title: 'Search Products', desc: 'Browse thousands of products from verified sellers', icon: MagnifyingGlassIcon },
    { step: '2', title: 'Place Order', desc: 'Add to cart and checkout securely with escrow', icon: ShoppingBagIcon },
    { step: '3', title: 'Receive & Confirm', desc: 'Get your order and release payment to seller', icon: CheckBadgeIcon },
];

const sellerBenefits = [
    { icon: UserGroupIcon, title: 'Reach More Customers', desc: 'Access thousands of buyers across Zambia' },
    { icon: ShieldCheckIcon, title: 'Secure Payments', desc: 'Get paid safely through our escrow system' },
    { icon: BuildingStorefrontIcon, title: 'Easy Store Setup', desc: 'List products in minutes, no tech skills needed' },
    { icon: SparklesIcon, title: 'Grow Your Business', desc: 'Tools and insights to scale your sales' },
];
</script>

<template>
    <Head title="Marketplace - MyGrowNet" />
    
    <MarketplaceLayout>
        <!-- Hero Section with Search -->
        <section class="relative overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
                style="background-image: url('https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1920&q=80');">
            </div>
            
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-orange-600/95 via-amber-600/90 to-orange-500/95"></div>
            
            <!-- Pattern Overlay (Optional) -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-white text-sm mb-6">
                            <ShieldCheckIcon class="h-4 w-4" aria-hidden="true" />
                            Zambia's Trusted Marketplace
                        </div>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 leading-tight">
                            Shop with <span class="text-yellow-300">Trust</span>,<br/>
                            Sell with <span class="text-yellow-300">Confidence</span>
                        </h1>
                        <p class="text-lg text-orange-100 mb-8 max-w-lg">
                            Discover thousands of products from verified sellers. Every transaction protected by our escrow system.
                        </p>
                        
                        <!-- Search Bar -->
                        <form @submit.prevent="handleSearch" class="relative max-w-xl">
                            <div class="flex bg-white rounded-xl shadow-lg overflow-hidden">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search for products, categories, or sellers..."
                                    class="flex-1 px-5 py-4 text-gray-900 placeholder-gray-400 border-0 focus:ring-0 focus:outline-none"
                                />
                                <button
                                    type="submit"
                                    class="px-6 bg-orange-500 hover:bg-orange-600 text-white font-semibold transition-colors flex items-center gap-2"
                                >
                                    <MagnifyingGlassIcon class="h-5 w-5" aria-hidden="true" />
                                    <span class="hidden sm:inline">Search</span>
                                </button>
                            </div>
                        </form>

                        <!-- Quick Links -->
                        <div class="flex flex-wrap gap-2 mt-4">
                            <span class="text-orange-200 text-sm">Popular:</span>
                            <Link 
                                v-for="term in ['Electronics', 'Fashion', 'Food', 'Home']"
                                :key="term"
                                :href="route('marketplace.search', { q: term })"
                                class="text-sm text-white hover:text-yellow-300 transition-colors"
                            >
                                {{ term }}
                            </Link>
                        </div>
                    </div>

                    <!-- Right - Stats/Trust Indicators -->
                    <div class="hidden lg:block">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                                <div class="text-4xl font-bold text-yellow-300">500+</div>
                                <div class="text-orange-100">Verified Sellers</div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                                <div class="text-4xl font-bold text-yellow-300">10K+</div>
                                <div class="text-orange-100">Products Listed</div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                                <div class="text-4xl font-bold text-yellow-300">100%</div>
                                <div class="text-orange-100">Escrow Protected</div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white">
                                <div class="text-4xl font-bold text-yellow-300">10</div>
                                <div class="text-orange-100">Provinces Covered</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trust Bar -->
        <section class="bg-white border-b shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div 
                        v-for="feature in trustFeatures" 
                        :key="feature.title"
                        class="flex items-center gap-3"
                    >
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-orange-100 to-amber-100 rounded-xl flex items-center justify-center">
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

        <!-- Categories with Images -->
        <section class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Explore Categories</h2>
                        <p class="text-gray-500 mt-1">Find exactly what you're looking for</p>
                    </div>
                    <Link 
                        :href="route('marketplace.search')"
                        class="text-orange-600 hover:text-orange-700 font-medium text-sm flex items-center gap-1"
                    >
                        View All
                        <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>
                
                <!-- Main Categories Grid with Images -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <Link
                        v-for="category in categories.filter(c => !c.parent_id)"
                        :key="category.id"
                        :href="route('marketplace.category', category.slug)"
                        class="group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all"
                    >
                        <div class="aspect-[4/3] bg-gradient-to-br from-orange-100 to-amber-50 relative overflow-hidden">
                            <img 
                                v-if="category.image_url"
                                :src="category.image_url"
                                :alt="category.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <span class="text-4xl">{{ category.icon || '📦' }}</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <span class="text-xl mb-1 block">{{ category.icon || '📦' }}</span>
                                <h3 class="font-semibold text-white text-sm leading-tight">{{ category.name }}</h3>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="featuredProducts.length > 0" class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Featured Products</h2>
                        <p class="text-gray-500 mt-1">Handpicked deals from top sellers</p>
                    </div>
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
                        class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:border-orange-200 transition-all duration-300"
                    >
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            <img 
                                v-if="product.primary_image_url"
                                :src="product.primary_image_url"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                <ShoppingBagIcon class="h-16 w-16" aria-hidden="true" />
                            </div>
                            <span 
                                v-if="product.discount_percentage > 0"
                                class="absolute top-3 left-3 px-2 py-1 bg-red-500 text-white text-xs font-bold rounded-lg"
                            >
                                -{{ product.discount_percentage }}%
                            </span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 line-clamp-2 mb-2 group-hover:text-orange-600 transition-colors">
                                {{ product.name }}
                            </h3>
                            <Link
                                :href="route('marketplace.seller.show', product.seller.id)"
                                @click.stop
                                class="flex items-center gap-1 text-xs text-gray-500 mb-3 hover:text-orange-600 transition-colors w-fit"
                            >
                                <span class="text-green-600">{{ product.seller.trust_badge }}</span>
                                <span class="truncate">{{ product.seller.business_name }}</span>
                            </Link>
                            <div class="flex items-baseline gap-2">
                                <span class="text-xl font-bold text-orange-600">{{ product.formatted_price }}</span>
                                <span 
                                    v-if="product.compare_price"
                                    class="text-sm text-gray-400 line-through"
                                >
                                    K{{ (product.compare_price / 100).toFixed(0) }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-16 bg-gradient-to-br from-gray-900 to-gray-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold mb-4">Trade with Confidence</h2>
                    <p class="text-gray-400 max-w-2xl mx-auto">
                        Our escrow system protects both buyers and sellers. Funds are only released when you confirm delivery.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div 
                        v-for="(item, index) in howItWorks" 
                        :key="item.step"
                        class="relative"
                    >
                        <!-- Connector Line -->
                        <div 
                            v-if="index < howItWorks.length - 1"
                            class="hidden md:block absolute top-12 left-1/2 w-full h-0.5 bg-gradient-to-r from-orange-500 to-orange-500/20"
                        ></div>
                        
                        <div class="relative bg-white/5 backdrop-blur-sm rounded-2xl p-8 text-center hover:bg-white/10 transition-colors">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-orange-500/30">
                                <component :is="item.icon" class="h-8 w-8 text-white" aria-hidden="true" />
                            </div>
                            <div class="text-orange-400 font-bold text-sm mb-2">Step {{ item.step }}</div>
                            <h3 class="text-xl font-semibold mb-2">{{ item.title }}</h3>
                            <p class="text-gray-400 text-sm">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <Link 
                        :href="route('marketplace.search')"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors shadow-lg shadow-orange-500/30"
                    >
                        Start Shopping Now
                        <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Latest Products -->
        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Just Arrived</h2>
                        <p class="text-gray-500 mt-1">Fresh listings from our sellers</p>
                    </div>
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
                        class="group bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-lg hover:border-orange-200 transition-all"
                    >
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            <img 
                                v-if="product.primary_image_url"
                                :src="product.primary_image_url"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                <ShoppingBagIcon class="h-10 w-10" aria-hidden="true" />
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1 group-hover:text-orange-600 transition-colors">
                                {{ product.name }}
                            </h3>
                            <span class="text-base font-bold text-orange-600">{{ product.formatted_price }}</span>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Seller CTA Section -->
        <section class="py-16 bg-gradient-to-br from-amber-50 to-orange-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-orange-100 rounded-full text-orange-700 text-sm mb-4">
                            <BuildingStorefrontIcon class="h-4 w-4" aria-hidden="true" />
                            For Sellers
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Ready to Grow Your Business?
                        </h2>
                        <p class="text-gray-600 mb-8">
                            Join hundreds of sellers already thriving on MyGrowNet Marketplace. Get verified, list your products, and reach customers across all 10 provinces of Zambia.
                        </p>
                        
                        <div class="grid sm:grid-cols-2 gap-4 mb-8">
                            <div 
                                v-for="benefit in sellerBenefits"
                                :key="benefit.title"
                                class="flex items-start gap-3"
                            >
                                <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <component :is="benefit.icon" class="h-5 w-5 text-orange-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-sm">{{ benefit.title }}</h4>
                                    <p class="text-xs text-gray-500">{{ benefit.desc }}</p>
                                </div>
                            </div>
                        </div>

                        <Link 
                            :href="route('marketplace.seller.join')"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors shadow-lg shadow-orange-500/20"
                        >
                            Start Selling Today
                            <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                        </Link>
                    </div>

                    <!-- Seller Stats -->
                    <div class="bg-white rounded-3xl shadow-xl p-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Why Sellers Love Us</h3>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <span class="text-gray-600">Average seller rating</span>
                                <span class="font-bold text-gray-900">⭐ 4.8/5</span>
                            </div>
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <span class="text-gray-600">Seller payout success rate</span>
                                <span class="font-bold text-green-600">99.9%</span>
                            </div>
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <span class="text-gray-600">Average time to first sale</span>
                                <span class="font-bold text-gray-900">3 days</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Commission rate</span>
                                <span class="font-bold text-orange-600">Only 5%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="bg-gradient-to-r from-orange-500 to-amber-500 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl md:text-3xl font-bold mb-4">Join Zambia's Most Trusted Marketplace</h2>
                <p class="text-orange-100 mb-8 max-w-2xl mx-auto">
                    Whether you're buying or selling, MyGrowNet Marketplace has you covered with escrow protection on every transaction.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <Link 
                        :href="route('marketplace.search')"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-white text-orange-600 font-semibold rounded-xl hover:bg-orange-50 transition-colors"
                    >
                        Browse Products
                    </Link>
                    <Link 
                        :href="route('marketplace.seller.join')"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-orange-700 text-white font-semibold rounded-xl hover:bg-orange-800 transition-colors"
                    >
                        Become a Seller
                    </Link>
                </div>
            </div>
        </section>
    </MarketplaceLayout>
</template>
