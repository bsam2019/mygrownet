<script setup lang="ts">
import { computed, ref } from 'vue';
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
        icon: string;
    };
}

interface Props {
    product: Product;
    relatedProducts: Product[];
}

const props = defineProps<Props>();
const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const quantity = ref(1);

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(price).replace('ZMW', 'K');
};

const calculateBP = (price: number, bpValue: number, qty: number = 1) => {
    return Math.round((price * qty / 100) * bpValue);
};

const totalPrice = computed(() => props.product.price * quantity.value);
const totalBP = computed(() => calculateBP(props.product.price, props.product.bp_value, quantity.value));

const addToCart = () => {
    if (!isAuthenticated.value) {
        router.visit(route('login'));
        return;
    }
    
    router.post(route('shop.cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            quantity.value = 1;
        }
    });
};

const incrementQuantity = () => {
    if (quantity.value < props.product.stock_quantity) {
        quantity.value++;
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};
</script>


<template>
    <!-- Guest View -->
    <div v-if="!isAuthenticated" class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50">
        <Navigation />
        
        <div class="py-8 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
                        <li class="inline-flex items-center">
                            <a :href="route('shop.index')" class="text-gray-600 hover:text-blue-600 transition-colors">
                                Shop
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <a :href="route('shop.category', product.category.slug)" class="ml-1 text-gray-600 hover:text-blue-600 transition-colors">
                                    {{ product.category.name }}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-1 text-gray-900 font-medium">{{ product.name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>


                <!-- Product Detail -->
                <div class="grid md:grid-cols-2 gap-8 mb-12">
                    <!-- Product Image -->
                    <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-2xl p-12 flex items-center justify-center shadow-lg">
                        <div class="text-center">
                            <span class="text-9xl">📦</span>
                            <div v-if="product.is_featured" class="mt-4 inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-sm font-bold">
                                ⭐ Featured Product
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div>
                        <div class="mb-4">
                            <a :href="route('shop.category', product.category.slug)" 
                               class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                                <span class="mr-2">{{ product.category.icon }}</span>
                                {{ product.category.name }}
                            </a>
                        </div>
                        
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            {{ product.name }}
                        </h1>
                        
                        <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                            {{ product.description }}
                        </p>

                        <!-- Price & BP -->
                        <div class="bg-gradient-to-r from-emerald-50 to-blue-50 rounded-xl p-6 mb-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <div class="text-sm text-gray-600 mb-2">Price</div>
                                    <div class="text-4xl font-bold text-emerald-600">{{ formatPrice(totalPrice) }}</div>
                                    <div v-if="quantity > 1" class="text-sm text-gray-500 mt-1">
                                        {{ formatPrice(product.price) }} × {{ quantity }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600 mb-2">You'll Earn</div>
                                    <div class="text-4xl font-bold text-indigo-600">{{ totalBP }} BP</div>
                                    <div class="text-sm text-gray-500 mt-1">Bonus Points</div>
                                </div>
                            </div>
                        </div>


                        <!-- Quantity & Add to Cart -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <div class="flex items-center space-x-4">
                                    <button @click="decrementQuantity" 
                                            :disabled="quantity <= 1"
                                            class="w-12 h-12 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center font-bold text-xl">
                                        −
                                    </button>
                                    <input v-model.number="quantity" 
                                           type="number" 
                                           min="1" 
                                           :max="product.stock_quantity"
                                           class="w-20 h-12 text-center text-xl font-bold border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                                    <button @click="incrementQuantity" 
                                            :disabled="quantity >= product.stock_quantity"
                                            class="w-12 h-12 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center font-bold text-xl">
                                        +
                                    </button>
                                    <span class="text-sm text-gray-500">
                                        {{ product.stock_quantity }} available
                                    </span>
                                </div>
                            </div>

                            <button @click="addToCart" 
                                    :disabled="product.stock_quantity === 0"
                                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 px-8 rounded-xl hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                {{ product.stock_quantity === 0 ? 'Out of Stock' : (isAuthenticated ? 'Add to Cart' : 'Login to Purchase') }}
                            </button>
                        </div>

                        <!-- Features -->
                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <h3 class="font-semibold text-gray-900 mb-4">What's Included:</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Instant digital delivery</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Earn {{ calculateBP(product.price, product.bp_value) }} BP per purchase</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Lifetime access</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Member support</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>


                <!-- Related Products -->
                <div v-if="relatedProducts.length > 0">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">More from {{ product.category.name }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="related in relatedProducts" :key="related.id"
                             class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer"
                             @click="router.visit(route('shop.product', related.slug))">
                            <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <span class="text-6xl group-hover:scale-110 transition-transform duration-300">📦</span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1">{{ related.name }}</h3>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ related.description }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xl font-bold text-emerald-600">{{ formatPrice(related.price) }}</span>
                                    <span class="text-xs font-medium text-indigo-600">+{{ calculateBP(related.price, related.bp_value) }} BP</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Footer />
    </div>

    <!-- Member View (same structure) -->
    <MemberLayout v-else>
        <div class="py-8 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Same content as guest view but without Navigation/Footer -->
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
                        <li class="inline-flex items-center">
                            <a :href="route('shop.index')" class="text-gray-600 hover:text-blue-600 transition-colors">
                                Shop
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <a :href="route('shop.category', product.category.slug)" class="ml-1 text-gray-600 hover:text-blue-600 transition-colors">
                                    {{ product.category.name }}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-1 text-gray-900 font-medium">{{ product.name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Product Detail (same as guest) -->
                <div class="grid md:grid-cols-2 gap-8 mb-12">
                    <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-2xl p-12 flex items-center justify-center shadow-lg">
                        <div class="text-center">
                            <span class="text-9xl">📦</span>
                            <div v-if="product.is_featured" class="mt-4 inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-sm font-bold">
                                ⭐ Featured Product
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <a :href="route('shop.category', product.category.slug)" 
                               class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                                <span class="mr-2">{{ product.category.icon }}</span>
                                {{ product.category.name }}
                            </a>
                        </div>
                        
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            {{ product.name }}
                        </h1>
                        
                        <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                            {{ product.description }}
                        </p>

                        <div class="bg-gradient-to-r from-emerald-50 to-blue-50 rounded-xl p-6 mb-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <div class="text-sm text-gray-600 mb-2">Price</div>
                                    <div class="text-4xl font-bold text-emerald-600">{{ formatPrice(totalPrice) }}</div>
                                    <div v-if="quantity > 1" class="text-sm text-gray-500 mt-1">
                                        {{ formatPrice(product.price) }} × {{ quantity }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600 mb-2">You'll Earn</div>
                                    <div class="text-4xl font-bold text-indigo-600">{{ totalBP }} BP</div>
                                    <div class="text-sm text-gray-500 mt-1">Bonus Points</div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <div class="flex items-center space-x-4">
                                    <button @click="decrementQuantity" 
                                            :disabled="quantity <= 1"
                                            class="w-12 h-12 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center font-bold text-xl">
                                        −
                                    </button>
                                    <input v-model.number="quantity" 
                                           type="number" 
                                           min="1" 
                                           :max="product.stock_quantity"
                                           class="w-20 h-12 text-center text-xl font-bold border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                                    <button @click="incrementQuantity" 
                                            :disabled="quantity >= product.stock_quantity"
                                            class="w-12 h-12 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center font-bold text-xl">
                                        +
                                    </button>
                                    <span class="text-sm text-gray-500">
                                        {{ product.stock_quantity }} available
                                    </span>
                                </div>
                            </div>

                            <button @click="addToCart" 
                                    :disabled="product.stock_quantity === 0"
                                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 px-8 rounded-xl hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                {{ product.stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart' }}
                            </button>
                        </div>

                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <h3 class="font-semibold text-gray-900 mb-4">What's Included:</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Instant digital delivery</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Earn {{ calculateBP(product.price, product.bp_value) }} BP per purchase</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Lifetime access</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-700">Member support</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div v-if="relatedProducts.length > 0">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">More from {{ product.category.name }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="related in relatedProducts" :key="related.id"
                             class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer"
                             @click="router.visit(route('shop.product', related.slug))">
                            <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <span class="text-6xl group-hover:scale-110 transition-transform duration-300">📦</span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1">{{ related.name }}</h3>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ related.description }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xl font-bold text-emerald-600">{{ formatPrice(related.price) }}</span>
                                    <span class="text-xs font-medium text-indigo-600">+{{ calculateBP(related.price, related.bp_value) }} BP</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
