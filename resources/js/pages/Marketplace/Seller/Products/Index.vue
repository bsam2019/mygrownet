<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    CubeIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline';

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    stock_quantity: number;
    status: string;
    views: number;
    primary_image_url: string | null;
    formatted_price: string;
    category: { name: string };
}

interface Category {
    id: number;
    name: string;
}

defineProps<{
    products: { data: Product[]; links: any };
    categories: Category[];
    filters: { status: string; category: string };
}>();

const getStatusColor = (status: string) => ({
    'draft': 'bg-gray-100 text-gray-700',
    'pending': 'bg-yellow-100 text-yellow-700',
    'active': 'bg-green-100 text-green-700',
    'rejected': 'bg-red-100 text-red-700',
    'suspended': 'bg-orange-100 text-orange-700',
}[status] || 'bg-gray-100 text-gray-700');

const deleteProduct = (id: number) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('marketplace.seller.products.destroy', id));
    }
};
</script>

<template>
    <Head title="My Products - Marketplace" />
    
    <MarketplaceLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">My Products</h1>
                <Link 
                    :href="route('marketplace.seller.products.create')"
                    class="flex items-center gap-2 px-4 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Product
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="products.data.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200">
                <CubeIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No products yet</h2>
                <p class="text-gray-500 mb-6">Start selling by adding your first product.</p>
                <Link 
                    :href="route('marketplace.seller.products.create')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Product
                </Link>
            </div>

            <!-- Products Table -->
            <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Product</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Category</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Price</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Stock</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Views</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            <img 
                                                v-if="product.primary_image_url"
                                                :src="product.primary_image_url"
                                                :alt="product.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <CubeIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                                            </div>
                                        </div>
                                        <span class="font-medium text-gray-900 line-clamp-1">{{ product.name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ product.category?.name }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ product.formatted_price }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ product.stock_quantity }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['text-xs px-2 py-1 rounded-full font-medium', getStatusColor(product.status)]">
                                        {{ product.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ product.views }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link 
                                            v-if="product.status === 'active'"
                                            :href="route('marketplace.product', product.slug)"
                                            class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded"
                                            aria-label="View product"
                                        >
                                            <EyeIcon class="h-5 w-5" aria-hidden="true" />
                                        </Link>
                                        <Link 
                                            :href="route('marketplace.seller.products.edit', product.id)"
                                            class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded"
                                            aria-label="Edit product"
                                        >
                                            <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                        </Link>
                                        <button 
                                            @click="deleteProduct(product.id)"
                                            class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded"
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
    </MarketplaceLayout>
</template>
