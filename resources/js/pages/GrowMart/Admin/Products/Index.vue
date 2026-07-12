<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { CubeIcon, PlusIcon, PencilIcon, TrashIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import { growmartImage } from '@/lib/growmart';

interface ProductImage {
    id: number;
    path: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    unit: string;
    price: number;
    compare_price: number | null;
    category: { id: number; name: string } | null;
    images: ProductImage[];
    status: string;
    inventory_sum_quantity: number;
    created_at: string;
}

interface Props {
    products: {
        data: Product[];
        meta: any;
    };
}

const props = defineProps<Props>();

const formatPrice = (ngwee: number) => 'K' + (ngwee / 100).toFixed(2);

const deleteProduct = (product: Product) => {
    if (!confirm(`Delete product "${product.name}"?`)) return;
    router.delete(route('admin.growmart.products.destroy', product.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="GrowMart Products - Admin" />

    <AdminLayout title="GrowMart Products">
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">Manage grocery products</p>
            <Link :href="route('admin.growmart.products.create')" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <PlusIcon class="h-5 w-5" />
                Add Product
            </Link>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <img v-if="product.images?.[0]" :src="growmartImage(product.images[0].path)" class="w-full h-full object-cover" />
                                        <PhotoIcon v-else class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <p class="font-medium text-gray-900">{{ product.name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ product.category?.name || '—' }}</td>
                            <td class="px-6 py-4 text-gray-900 font-medium">{{ formatPrice(product.price) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ product.unit }}</td>
                            <td class="px-6 py-4">
                                <span :class="product.inventory_sum_quantity > 0 ? 'text-gray-900' : 'text-red-600'">{{ product.inventory_sum_quantity }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'bg-green-100 text-green-800': product.status === 'active',
                                    'bg-yellow-100 text-yellow-800': product.status === 'out_of_stock',
                                    'bg-red-100 text-red-800': product.status === 'discontinued',
                                }" class="px-2.5 py-1 rounded-full text-xs font-medium">
                                    {{ product.status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.growmart.products.edit', product.id)" class="text-emerald-600 hover:text-emerald-700">
                                        <PencilIcon class="h-4 w-4" />
                                    </Link>
                                    <button @click="deleteProduct(product)" class="text-red-600 hover:text-red-700">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <CubeIcon class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by adding a new product</p>
                                <Link :href="route('admin.growmart.products.create')" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                                    <PlusIcon class="h-5 w-5" />
                                    Add Product
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
