<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    short_description: string | null;
    price: number;
    compare_price: number | null;
    stock_quantity: number;
    track_stock: boolean;
    sku: string | null;
    category: string | null;
    images: string[];
    is_active: boolean;
    is_featured: boolean;
}

const props = defineProps<{
    site: Site;
    product: Product;
    categories: string[];
}>();

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    short_description: props.product.short_description || '',
    price: (props.product.price / 100).toString(),
    compare_price: props.product.compare_price ? (props.product.compare_price / 100).toString() : '',
    stock_quantity: props.product.stock_quantity,
    track_stock: props.product.track_stock,
    sku: props.product.sku || '',
    category: props.product.category || '',
    images: props.product.images || [],
    is_active: props.product.is_active,
    is_featured: props.product.is_featured,
});

const submit = () => {
    form.put(route('growbuilder.products.update', [props.site.id, props.product.id]));
};

const deleteProduct = () => {
    if (confirm('Are you sure you want to delete this product?')) {
        form.delete(route('growbuilder.products.destroy', [props.site.id, props.product.id]));
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Edit ${product.name} - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.products.index', site.id)"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Products
                    </Link>

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                            <p class="text-sm text-gray-500">{{ site.name }}</p>
                        </div>
                        <button
                            @click="deleteProduct"
                            class="inline-flex items-center gap-1 px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg"
                        >
                            <TrashIcon class="h-4 w-4" aria-hidden="true" />
                            Delete
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Details</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Name *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Short Description
                                </label>
                                <input
                                    id="short_description"
                                    v-model="form.short_description"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    maxlength="500"
                                />
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Full Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                ></textarea>
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                    Category
                                </label>
                                <input
                                    id="category"
                                    v-model="form.category"
                                    type="text"
                                    list="categories"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                />
                                <datalist id="categories">
                                    <option v-for="cat in categories" :key="cat" :value="cat" />
                                </datalist>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h2>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Price (K) *
                                </label>
                                <input
                                    id="price"
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                            </div>

                            <div>
                                <label for="compare_price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Compare Price (K)
                                </label>
                                <input
                                    id="compare_price"
                                    v-model="form.compare_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Inventory</h2>

                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <input
                                    id="track_stock"
                                    v-model="form.track_stock"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label for="track_stock" class="text-sm text-gray-700">
                                    Track stock quantity
                                </label>
                            </div>

                            <div v-if="form.track_stock" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                        Stock Quantity
                                    </label>
                                    <input
                                        id="stock_quantity"
                                        v-model="form.stock_quantity"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">
                                        SKU
                                    </label>
                                    <input
                                        id="sku"
                                        v-model="form.sku"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>

                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label for="is_active" class="text-sm text-gray-700">
                                    Active (visible on store)
                                </label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    id="is_featured"
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label for="is_featured" class="text-sm text-gray-700">
                                    Featured product
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <Link
                            :href="route('growbuilder.products.index', site.id)"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
