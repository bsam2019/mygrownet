<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    bp_value: number;
    image: string | null;
    stock_quantity: number;
    is_featured: boolean;
    category: {
        id: number;
        name: string;
        slug: string;
    };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string;
    icon: string;
}

interface Props {
    category: Category;
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const props = defineProps<Props>();
const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(price).replace('ZMW', 'K');
};

const calculateBP = (price: number, bpValue: number) => {
    return Math.round((price / 100) * bpValue);
};

const addToCart = (productId: number) => {
    if (!isAuthenticated.value) {
        router.visit(route('login'));
        return;
    }
    
    router.post(route('shop.cart.add'), {
        product_id: productId,
        quantity: 1,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <!-- Guest View -->
    <div v-if="!isAuthenticated" class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50">
        <Navigation />
        
        <!-- Hero Section with Category -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-12 md:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a :href="route('shop.index')" class="text-blue-100 hover:text-white transition-colors">
                                <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                                Shop
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-1 text-white font-medium">{{ category.name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Category Header -->
                <div class="flex items-center space-x-6">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 shadow-xl">
                        <span class="text-7xl">{{ category.icon }}</span>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-4xl md:text-5xl font-bold mb-3">
                            {{ category.name }}
                        </h1>
                        <p class="text-xl text-blue-100 mb-4">
                            {{ category.description }}
                        </p>
                        <div class="flex items-center space-x-6">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                </svg>
                                <span class="text-blue-100">{{ products.total }} {{ products.total === 1 ? 'product' : 'products' }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-emerald-100 font-medium">Earn BP with every purchase!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Products Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="product in products.data" :key="product.id"
                         class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <!-- Product Image -->
                        <div class="relative aspect-square bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-400/10 to-purple-400/10 group-hover:scale-110 transition-transform duration-500"></div>
                            <span class="text-7xl relative z-10 group-hover:scale-110 transition-transform duration-300">📦</span>
                            <!-- BP Badge -->
                            <div class="absolute top-3 right-3 bg-indigo-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                +{{ calculateBP(product.price, product.bp_value) }} BP
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-5">
                            <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-1 group-hover:text-blue-600 transition-colors">
                                {{ product.name }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2 leading-relaxed">
                                {{ product.description }}
                            </p>
                            
                            <!-- Price -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <div class="text-xs text-gray-500 mb-1">Price</div>
                                    <div class="text-2xl font-bold text-emerald-600">{{ formatPrice(product.price) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 mb-1">You Earn</div>
                                    <div class="text-lg font-bold text-indigo-600">{{ calculateBP(product.price, product.bp_value) }} BP</div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button @click="router.visit(route('shop.product', product.slug))"
                                        class="flex-1 bg-gray-100 text-gray-700 py-2.5 px-4 rounded-lg hover:bg-gray-200 transition-all duration-200 font-medium text-sm">
                                    Details
                                </button>
                                <button @click="addToCart(product.id)"
                                        class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-2.5 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-medium text-sm shadow-md hover:shadow-lg">
                                    {{ isAuthenticated ? 'Add to Cart' : 'Login' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="products.data.length === 0" class="text-center py-12">
                    <div class="text-6xl mb-4">{{ category.icon }}</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No products in this category yet</h3>
                    <p class="text-gray-600 mb-6">Check back soon for new products!</p>
                    <a :href="route('shop.index')" class="text-blue-600 hover:text-blue-700 font-medium">
                        ← Back to Shop
                    </a>
                </div>
            </div>
        </div>
        <Footer />
    </div>

    <!-- Member View -->
    <MemberLayout v-else>
        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a :href="route('shop.index')" class="text-gray-600 hover:text-blue-600 transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                                Shop
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-1 text-gray-900 font-medium">{{ category.name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Category Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8 mb-8 shadow-sm">
                    <div class="flex items-center space-x-6">
                        <div class="bg-white rounded-xl p-5 shadow-md">
                            <span class="text-6xl">{{ category.icon }}</span>
                        </div>
                        <div class="flex-1">
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                                {{ category.name }}
                            </h1>
                            <p class="text-lg text-gray-600 mb-3">
                                {{ category.description }}
                            </p>
                            <div class="flex items-center space-x-6 text-sm">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                    </svg>
                                    <span class="text-gray-700 font-medium">{{ products.total }} {{ products.total === 1 ? 'product' : 'products' }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-emerald-700 font-medium">Earn 10-20 BP per K100 spent</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="product in products.data" :key="product.id"
                         class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <!-- Product Image -->
                        <div class="relative aspect-square bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-400/10 to-purple-400/10 group-hover:scale-110 transition-transform duration-500"></div>
                            <span class="text-7xl relative z-10 group-hover:scale-110 transition-transform duration-300">📦</span>
                            <!-- BP Badge -->
                            <div class="absolute top-3 right-3 bg-indigo-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                +{{ calculateBP(product.price, product.bp_value) }} BP
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-5">
                            <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-1 group-hover:text-blue-600 transition-colors">
                                {{ product.name }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2 leading-relaxed">
                                {{ product.description }}
                            </p>
                            
                            <!-- Price -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <div class="text-xs text-gray-500 mb-1">Price</div>
                                    <div class="text-2xl font-bold text-emerald-600">{{ formatPrice(product.price) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 mb-1">You Earn</div>
                                    <div class="text-lg font-bold text-indigo-600">{{ calculateBP(product.price, product.bp_value) }} BP</div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button @click="router.visit(route('shop.product', product.slug))"
                                        class="flex-1 bg-gray-100 text-gray-700 py-2.5 px-4 rounded-lg hover:bg-gray-200 transition-all duration-200 font-medium text-sm">
                                    Details
                                </button>
                                <button @click="addToCart(product.id)"
                                        class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-2.5 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-medium text-sm shadow-md hover:shadow-lg">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="products.data.length === 0" class="text-center py-20">
                    <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-full w-32 h-32 flex items-center justify-center mx-auto mb-6">
                        <span class="text-7xl">{{ category.icon }}</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">No products available yet</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">
                        We're working on adding amazing products to this category. Check back soon!
                    </p>
                    <a :href="route('shop.index')" 
                       class="inline-flex items-center space-x-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back to Shop</span>
                    </a>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
