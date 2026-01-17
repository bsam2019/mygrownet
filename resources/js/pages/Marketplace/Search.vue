<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ShoppingBagIcon,
    FunnelIcon,
    XMarkIcon,
    ChevronDownIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';

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
        trust_badge: string;
        province: string;
    };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    icon?: string;
    parent_id?: number | null;
}

interface Filters {
    q: string;
    category: string;
    subcategory: string;
    province: string;
    min_price: string;
    max_price: string;
    sort: string;
}

const props = defineProps<{
    products: { data: Product[]; links: any; meta: any };
    categories: Category[];
    provinces: string[];
    filters: Filters;
}>();

const showFilters = ref(false);
const localFilters = ref({ ...props.filters, subcategory: props.filters.subcategory || '' });

// Parent categories
const parentCategories = computed(() => props.categories.filter(c => !c.parent_id));

// Get subcategories for selected parent category
const subcategories = computed(() => {
    if (!localFilters.value.category) return [];
    return props.categories.filter(c => c.parent_id === Number(localFilters.value.category));
});

// Track expanded categories
const expandedCategories = ref<Set<number>>(new Set(
    localFilters.value.category ? [Number(localFilters.value.category)] : []
));

const toggleCategory = (categoryId: number) => {
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
};

const selectCategory = (categoryId: number | string) => {
    localFilters.value.category = String(categoryId);
    localFilters.value.subcategory = '';
    if (categoryId) {
        expandedCategories.value.add(Number(categoryId));
    }
};

const selectSubcategory = (subcategoryId: number | string) => {
    localFilters.value.subcategory = String(subcategoryId);
};

// Get subcategories for a specific parent
const getSubcategoriesFor = (parentId: number) => {
    return props.categories.filter(c => c.parent_id === parentId);
};

const sortOptions = [
    { value: 'newest', label: 'Newest First' },
    { value: 'price_low', label: 'Price: Low to High' },
    { value: 'price_high', label: 'Price: High to Low' },
    { value: 'popular', label: 'Most Popular' },
];

const applyFilters = () => {
    const params: Record<string, string> = {};
    
    if (localFilters.value.q) params.q = localFilters.value.q;
    if (localFilters.value.category) params.category = localFilters.value.category;
    if (localFilters.value.subcategory) params.subcategory = localFilters.value.subcategory;
    if (localFilters.value.province) params.province = localFilters.value.province;
    if (localFilters.value.min_price) params.min_price = localFilters.value.min_price;
    if (localFilters.value.max_price) params.max_price = localFilters.value.max_price;
    if (localFilters.value.sort && localFilters.value.sort !== 'newest') params.sort = localFilters.value.sort;

    router.get(route('marketplace.search'), params, {
        preserveState: true,
    });
    showFilters.value = false;
};

const clearFilters = () => {
    localFilters.value = {
        q: '',
        category: '',
        subcategory: '',
        province: '',
        min_price: '',
        max_price: '',
        sort: 'newest',
    };
    expandedCategories.value.clear();
    router.get(route('marketplace.search'));
    showFilters.value = false;
};

const hasActiveFilters = () => {
    return props.filters.category || props.filters.subcategory || props.filters.province || 
           props.filters.min_price || props.filters.max_price;
};
</script>

<template>
    <Head :title="filters.q ? `Search: ${filters.q}` : 'Browse Products'" />
    
    <MarketplaceLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ filters.q ? `Results for "${filters.q}"` : 'All Products' }}
                    </h1>
                    <p class="text-gray-500 mt-1">
                        {{ products.meta?.total || products.data.length }} products found
                    </p>
                </div>
                
                <button 
                    @click="showFilters = true"
                    class="lg:hidden flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    <FunnelIcon class="h-5 w-5" aria-hidden="true" />
                    Filters
                    <span v-if="hasActiveFilters()" class="w-2 h-2 bg-orange-500 rounded-full"></span>
                </button>
            </div>

            <div class="flex gap-8">
                <!-- Sidebar Filters (Desktop) -->
                <aside class="hidden lg:block w-64 flex-shrink-0">
                    <div class="sticky top-24 space-y-6">
                        <!-- Categories -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Category</h3>
                            <div class="space-y-1 max-h-80 overflow-y-auto">
                                <!-- All Categories option -->
                                <button 
                                    @click="selectCategory('')"
                                    :class="[
                                        'w-full text-left px-3 py-2 text-sm rounded-lg transition-colors',
                                        !localFilters.category ? 'bg-orange-100 text-orange-700 font-medium' : 'text-gray-700 hover:bg-gray-100'
                                    ]"
                                >
                                    All Categories
                                </button>
                                
                                <!-- Parent categories with expandable subcategories -->
                                <div v-for="category in parentCategories" :key="category.id">
                                    <div class="flex items-center">
                                        <button
                                            v-if="getSubcategoriesFor(category.id).length > 0"
                                            @click="toggleCategory(category.id)"
                                            class="p-1 hover:bg-gray-100 rounded"
                                            :aria-label="`Toggle ${category.name} subcategories`"
                                        >
                                            <ChevronDownIcon 
                                                v-if="expandedCategories.has(category.id)"
                                                class="h-4 w-4 text-gray-500" 
                                                aria-hidden="true" 
                                            />
                                            <ChevronRightIcon 
                                                v-else
                                                class="h-4 w-4 text-gray-500" 
                                                aria-hidden="true" 
                                            />
                                        </button>
                                        <span v-else class="w-6"></span>
                                        
                                        <button 
                                            @click="selectCategory(category.id)"
                                            :class="[
                                                'flex-1 text-left px-2 py-1.5 text-sm rounded-lg transition-colors',
                                                localFilters.category === String(category.id) && !localFilters.subcategory
                                                    ? 'bg-orange-100 text-orange-700 font-medium' 
                                                    : 'text-gray-700 hover:bg-gray-100'
                                            ]"
                                        >
                                            {{ category.icon }} {{ category.name }}
                                        </button>
                                    </div>
                                    
                                    <!-- Subcategories -->
                                    <div 
                                        v-if="expandedCategories.has(category.id) && getSubcategoriesFor(category.id).length > 0"
                                        class="ml-6 mt-1 space-y-1 border-l-2 border-gray-100 pl-2"
                                    >
                                        <button
                                            v-for="sub in getSubcategoriesFor(category.id)"
                                            :key="sub.id"
                                            @click="localFilters.category = String(category.id); selectSubcategory(sub.id)"
                                            :class="[
                                                'w-full text-left px-2 py-1.5 text-sm rounded-lg transition-colors',
                                                localFilters.subcategory === String(sub.id)
                                                    ? 'bg-orange-100 text-orange-700 font-medium' 
                                                    : 'text-gray-600 hover:bg-gray-100'
                                            ]"
                                        >
                                            {{ sub.icon }} {{ sub.name }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Province -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Location</h3>
                            <select 
                                v-model="localFilters.province"
                                class="w-full border-gray-300 rounded-lg text-sm text-gray-900 bg-white focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">All Provinces</option>
                                <option v-for="province in provinces" :key="province" :value="province">
                                    {{ province }}
                                </option>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Price Range</h3>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="number"
                                    v-model="localFilters.min_price"
                                    placeholder="Min"
                                    class="w-full border-gray-300 rounded-lg text-sm text-gray-900 bg-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500"
                                />
                                <span class="text-gray-400">-</span>
                                <input 
                                    type="number"
                                    v-model="localFilters.max_price"
                                    placeholder="Max"
                                    class="w-full border-gray-300 rounded-lg text-sm text-gray-900 bg-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>
                        </div>

                        <!-- Apply Button -->
                        <div class="flex gap-2">
                            <button 
                                @click="applyFilters"
                                class="flex-1 px-4 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                            >
                                Apply
                            </button>
                            <button 
                                @click="clearFilters"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- Products Grid -->
                <div class="flex-1">
                    <!-- Sort -->
                    <div class="flex items-center justify-end mb-4">
                        <select 
                            v-model="localFilters.sort"
                            @change="applyFilters"
                            class="border-gray-300 rounded-lg text-sm text-gray-900 bg-white focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Empty State -->
                    <div v-if="products.data.length === 0" class="text-center py-16">
                        <ShoppingBagIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">No products found</h2>
                        <p class="text-gray-500 mb-6">Try adjusting your search or filters.</p>
                        <button 
                            @click="clearFilters"
                            class="px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                        >
                            Clear Filters
                        </button>
                    </div>

                    <!-- Products -->
                    <div v-else class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                        <Link
                            v-for="product in products.data"
                            :key="product.id"
                            :href="route('marketplace.product', product.slug)"
                            class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow"
                        >
                            <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                <img 
                                    v-if="product.primary_image_url"
                                    :src="product.primary_image_url"
                                    :alt="product.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <ShoppingBagIcon class="h-10 w-10" aria-hidden="true" />
                                </div>
                                <span 
                                    v-if="product.discount_percentage > 0"
                                    class="absolute top-2 left-2 px-2 py-1 bg-red-500 text-white text-xs font-bold rounded"
                                >
                                    -{{ product.discount_percentage }}%
                                </span>
                            </div>
                            <div class="p-3">
                                <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1 group-hover:text-orange-600">
                                    {{ product.name }}
                                </h3>
                                <Link
                                    :href="route('marketplace.seller.show', product.seller.id)"
                                    @click.stop
                                    class="flex items-center gap-1 text-xs text-gray-500 mb-2 hover:text-orange-600 transition-colors w-fit"
                                >
                                    <span>{{ product.seller.trust_badge }}</span>
                                    <span class="truncate">{{ product.seller.business_name }}</span>
                                </Link>
                                <span class="text-base font-bold text-orange-600">{{ product.formatted_price }}</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.links && products.links.length > 3" class="mt-8 flex justify-center">
                        <nav class="flex items-center gap-1">
                            <template v-for="link in products.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg',
                                        link.active 
                                            ? 'bg-orange-500 text-white' 
                                            : 'text-gray-700 hover:bg-gray-100'
                                    ]"
                                    v-html="link.label"
                                />
                                <span 
                                    v-else
                                    class="px-3 py-2 text-sm text-gray-400"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Filters Drawer -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div 
                    v-if="showFilters" 
                    class="fixed inset-0 z-50 bg-black/50 lg:hidden"
                    @click="showFilters = false"
                />
            </Transition>

            <Transition
                enter-active-class="transition-transform duration-300"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-200"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <div 
                    v-if="showFilters"
                    class="fixed inset-y-0 right-0 w-full max-w-sm z-50 bg-white shadow-xl lg:hidden overflow-y-auto"
                >
                    <div class="p-4 border-b flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Filters</h2>
                        <button @click="showFilters = false" aria-label="Close filters">
                            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>
                    
                    <div class="p-4 space-y-6">
                        <!-- Same filter content as desktop -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Category</h3>
                            <div class="space-y-1 max-h-64 overflow-y-auto">
                                <button 
                                    @click="selectCategory('')"
                                    :class="[
                                        'w-full text-left px-3 py-2 text-sm rounded-lg transition-colors',
                                        !localFilters.category ? 'bg-orange-100 text-orange-700 font-medium' : 'text-gray-700 hover:bg-gray-100'
                                    ]"
                                >
                                    All Categories
                                </button>
                                
                                <div v-for="category in parentCategories" :key="category.id">
                                    <div class="flex items-center">
                                        <button
                                            v-if="getSubcategoriesFor(category.id).length > 0"
                                            @click="toggleCategory(category.id)"
                                            class="p-1 hover:bg-gray-100 rounded"
                                            :aria-label="`Toggle ${category.name} subcategories`"
                                        >
                                            <ChevronDownIcon 
                                                v-if="expandedCategories.has(category.id)"
                                                class="h-4 w-4 text-gray-500" 
                                                aria-hidden="true" 
                                            />
                                            <ChevronRightIcon 
                                                v-else
                                                class="h-4 w-4 text-gray-500" 
                                                aria-hidden="true" 
                                            />
                                        </button>
                                        <span v-else class="w-6"></span>
                                        
                                        <button 
                                            @click="selectCategory(category.id)"
                                            :class="[
                                                'flex-1 text-left px-2 py-1.5 text-sm rounded-lg transition-colors',
                                                localFilters.category === String(category.id) && !localFilters.subcategory
                                                    ? 'bg-orange-100 text-orange-700 font-medium' 
                                                    : 'text-gray-700 hover:bg-gray-100'
                                            ]"
                                        >
                                            {{ category.icon }} {{ category.name }}
                                        </button>
                                    </div>
                                    
                                    <div 
                                        v-if="expandedCategories.has(category.id) && getSubcategoriesFor(category.id).length > 0"
                                        class="ml-6 mt-1 space-y-1 border-l-2 border-gray-100 pl-2"
                                    >
                                        <button
                                            v-for="sub in getSubcategoriesFor(category.id)"
                                            :key="sub.id"
                                            @click="localFilters.category = String(category.id); selectSubcategory(sub.id)"
                                            :class="[
                                                'w-full text-left px-2 py-1.5 text-sm rounded-lg transition-colors',
                                                localFilters.subcategory === String(sub.id)
                                                    ? 'bg-orange-100 text-orange-700 font-medium' 
                                                    : 'text-gray-600 hover:bg-gray-100'
                                            ]"
                                        >
                                            {{ sub.icon }} {{ sub.name }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Location</h3>
                            <select 
                                v-model="localFilters.province"
                                class="w-full border-gray-300 rounded-lg text-sm text-gray-900 bg-white"
                            >
                                <option value="">All Provinces</option>
                                <option v-for="province in provinces" :key="province" :value="province">
                                    {{ province }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Price Range</h3>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="number"
                                    v-model="localFilters.min_price"
                                    placeholder="Min"
                                    class="w-full border-gray-300 rounded-lg text-sm text-gray-900 bg-white placeholder-gray-400"
                                />
                                <span class="text-gray-400">-</span>
                                <input 
                                    type="number"
                                    v-model="localFilters.max_price"
                                    placeholder="Max"
                                    class="w-full border-gray-300 rounded-lg text-sm text-gray-900 bg-white placeholder-gray-400"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t flex gap-2">
                        <button 
                            @click="applyFilters"
                            class="flex-1 px-4 py-3 bg-orange-500 text-white font-medium rounded-lg"
                        >
                            Apply Filters
                        </button>
                        <button 
                            @click="clearFilters"
                            class="px-4 py-3 border border-gray-300 text-gray-700 rounded-lg"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </MarketplaceLayout>
</template>
