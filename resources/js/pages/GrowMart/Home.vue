<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { ShoppingBagIcon, MagnifyingGlassIcon, TruckIcon, ShieldCheckIcon, ClockIcon } from '@heroicons/vue/24/outline';
import { growmartImage } from '@/lib/growmart';

interface Category {
    id: number;
    name: string;
    slug: string;
    image?: string;
    children: { id: number; name: string; slug: string }[];
}

interface Product {
    id: number;
    name: string;
    slug: string;
    unit: string;
    price: number;
    price_formatted: string;
    compare_price_formatted?: string;
    has_discount: boolean;
    discount_percentage: number;
    category?: string;
    image?: string;
    stock: number;
}

interface Props {
    categories: Category[];
    featuredProducts: Product[];
    cartCount: number;
}

const props = defineProps<Props>();

const deliveryMethods = [
    { icon: TruckIcon, title: 'Yango Delivery', desc: 'Delivered to your door' },
    { icon: ShieldCheckIcon, title: 'Quality Guaranteed', desc: 'Fresh & quality assured' },
    { icon: ClockIcon, title: 'Same Day', desc: 'Order before 2pm' },
];
</script>

<template>
    <Head title="GrowMart - Online Grocery Supermarket" />

    <GrowMartLayout>
        <!-- Hero -->
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
                <div class="max-w-2xl">
                    <h1 class="text-3xl md:text-5xl font-bold mb-4">Fresh Groceries Delivered to Your Door</h1>
                    <p class="text-emerald-100 text-lg mb-8">Shop from a wide selection of fresh produce, pantry staples, and more. We deliver across Lusaka.</p>
                    <div class="flex gap-3">
                        <Link :href="route('growmart.products.index')" class="inline-flex items-center gap-2 bg-white text-emerald-700 px-6 py-3 rounded-lg font-semibold hover:bg-emerald-50 transition-colors">
                            <ShoppingBagIcon class="h-5 w-5" />
                            Shop Now
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trust Bar -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="grid grid-cols-3 gap-4">
                    <div v-for="item in deliveryMethods" :key="item.title" class="flex items-center gap-3 justify-center">
                        <component :is="item.icon" class="h-6 w-6 text-emerald-600 flex-shrink-0" />
                        <div class="text-sm">
                            <p class="font-medium text-gray-900">{{ item.title }}</p>
                            <p class="text-gray-500">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Shop by Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <Link
                    v-for="cat in categories" :key="cat.id"
                    :href="route('growmart.products.index', { category: cat.slug })"
                    class="bg-white rounded-lg border border-gray-200 p-4 text-center hover:border-emerald-300 hover:shadow-sm transition-all"
                >
                    <div class="w-full aspect-square mb-2 overflow-hidden rounded-lg bg-gray-50">
                        <img v-if="cat.image" :src="growmartImage(cat.image)" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-3xl">🛒</div>
                    </div>
                    <p class="text-sm font-medium text-gray-900">{{ cat.name }}</p>
                </Link>
            </div>
        </div>

        <!-- Featured Products -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Featured Products</h2>
                <Link :href="route('growmart.products.index')" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View All →</Link>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <Link
                    v-for="product in featuredProducts" :key="product.id"
                    :href="route('growmart.products.show', product.slug)"
                    class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-emerald-300 hover:shadow-md transition-all group"
                >
                    <div class="aspect-square bg-gray-50 relative overflow-hidden">
                        <img v-if="product.image" :src="growmartImage(product.image)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                            <ShoppingBagIcon class="h-12 w-12" />
                        </div>
                        <div v-if="product.has_discount" class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded">-{{ product.discount_percentage }}%</div>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-500 mb-0.5">{{ product.category }}</p>
                        <p class="text-sm font-medium text-gray-900 line-clamp-2">{{ product.name }}</p>
                        <p class="text-xs text-gray-400">{{ product.unit }}</p>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="font-bold text-emerald-700">{{ product.price_formatted }}</span>
                            <span v-if="product.has_discount" class="text-xs text-gray-400 line-through">{{ product.compare_price_formatted }}</span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </GrowMartLayout>
</template>
