<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ShoppingCartIcon,
    ShoppingBagIcon,
    TruckIcon,
    ShieldCheckIcon,
    MinusIcon,
    PlusIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    MapPinIcon,
    StarIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    compare_price: number | null;
    stock_quantity: number;
    images: string[];
    image_urls: string[];
    formatted_price: string;
    formatted_compare_price: string | null;
    discount_percentage: number;
    category: {
        id: number;
        name: string;
        slug: string;
    };
    seller: {
        id: number;
        business_name: string;
        province: string;
        district: string;
        trust_level: string;
        trust_badge: string;
        trust_label: string;
        rating: number;
        total_orders: number;
        user: {
            name: string;
        };
    };
}

const props = defineProps<{
    product: Product;
    relatedProducts: Product[];
}>();

const quantity = ref(1);
const currentImageIndex = ref(0);
const isAddingToCart = ref(false);

const images = computed(() => {
    return props.product.image_urls?.length > 0 
        ? props.product.image_urls 
        : props.product.images?.length > 0
        ? props.product.images
        : [null];
});

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

const nextImage = () => {
    currentImageIndex.value = (currentImageIndex.value + 1) % images.value.length;
};

const prevImage = () => {
    currentImageIndex.value = currentImageIndex.value === 0 
        ? images.value.length - 1 
        : currentImageIndex.value - 1;
};

const addToCart = () => {
    isAddingToCart.value = true;
    router.post(route('marketplace.cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isAddingToCart.value = false;
        },
    });
};

const getTrustLevelColor = (level: string) => {
    return {
        'new': 'bg-gray-100 text-gray-700',
        'verified': 'bg-blue-100 text-blue-700',
        'trusted': 'bg-green-100 text-green-700',
        'top': 'bg-amber-100 text-amber-700',
    }[level] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head :title="product.name + ' - Marketplace'" />
    
    <MarketplaceLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                <Link :href="route('marketplace.home')" class="hover:text-orange-600">Home</Link>
                <span>/</span>
                <Link :href="route('marketplace.category', product.category.slug)" class="hover:text-orange-600">
                    {{ product.category.name }}
                </Link>
                <span>/</span>
                <span class="text-gray-900 truncate">{{ product.name }}</span>
            </nav>

            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
                <!-- Image Gallery -->
                <div>
                    <div class="relative aspect-square bg-gray-100 rounded-2xl overflow-hidden mb-4">
                        <img 
                            v-if="images[currentImageIndex]"
                            :src="images[currentImageIndex]"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            <ShoppingBagIcon class="h-24 w-24" aria-hidden="true" />
                        </div>

                        <!-- Navigation Arrows -->
                        <template v-if="images.length > 1">
                            <button 
                                @click="prevImage"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 rounded-full flex items-center justify-center shadow-lg hover:bg-white"
                                aria-label="Previous image"
                            >
                                <ChevronLeftIcon class="h-6 w-6" aria-hidden="true" />
                            </button>
                            <button 
                                @click="nextImage"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 rounded-full flex items-center justify-center shadow-lg hover:bg-white"
                                aria-label="Next image"
                            >
                                <ChevronRightIcon class="h-6 w-6" aria-hidden="true" />
                            </button>
                        </template>

                        <!-- Discount Badge -->
                        <span 
                            v-if="product.discount_percentage > 0"
                            class="absolute top-4 left-4 px-3 py-1 bg-red-500 text-white text-sm font-bold rounded-lg"
                        >
                            -{{ product.discount_percentage }}% OFF
                        </span>
                    </div>

                    <!-- Thumbnails -->
                    <div v-if="images.length > 1" class="flex gap-2 overflow-x-auto pb-2">
                        <button
                            v-for="(img, index) in images"
                            :key="index"
                            @click="currentImageIndex = index"
                            :class="[
                                'flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-colors',
                                currentImageIndex === index ? 'border-orange-500' : 'border-transparent hover:border-gray-300'
                            ]"
                        >
                            <img 
                                v-if="img"
                                :src="img"
                                :alt="`${product.name} thumbnail ${index + 1}`"
                                class="w-full h-full object-cover"
                            />
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                        {{ product.name }}
                    </h1>

                    <!-- Seller Info -->
                    <Link 
                        :href="route('marketplace.seller.show', product.seller.id)"
                        class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl mb-6 hover:bg-gray-100 transition-colors"
                    >
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-500 rounded-full flex items-center justify-center text-white font-bold">
                            {{ product.seller.business_name.charAt(0) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900">{{ product.seller.business_name }}</span>
                                <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', getTrustLevelColor(product.seller.trust_level)]">
                                    {{ product.seller.trust_badge }} {{ product.seller.trust_label }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ product.seller.province }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <StarIconSolid class="h-4 w-4 text-amber-400" aria-hidden="true" />
                                    {{ product.seller.rating.toFixed(1) }}
                                </span>
                                <span>{{ product.seller.total_orders }} orders</span>
                            </div>
                        </div>
                    </Link>

                    <!-- Price -->
                    <div class="mb-6">
                        <div class="flex items-baseline gap-3">
                            <span class="text-3xl font-bold text-orange-600">{{ product.formatted_price }}</span>
                            <span 
                                v-if="product.formatted_compare_price"
                                class="text-xl text-gray-400 line-through"
                            >
                                {{ product.formatted_compare_price }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ product.stock_quantity > 0 ? `${product.stock_quantity} in stock` : 'Out of stock' }}
                        </p>
                    </div>

                    <!-- Quantity & Add to Cart -->
                    <div v-if="product.stock_quantity > 0" class="space-y-4 mb-8">
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium text-gray-700">Quantity:</span>
                            <div class="flex items-center border border-gray-300 rounded-lg bg-white">
                                <button 
                                    @click="decrementQuantity"
                                    :disabled="quantity <= 1"
                                    class="p-2 text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                    aria-label="Decrease quantity"
                                >
                                    <MinusIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                                <span class="w-12 text-center font-medium text-gray-900">{{ quantity }}</span>
                                <button 
                                    @click="incrementQuantity"
                                    :disabled="quantity >= product.stock_quantity"
                                    class="p-2 text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                    aria-label="Increase quantity"
                                >
                                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <button 
                            @click="addToCart"
                            :disabled="isAddingToCart"
                            class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <ShoppingCartIcon class="h-6 w-6" aria-hidden="true" />
                            {{ isAddingToCart ? 'Adding...' : 'Add to Cart' }}
                        </button>
                    </div>

                    <div v-else class="mb-8">
                        <button 
                            disabled
                            class="w-full px-6 py-4 bg-gray-300 text-gray-500 font-semibold rounded-xl cursor-not-allowed"
                        >
                            Out of Stock
                        </button>
                    </div>

                    <!-- Trust Features -->
                    <div class="grid grid-cols-2 gap-4 p-4 bg-green-50 rounded-xl mb-8">
                        <div class="flex items-center gap-2">
                            <ShieldCheckIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            <span class="text-sm text-green-800">Escrow Protected</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <TruckIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            <span class="text-sm text-green-800">Delivery Tracking</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Description</h2>
                        <div class="prose prose-sm text-gray-600 max-w-none">
                            <p class="whitespace-pre-line">{{ product.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <section v-if="relatedProducts.length > 0" class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <Link
                        v-for="related in relatedProducts"
                        :key="related.id"
                        :href="route('marketplace.product', related.slug)"
                        class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img 
                                v-if="related.primary_image_url"
                                :src="related.primary_image_url"
                                :alt="related.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 line-clamp-2 mb-2 group-hover:text-orange-600">
                                {{ related.name }}
                            </h3>
                            <span class="text-lg font-bold text-orange-600">{{ related.formatted_price }}</span>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </MarketplaceLayout>
</template>
