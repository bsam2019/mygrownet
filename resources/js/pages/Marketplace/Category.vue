<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { ref, computed } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string;
    image_url?: string | null;
    parent_id?: number | null;
    description?: string | null;
    products_count?: number;
    children?: Category[];
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    compare_price: number | null;
    primary_image_url: string | null;
    formatted_price: string;
    discount_percentage: number;
    seller: {
        id: number;
        business_name: string;
        province: string;
        trust_level: string;
    };
}

interface Props {
    category: Category;
    products: {
        data: Product[];
        links: any[];
        meta?: any;
    };
    categories: Category[];
    subcategories?: Category[];
    provinces: string[];
    filters: {
        province: string;
        sort: string;
        subcategory?: string;
    };
}

const props = defineProps<Props>();

const selectedProvince = ref(props.filters.province);
const selectedSort = ref(props.filters.sort);
const selectedSubcategory = ref(props.filters.subcategory || '');

// Get parent categories for sidebar
const parentCategories = computed(() => props.categories.filter(c => !c.parent_id));

// Get subcategories of current category (if it's a parent)
const currentSubcategories = computed(() => props.subcategories || []);

// Check if current category is a parent
const isParentCategory = computed(() => !props.category.parent_id);

// Get parent category if viewing a subcategory
const parentCategory = computed(() => {
    if (props.category.parent_id) {
        return props.categories.find(c => c.id === props.category.parent_id);
    }
    return null;
});

const sortOptions = [
    { value: 'newest', label: 'Newest First' },
    { value: 'price_low', label: 'Price: Low to High' },
    { value: 'price_high', label: 'Price: High to Low' },
    { value: 'popular', label: 'Most Popular' },
];

const applyFilters = () => {
    const params: Record<string, string> = {};
    
    if (selectedProvince.value) params.province = selectedProvince.value;
    if (selectedSort.value) params.sort = selectedSort.value;
    if (selectedSubcategory.value) params.subcategory = selectedSubcategory.value;

    router.get(route('marketplace.category', props.category.slug), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const selectSubcategory = (subcategoryId: string) => {
    selectedSubcategory.value = subcategoryId;
    applyFilters();
};

const clearSubcategoryFilter = () => {
    selectedSubcategory.value = '';
    applyFilters();
};

const getTrustBadge = (level: string) => {
    const badges: Record<string, { icon: string; color: string }> = {
        'new': { icon: '🆕', color: 'text-gray-500' },
        'verified': { icon: '✓', color: 'text-blue-600' },
        'trusted': { icon: '⭐', color: 'text-amber-500' },
        'top': { icon: '👑', color: 'text-purple-600' },
    };
    return badges[level] || badges['new'];
};
</script>

<template>
    <Head :title="`${category.name} - Marketplace`" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Category Header -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Breadcrumb for subcategories -->
                    <div v-if="category.parent_id" class="flex items-center gap-2 text-amber-100 text-sm mb-2">
                        <Link :href="route('marketplace.home')" class="hover:text-white">Home</Link>
                        <span>›</span>
                        <Link 
                            v-for="parent in categories.filter(c => c.id === category.parent_id)"
                            :key="parent.id"
                            :href="route('marketplace.category', parent.slug)" 
                            class="hover:text-white"
                        >
                            {{ parent.name }}
                        </Link>
                        <span>›</span>
                        <span class="text-white">{{ category.name }}</span>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <span class="text-4xl">{{ category.icon }}</span>
                        <div>
                            <h1 class="text-2xl font-bold">{{ category.name }}</h1>
                            <p class="text-amber-100">
                                {{ category.description || `${products.data?.length || 0} products available` }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Sidebar Filters -->
                    <div class="lg:w-64 flex-shrink-0">
                        <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                            <h3 class="font-semibold text-gray-900 mb-4">Filters</h3>
                            
                            <!-- Province Filter -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Province
                                </label>
                                <select
                                    v-model="selectedProvince"
                                    @change="applyFilters"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                >
                                    <option value="">All Provinces</option>
                                    <option v-for="province in provinces" :key="province" :value="province">
                                        {{ province }}
                                    </option>
                                </select>
                            </div>

                            <!-- Sort -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Sort By
                                </label>
                                <select
                                    v-model="selectedSort"
                                    @change="applyFilters"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                >
                                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Subcategories (if parent category) -->
                            <div v-if="currentSubcategories.length > 0" class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Filter by Subcategory</h4>
                                <div class="space-y-1">
                                    <!-- All in this category option -->
                                    <button
                                        @click="clearSubcategoryFilter"
                                        :class="[
                                            'w-full text-left px-3 py-2 text-sm rounded-lg transition-colors',
                                            !selectedSubcategory 
                                                ? 'bg-amber-100 text-amber-700 font-medium' 
                                                : 'text-gray-600 hover:bg-amber-50 hover:text-amber-600'
                                        ]"
                                    >
                                        All {{ category.name }}
                                    </button>
                                    <button
                                        v-for="sub in currentSubcategories"
                                        :key="sub.id"
                                        @click="selectSubcategory(String(sub.id))"
                                        :class="[
                                            'w-full text-left px-3 py-2 text-sm rounded-lg transition-colors',
                                            selectedSubcategory === String(sub.id)
                                                ? 'bg-amber-100 text-amber-700 font-medium' 
                                                : 'text-gray-600 hover:bg-amber-50 hover:text-amber-600'
                                        ]"
                                    >
                                        {{ sub.icon }} {{ sub.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- Other Categories -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">All Categories</h4>
                                <div class="space-y-1">
                                    <Link
                                        v-for="cat in parentCategories.filter(c => c.id !== category.id && c.id !== category.parent_id)"
                                        :key="cat.id"
                                        :href="route('marketplace.category', cat.slug)"
                                        class="block px-3 py-2 text-sm text-gray-600 hover:bg-amber-50 hover:text-amber-600 rounded-lg transition-colors"
                                    >
                                        {{ cat.icon }} {{ cat.name }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="flex-1">
                        <div v-if="products.data?.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link
                                v-for="product in products.data"
                                :key="product.id"
                                :href="route('marketplace.product', product.slug)"
                                class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow group"
                            >
                                <!-- Product Image -->
                                <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                    <img
                                        v-if="product.primary_image_url"
                                        :src="product.primary_image_url"
                                        :alt="product.name"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Discount Badge -->
                                    <div
                                        v-if="product.discount_percentage > 0"
                                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded"
                                    >
                                        -{{ product.discount_percentage }}%
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="p-4">
                                    <h3 class="font-medium text-gray-900 line-clamp-2 mb-2 group-hover:text-amber-600 transition-colors">
                                        {{ product.name }}
                                    </h3>
                                    
                                    <!-- Seller Info -->
                                    <div class="flex items-center gap-1 text-sm text-gray-500 mb-2">
                                        <Link
                                            :href="route('marketplace.seller.show', product.seller.id)"
                                            @click.stop
                                            class="flex items-center gap-1 hover:text-amber-600 transition-colors"
                                        >
                                            <span :class="getTrustBadge(product.seller.trust_level).color">
                                                {{ getTrustBadge(product.seller.trust_level).icon }}
                                            </span>
                                            <span class="truncate">{{ product.seller.business_name }}</span>
                                        </Link>
                                        <span class="text-gray-300">•</span>
                                        <span>{{ product.seller.province }}</span>
                                    </div>

                                    <!-- Price -->
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-lg font-bold text-amber-600">
                                            {{ product.formatted_price }}
                                        </span>
                                        <span
                                            v-if="product.compare_price && product.compare_price > product.price"
                                            class="text-sm text-gray-400 line-through"
                                        >
                                            K{{ (product.compare_price / 100).toFixed(0) }}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
                            <div class="text-6xl mb-4">📦</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                            <p class="text-gray-500 mb-6">
                                There are no products in this category yet.
                            </p>
                            <Link
                                :href="route('marketplace.home')"
                                class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors"
                            >
                                Browse All Products
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
