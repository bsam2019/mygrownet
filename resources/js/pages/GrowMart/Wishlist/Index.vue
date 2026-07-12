<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { HeartIcon, ShoppingBagIcon, TrashIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { growmartImage } from '@/lib/growmart';

interface WishlistItem {
    id: number;
    product_id: number;
    name: string;
    slug: string;
    unit: string;
    price: number;
    price_formatted: string;
    image: string | null;
    in_stock: boolean;
    created_at: string;
}

const props = defineProps<{
    items: WishlistItem[] | { data: WishlistItem[] };
    cartCount: number;
}>();

// Handle both array and paginated object formats
const wishlistItems = Array.isArray(props.items) ? props.items : props.items.data;

function removeItem(productId: number) {
    router.delete(route('growmart.wishlist.remove'), {
        data: { product_id: productId },
        preserveScroll: true,
    });
}

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.get(route('growmart.products.index'));
    }
}

function addToCart(productId: number) {
    router.post(route('growmart.cart.add'), {
        product_id: productId,
        quantity: 1,
    }, { preserveScroll: true });
}
</script>

<template>
    <Head title="My Wishlist" />

    <GrowMartLayout :cartCount="cartCount">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="flex items-center gap-3 mb-8">
                <HeartIcon class="w-7 h-7 text-red-500" />
                <h1 class="text-2xl font-bold text-gray-900">My Wishlist</h1>
            </div>

            <div v-if="wishlistItems.length === 0" class="text-center py-16">
                <HeartIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h2 class="text-lg font-medium text-gray-600 mb-2">Your wishlist is empty</h2>
                <p class="text-gray-400 mb-6">Save items you love to your wishlist</p>
                <Link :href="route('growmart.products.index')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    <ShoppingBagIcon class="w-5 h-5" />
                    Browse Products
                </Link>
            </div>

            <div v-else class="grid gap-4">
                <div v-for="item in wishlistItems" :key="item.id"
                    class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-200 hover:border-emerald-200 transition-colors">
                    <Link :href="route('growmart.products.show', item.slug)" class="shrink-0">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden">
                            <img v-if="item.image" :src="growmartImage(item.image)" :alt="item.name"
                                class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <ShoppingBagIcon class="w-8 h-8" />
                            </div>
                        </div>
                    </Link>

                    <div class="flex-1 min-w-0">
                        <Link :href="route('growmart.products.show', item.slug)"
                            class="text-sm font-medium text-gray-900 hover:text-emerald-600 line-clamp-1">
                            {{ item.name }}
                        </Link>
                        <p class="text-xs text-gray-500 mt-0.5">{{ item.unit }}</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ item.price_formatted }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button v-if="item.in_stock" @click="addToCart(item.product_id)"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                            Add to Cart
                        </button>
                        <button v-else disabled
                            class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                            Out of Stock
                        </button>
                        <button @click="removeItem(item.product_id)"
                            class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                            <TrashIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button @click="goBack"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors cursor-pointer">
                    <ArrowLeftIcon class="w-4 h-4" />
                    Continue Shopping
                </button>
            </div>
        </div>
    </GrowMartLayout>
</template>
