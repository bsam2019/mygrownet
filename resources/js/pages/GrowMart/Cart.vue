<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { ShoppingBagIcon, TrashIcon, MinusIcon, PlusIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { growmartImage } from '@/lib/growmart';

interface CartItem {
    id: number;
    product_id: number;
    name: string;
    slug: string;
    image?: string;
    unit: string;
    unit_price: number;
    unit_price_formatted: string;
    quantity: number;
    total: number;
    total_formatted: string;
    max_stock: number;
}

interface Cart {
    items: CartItem[];
    item_count: number;
    subtotal: number;
    subtotal_formatted: string;
}

interface Props {
    cart: Cart;
    cartCount: number;
}

const props = defineProps<Props>();

const updateQty = (productId: number, qty: number) => {
    if (qty < 0) return;
    router.put(route('growmart.cart.update'), {
        product_id: productId,
        quantity: qty,
    }, { preserveScroll: true });
};

const removeItem = (productId: number) => {
    router.delete(route('growmart.cart.remove'), {
        data: { product_id: productId },
        preserveScroll: true,
    });
};

const clearCart = () => {
    if (!confirm('Clear your cart?')) return;
    router.delete(route('growmart.cart.clear'), { preserveScroll: true });
};

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.get(route('growmart.products.index'));
    }
};
</script>

<template>
    <Head title="Shopping Cart - GrowMart" />

    <GrowMartLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
                <button v-if="cart.items.length > 0" @click="clearCart" class="text-sm text-red-600 hover:text-red-700 font-medium">Clear Cart</button>
            </div>

            <div v-if="cart.items.length === 0" class="text-center py-12">
                <ShoppingBagIcon class="mx-auto h-16 w-16 text-gray-300" />
                <h2 class="mt-4 text-lg font-medium text-gray-900">Your cart is empty</h2>
                <p class="mt-1 text-sm text-gray-500">Start shopping to add items to your cart</p>
                <Link :href="route('growmart.products.index')" class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700">
                    Browse Products
                </Link>
            </div>

            <div v-else class="space-y-4">
                <div v-for="item in cart.items" :key="item.id" class="bg-white rounded-lg border border-gray-200 p-4 flex items-center gap-4">
                    <div class="w-16 h-16 bg-gray-50 rounded-lg overflow-hidden flex-shrink-0">
                        <img v-if="item.image" :src="growmartImage(item.image)" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300"><ShoppingBagIcon class="h-8 w-8" /></div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <Link :href="route('growmart.products.show', item.slug)" class="text-sm font-medium text-gray-900 hover:text-emerald-600 truncate block">{{ item.name }}</Link>
                        <p class="text-xs text-gray-500">{{ item.unit_price_formatted }} / {{ item.unit }}</p>
                        <p v-if="item.quantity > item.max_stock" class="text-xs text-red-600 font-medium mt-0.5">Only {{ item.max_stock }} in stock</p>
                        <p v-else-if="item.max_stock > 0 && item.max_stock <= 5" class="text-xs text-orange-600 mt-0.5">Low stock ({{ item.max_stock }} left)</p>
                    </div>

                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button @click="updateQty(item.product_id, item.quantity - 1)" class="p-1.5 hover:bg-gray-50"><MinusIcon class="h-4 w-4 text-gray-600" /></button>
                        <span class="px-3 font-medium text-gray-900 text-sm min-w-[2rem] text-center">{{ item.quantity }}</span>
                        <button @click="updateQty(item.product_id, item.quantity + 1)" :disabled="item.quantity >= item.max_stock" class="p-1.5 hover:bg-gray-50 disabled:opacity-30"><PlusIcon class="h-4 w-4 text-gray-600" /></button>
                    </div>

                    <div class="text-right min-w-[80px]">
                        <p class="font-semibold text-gray-900">{{ item.total_formatted }}</p>
                    </div>

                    <button @click="removeItem(item.product_id)" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                        <TrashIcon class="h-5 w-5" />
                    </button>
                </div>

                <!-- Summary -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex justify-between text-lg font-semibold text-gray-900 mb-4">
                        <span>Subtotal ({{ cart.item_count }} items)</span>
                        <span>{{ cart.subtotal_formatted }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Delivery fee calculated at checkout</p>
                    <Link :href="route('growmart.checkout')" class="block w-full text-center bg-emerald-600 text-white py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                        Proceed to Checkout
                    </Link>
                    <button @click="goBack" class="block w-full text-center text-sm text-gray-600 hover:text-emerald-600 mt-3 cursor-pointer">
                        ← Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </GrowMartLayout>
</template>
