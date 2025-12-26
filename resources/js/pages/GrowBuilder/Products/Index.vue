<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    compare_price: number | null;
    stock_quantity: number;
    track_stock: boolean;
    category: string | null;
    images: string[];
    is_active: boolean;
    is_featured: boolean;
    formatted_price: string;
    discount_percentage: number;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

defineProps<{
    site: Site;
    products: {
        data: Product[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}>();

const deleteProduct = (productId: number) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('growbuilder.products.destroy', { siteId: route().params.siteId, productId }));
    }
};

const formatPrice = (priceInNgwee: number): string => {
    return 'K' + (priceInNgwee / 100).toFixed(2);
};
</script>

<template>
    <AppLayout>
        <Head :title="`Products - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Sites
                    </Link>

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Products</h1>
                            <p class="text-sm text-gray-500">{{ site.name }}</p>
                        </div>
                        <Link
                            :href="route('growbuilder.products.create', site.id)"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Add Product
                        </Link>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div v-if="products.data.length === 0" class="text-center py-12">
                        <p class="text-gray-500 mb-4">No products yet</p>
                        <Link
                            :href="route('growbuilder.products.create', site.id)"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Add Your First Product
                        </Link>
                    </div>

                    <table v-else class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                            <img
                                                v-if="product.images?.[0]"
                                                :src="product.images[0]"
                                                :alt="product.name"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ product.name }}</p>
                                            <p v-if="product.category" class="text-sm text-gray-500">{{ product.category }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ formatPrice(product.price) }}</p>
                                    <p v-if="product.compare_price" class="text-sm text-gray-500 line-through">
                                        {{ formatPrice(product.compare_price) }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="!product.track_stock" class="text-gray-500">∞</span>
                                    <span v-else :class="product.stock_quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ product.stock_quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ product.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span
                                        v-if="product.is_featured"
                                        class="ml-1 px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800"
                                    >
                                        Featured
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('growbuilder.products.edit', { siteId: site.id, productId: product.id })"
                                            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                                            aria-label="Edit product"
                                        >
                                            <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                                        </Link>
                                        <button
                                            type="button"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                            @click="deleteProduct(product.id)"
                                            aria-label="Delete product"
                                        >
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
