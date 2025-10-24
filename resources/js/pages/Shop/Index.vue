<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

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
    active_products_count: number;
}

interface Props {
    categories: Category[];
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

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

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

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
    <MemberLayout>
        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                        MyGrow Shop
                    </h1>
                    <p class="text-lg text-gray-600">
                        Earn BP with every purchase! 10-20 BP per K100 spent
                    </p>
                </div>

                <!-- Categories -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div v-for="category in categories" :key="category.id"
                         class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer"
                         @click="router.visit(route('shop.category', category.slug))">
                        <div class="text-4xl mb-2">{{ category.icon }}</div>
                        <h3 class="font-semibold text-gray-900">{{ category.name }}</h3>
                        <p class="text-sm text-gray-500">{{ category.active_products_count }} products</p>
                    </div>
                </div>

                <!-- Featured Products -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Featured Products</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <div v-for="product in products.data.filter(p => p.is_featured)" :key="product.id"
                             class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="aspect-square bg-gradient-to-br from-blue-100 to-emerald-100 flex items-center justify-center">
                                <span class="text-6xl">🎁</span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-1">{{ product.name }}</h3>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ product.description }}</p>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl font-bold text-emerald-600">{{ formatPrice(product.price) }}</span>
                                    <span class="text-sm font-medium text-indigo-600">+{{ calculateBP(product.price, product.bp_value) }} BP</span>
                                </div>
                                <button @click="addToCart(product.id)"
                                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                    {{ isAuthenticated ? 'Add to Cart' : 'Login to Purchase' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Products -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">All Products</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <div v-for="product in products.data" :key="product.id"
                             class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <span class="text-6xl">📦</span>
                            </div>
                            <div class="p-4">
                                <div class="text-xs text-gray-500 mb-1">{{ product.category.name }}</div>
                                <h3 class="font-semibold text-gray-900 mb-1">{{ product.name }}</h3>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ product.description }}</p>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl font-bold text-emerald-600">{{ formatPrice(product.price) }}</span>
                                    <span class="text-sm font-medium text-indigo-600">+{{ calculateBP(product.price, product.bp_value) }} BP</span>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="router.visit(route('shop.product', product.slug))"
                                            class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                                        View
                                    </button>
                                    <button @click="addToCart(product.id)"
                                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                        {{ isAuthenticated ? 'Add' : 'Login' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="products.data.length === 0" class="text-center py-12">
                    <div class="text-6xl mb-4">🛒</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No products available</h3>
                    <p class="text-gray-600">Check back soon for new products!</p>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
