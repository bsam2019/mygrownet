<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    PhotoIcon,
    TagIcon,
} from '@heroicons/vue/24/outline';

interface Category {
    id: number;
    name: string;
    color: string | null;
}

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number;
    sku: string | null;
    stock_quantity: number | null;
    category: string | null;
    category_id: number | null;
    category_model?: Category | null;
    // Handle both snake_case and camelCase from Laravel
    categoryModel?: Category | null;
    image_url: string | null;
    is_active: boolean;
}

interface Props {
    products: {
        data: Product[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
        category?: string;
        category_id?: number;
    };
    categories: Category[];
    legacyCategories: string[];
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedCategoryId = ref(props.filters.category_id?.toString() || '');
const selectedLegacyCategory = ref(props.filters.category || '');

// Combine filter value for display
const selectedFilter = computed(() => {
    if (selectedCategoryId.value) return `id:${selectedCategoryId.value}`;
    if (selectedLegacyCategory.value) return `legacy:${selectedLegacyCategory.value}`;
    return '';
});

const applyFilters = () => {
    const params: Record<string, any> = {};
    
    if (search.value) params.search = search.value;
    
    // Parse the selected filter
    if (selectedFilter.value.startsWith('id:')) {
        params.category_id = selectedFilter.value.replace('id:', '');
    } else if (selectedFilter.value.startsWith('legacy:')) {
        params.category = selectedFilter.value.replace('legacy:', '');
    }
    
    router.get('/bizboost/products', params, { preserveState: true });
};

const onFilterChange = (event: Event) => {
    const value = (event.target as HTMLSelectElement).value;
    
    if (value.startsWith('id:')) {
        selectedCategoryId.value = value.replace('id:', '');
        selectedLegacyCategory.value = '';
    } else if (value.startsWith('legacy:')) {
        selectedLegacyCategory.value = value.replace('legacy:', '');
        selectedCategoryId.value = '';
    } else {
        selectedCategoryId.value = '';
        selectedLegacyCategory.value = '';
    }
    
    applyFilters();
};

const getProductCategory = (product: Product): string => {
    // Handle both snake_case (category_model) and camelCase (categoryModel) from Laravel
    const catModel = product.category_model || product.categoryModel;
    if (catModel && typeof catModel === 'object' && catModel.name) {
        return catModel.name;
    }
    if (product.category && typeof product.category === 'string') {
        return product.category;
    }
    return 'Uncategorized';
};

const deleteProduct = (id: number) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(`/bizboost/products/${id}`);
    }
};
</script>

<template>
    <Head title="Products - BizBoost" />
    <BizBoostLayout title="Products">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Products</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your product catalog</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        href="/bizboost/products/categories/manage"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                        <TagIcon class="h-4 w-4" aria-hidden="true" />
                        Categories
                    </Link>
                    <Link
                        href="/bizboost/products/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Add Product
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="relative flex-1">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search products..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-10 focus:border-violet-500 focus:ring-violet-500"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <select
                    :value="selectedFilter"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                    @change="onFilterChange"
                >
                    <option value="">All Categories</option>
                    <optgroup v-if="categories.length" label="Categories">
                        <option v-for="cat in categories" :key="cat.id" :value="`id:${cat.id}`">
                            {{ cat.name }}
                        </option>
                    </optgroup>
                    <optgroup v-if="legacyCategories.length" label="Legacy Categories">
                        <option v-for="cat in legacyCategories" :key="cat" :value="`legacy:${cat}`">
                            {{ cat }} (legacy)
                        </option>
                    </optgroup>
                </select>
            </div>

            <!-- Products Grid -->
            <div v-if="products.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="group relative rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-violet-300 dark:hover:ring-violet-500 transition-all"
                >
                    <!-- Image -->
                    <div class="aspect-square rounded-lg bg-gray-100 dark:bg-gray-700 mb-3 overflow-hidden">
                        <img
                            v-if="product.image_url"
                            :src="product.image_url"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full items-center justify-center">
                            <PhotoIcon class="h-12 w-12 text-gray-300 dark:text-gray-500" aria-hidden="true" />
                        </div>
                    </div>

                    <!-- Info -->
                    <h3 class="font-medium text-gray-900 dark:text-white truncate">{{ product.name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ getProductCategory(product) }}</p>
                    <p class="mt-1 text-lg font-semibold text-violet-600 dark:text-violet-400">K{{ product.price.toLocaleString() }}</p>

                    <!-- Status -->
                    <span
                        :class="[
                            'absolute top-3 right-3 text-xs px-2 py-1 rounded-full',
                            product.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                        ]"
                    >
                        {{ product.is_active ? 'Active' : 'Inactive' }}
                    </span>

                    <!-- Actions -->
                    <div class="mt-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Link
                            :href="`/bizboost/products/${product.id}/edit`"
                            class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                            Edit
                        </Link>
                        <button
                            @click="deleteProduct(product.id)"
                            class="inline-flex items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/30 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50"
                            aria-label="Delete product"
                        >
                            <TrashIcon class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-xl bg-white dark:bg-gray-800 p-12 text-center shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                <PhotoIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" aria-hidden="true" />
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No products yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Get started by adding your first product.</p>
                <Link
                    href="/bizboost/products/create"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Product
                </Link>
            </div>
        </div>
    </BizBoostLayout>
</template>
