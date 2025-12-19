<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ShoppingBagIcon,
    TrashIcon,
    MinusIcon,
    PlusIcon,
    ArrowLeftIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

interface CartItem {
    product_id: number;
    seller_id: number;
    name: string;
    price: number;
    quantity: number;
    max_quantity: number;
    total: number;
    image_url: string | null;
}

interface Cart {
    items: CartItem[];
    item_count: number;
    subtotal: number;
    seller_count: number;
    is_multi_seller: boolean;
}

defineProps<{
    cart: Cart;
}>();

const updateQuantity = (productId: number, quantity: number) => {
    router.put(route('marketplace.cart.update'), {
        product_id: productId,
        quantity,
    }, {
        preserveScroll: true,
    });
};

const removeItem = (productId: number) => {
    router.delete(route('marketplace.cart.remove'), {
        data: { product_id: productId },
        preserveScroll: true,
    });
};

const clearCart = () => {
    if (confirm('Are you sure you want to clear your cart?')) {
        router.delete(route('marketplace.cart.clear'));
    }
};

const formatPrice = (amount: number) => {
    return 'K' + (amount / 100).toFixed(2);
};
</script>

<template>
    <Head title="Shopping Cart - Marketplace" />
    
    <MarketplaceLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
                <Link 
                    :href="route('marketplace.home')"
                    class="flex items-center gap-2 text-orange-600 hover:text-orange-700"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    Continue Shopping
                </Link>
            </div>

            <!-- Empty Cart -->
            <div v-if="cart.items.length === 0" class="text-center py-16">
                <ShoppingBagIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Looks like you haven't added any items yet.</p>
                <Link 
                    :href="route('marketplace.home')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600"
                >
                    Start Shopping
                </Link>
            </div>

            <!-- Cart Items -->
            <div v-else class="space-y-6">
                <!-- Multi-seller Warning -->
                <div v-if="cart.is_multi_seller" class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-amber-800 text-sm">
                        <strong>Note:</strong> Your cart contains items from multiple sellers. 
                        You'll need to checkout separately for each seller.
                    </p>
                </div>

                <!-- Items List -->
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">
                    <div 
                        v-for="item in cart.items" 
                        :key="item.product_id"
                        class="p-4 sm:p-6 flex gap-4"
                    >
                        <!-- Image -->
                        <div class="flex-shrink-0 w-24 h-24 bg-gray-100 rounded-lg overflow-hidden">
                            <img 
                                v-if="item.image_url"
                                :src="item.image_url"
                                :alt="item.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <ShoppingBagIcon class="h-8 w-8" aria-hidden="true" />
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-gray-900 mb-1">{{ item.name }}</h3>
                            <p class="text-orange-600 font-semibold">{{ formatPrice(item.price) }}</p>
                            
                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-4 mt-3">
                                <div class="flex items-center border border-gray-300 rounded-lg">
                                    <button 
                                        @click="updateQuantity(item.product_id, item.quantity - 1)"
                                        :disabled="item.quantity <= 1"
                                        class="p-1.5 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        aria-label="Decrease quantity"
                                    >
                                        <MinusIcon class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                    <span class="w-10 text-center text-sm font-medium">{{ item.quantity }}</span>
                                    <button 
                                        @click="updateQuantity(item.product_id, item.quantity + 1)"
                                        :disabled="item.quantity >= item.max_quantity"
                                        class="p-1.5 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        aria-label="Increase quantity"
                                    >
                                        <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </div>
                                
                                <button 
                                    @click="removeItem(item.product_id)"
                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"
                                    aria-label="Remove item"
                                >
                                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <!-- Item Total -->
                        <div class="text-right">
                            <span class="font-semibold text-gray-900">{{ formatPrice(item.total) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-600">Subtotal ({{ cart.item_count }} items)</span>
                        <span class="text-xl font-bold text-gray-900">{{ formatPrice(cart.subtotal) }}</span>
                    </div>
                    
                    <p class="text-sm text-gray-500 mb-4">
                        Delivery fees calculated at checkout
                    </p>

                    <!-- Trust Badge -->
                    <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg mb-6">
                        <ShieldCheckIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                        <span class="text-sm text-green-800">
                            Your payment is protected by escrow until you confirm delivery
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <Link 
                            :href="route('marketplace.checkout')"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600"
                        >
                            Proceed to Checkout
                        </Link>
                        <button 
                            @click="clearCart"
                            class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                        >
                            Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
