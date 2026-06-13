<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { ShoppingBagIcon, FunnelIcon, XMarkIcon, HeartIcon as HeartOutline, ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { HeartIcon as HeartSolid } from '@heroicons/vue/24/solid';
import { growmartImage } from '@/lib/growmart';

interface Category {
    id: number;
    name: string;
    slug: string;
    children?: { id: number; name: string; slug: string }[];
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
    products: { data: Product[]; meta: any };
    categories: Category[];
    filters: { q?: string; category?: string; sort?: string };
    cartCount: number;
    wishlistProductIds: number[];
}

const props = defineProps<Props>();

const showFilters = ref(false);
const expandedCategories = ref<Set<number>>(new Set());
const searchQuery = ref(props.filters?.q || '');

const totalResults = computed(() => props.products?.meta?.total ?? 0);

function search() {
    if (searchQuery.value.trim()) {
        applyFilter('q', searchQuery.value.trim());
    } else if (props.filters?.q) {
        applyFilter('q', '');
    }
}

function clearSearch() {
    searchQuery.value = '';
    applyFilter('q', '');
}

function toggleExpand(catId: number) {
    const s = expandedCategories.value;
    if (s.has(catId)) {
        s.delete(catId);
    } else {
        s.add(catId);
    }
    // Trigger reactivity by reassigning
    expandedCategories.value = new Set(s);
}

function isExpanded(catId: number): boolean {
    return expandedCategories.value.has(catId);
}

function isWishlisted(productId: number): boolean {
    return props.wishlistProductIds.includes(productId);
}

function toggleWishlist(productId: number) {
    router.post(route('growmart.wishlist.toggle'), {
        product_id: productId,
    }, { preserveScroll: true });
}

const applyFilter = (key: string, value: string) => {
    router.get(route('growmart.products.index'), {
        ...props.filters,
        [key]: value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    router.get(route('growmart.products.index'), {}, { preserveState: true });
};

const hasActiveFilters = props.filters?.q || props.filters?.category || props.filters?.sort;
</script>

<template>
    <Head title="Browse Products - GrowMart" />

    <GrowMartLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="mb-4">
                <div class="flex items-center justify-between gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 shrink-0">Products</h1>
                    <div class="flex items-center gap-3">
                        <select @change="applyFilter('sort', ($event.target as HTMLSelectElement).value)" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                            <option value="latest" :selected="filters?.sort === 'latest'">Latest</option>
                            <option value="price_asc" :selected="filters?.sort === 'price_asc'">Price: Low to High</option>
                            <option value="price_desc" :selected="filters?.sort === 'price_desc'">Price: High to Low</option>
                            <option value="name" :selected="filters?.sort === 'name'">Name</option>
                        </select>
                        <button @click="showFilters = !showFilters" class="md:hidden p-2 border border-gray-300 rounded-lg">
                            <FunnelIcon class="h-5 w-5 text-gray-600" />
                        </button>
                    </div>
                </div>

                <!-- Inline search bar -->
                <div class="mt-3 relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        @keydown.enter="search"
                        type="text"
                        placeholder="Search products..."
                        class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                    />
                    <button v-if="searchQuery" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>

                <!-- Filter feedback -->
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                    <p v-if="filters?.q" class="text-gray-500">
                        Showing <span class="font-medium text-gray-900">{{ totalResults }}</span> result{{ totalResults === 1 ? '' : 's' }} for "<span class="font-medium text-gray-900">{{ filters.q }}</span>"
                        <button @click="clearSearch" class="text-gray-400 hover:text-gray-600 ml-1">✕</button>
                    </p>
                    <p v-if="filters?.category" class="text-gray-500">
                        Category: <span class="font-medium text-gray-900">{{ filters.category }}</span>
                        <button @click="applyFilter('category', '')" class="text-gray-400 hover:text-gray-600 ml-1">✕</button>
                    </p>
                </div>
            </div>

            <div class="flex gap-6">
                <!-- Sidebar Filters (desktop) -->
                <div class="w-56 flex-shrink-0 hidden md:block">
                    <div class="sticky top-24 space-y-4">
                        <div v-if="hasActiveFilters">
                            <button @click="clearFilters" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Clear all filters</button>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Categories</h3>
                            <div class="space-y-1">
                                <button @click="applyFilter('category', '')" class="block text-sm text-gray-600 hover:text-emerald-600 w-full text-left" :class="!filters?.category ? 'font-medium text-emerald-700' : ''">All Categories</button>
                                <template v-for="cat in categories" :key="cat.id">
                                    <div class="flex items-center gap-1">
                                        <button v-if="cat.children && cat.children.length > 0" @click="toggleExpand(cat.id)" class="p-0.5 shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                                            <ChevronDownIcon class="h-3.5 w-3.5 transition-transform duration-200" :class="isExpanded(cat.id) ? 'rotate-0' : '-rotate-90'" />
                                        </button>
                                        <span v-else class="w-4 shrink-0"></span>
                                        <button @click="applyFilter('category', cat.slug)" class="block text-sm text-gray-600 hover:text-emerald-600 w-full text-left" :class="filters?.category === cat.slug ? 'font-medium text-emerald-700' : ''">{{ cat.name }}</button>
                                    </div>
                                    <div v-if="isExpanded(cat.id) && cat.children && cat.children.length > 0" class="ml-4 space-y-1 border-l-2 border-gray-100 pl-2">
                                        <button v-for="child in cat.children" :key="child.id" @click="applyFilter('category', child.slug)" class="block text-sm text-gray-500 hover:text-emerald-600 w-full text-left" :class="filters?.category === child.slug ? 'font-medium text-emerald-700' : ''">{{ child.name }}</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="flex-1">
                    <div v-if="products.data.length === 0" class="text-center py-12">
                        <ShoppingBagIcon class="mx-auto h-12 w-12 text-gray-300" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div
                            v-for="product in products.data" :key="product.id"
                            class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-emerald-300 hover:shadow-md transition-all group relative"
                        >
                            <Link :href="route('growmart.products.show', product.slug)">
                                <div class="aspect-square bg-gray-50 relative overflow-hidden">
                                    <img v-if="product.image" :src="growmartImage(product.image)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                        <ShoppingBagIcon class="h-12 w-12" />
                                    </div>
                                    <div v-if="product.has_discount" class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded">-{{ product.discount_percentage }}%</div>
                                </div>
                            </Link>
                            <button @click.prevent.stop="toggleWishlist(product.id)"
                                class="absolute top-2 right-2 p-1.5 bg-white/80 rounded-full hover:bg-white transition-colors shadow-sm">
                                <HeartSolid v-if="isWishlisted(product.id)" class="w-4 h-4 text-red-500" />
                                <HeartOutline v-else class="w-4 h-4 text-gray-400 hover:text-red-400" />
                            </button>
                            <Link :href="route('growmart.products.show', product.slug)">
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
                </div>
            </div>
        </div>

        <!-- Mobile Filter Drawer -->
        <Teleport to="body">
            <div v-if="showFilters" class="fixed inset-0 z-50 md:hidden">
                <div @click="showFilters = false" class="absolute inset-0 bg-black/30"></div>
                <div class="absolute right-0 top-0 bottom-0 w-72 bg-white shadow-xl p-6 overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-gray-900">Filters</h3>
                        <button @click="showFilters = false"><XMarkIcon class="h-5 w-5 text-gray-500" /></button>
                    </div>

                    <div v-if="hasActiveFilters" class="mb-4">
                        <button @click="clearFilters" class="text-sm text-emerald-600 font-medium">Clear all filters</button>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Categories</h4>
                        <div class="space-y-1">
                            <button @click="applyFilter('category', ''); showFilters = false" class="block text-sm text-gray-600 hover:text-emerald-600 w-full text-left" :class="!filters?.category ? 'font-medium text-emerald-700' : ''">All Categories</button>
                            <template v-for="cat in categories" :key="cat.id">
                                <div class="flex items-center gap-1">
                                    <button v-if="cat.children && cat.children.length > 0" @click="toggleExpand(cat.id)" class="p-0.5 shrink-0 text-gray-400 hover:text-gray-600">
                                        <ChevronDownIcon class="h-3.5 w-3.5 transition-transform duration-200" :class="isExpanded(cat.id) ? 'rotate-0' : '-rotate-90'" />
                                    </button>
                                    <span v-else class="w-4 shrink-0"></span>
                                    <button @click="applyFilter('category', cat.slug); showFilters = false" class="block text-sm text-gray-600 hover:text-emerald-600 w-full text-left" :class="filters?.category === cat.slug ? 'font-medium text-emerald-700' : ''">{{ cat.name }}</button>
                                </div>
                                <div v-if="isExpanded(cat.id) && cat.children && cat.children.length > 0" class="ml-4 space-y-1 border-l-2 border-gray-100 pl-2">
                                    <button v-for="child in cat.children" :key="child.id" @click="applyFilter('category', child.slug); showFilters = false" class="block text-sm text-gray-500 hover:text-emerald-600 w-full text-left">{{ child.name }}</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </GrowMartLayout>
</template>
