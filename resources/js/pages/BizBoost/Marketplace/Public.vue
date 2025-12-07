<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    MapPinIcon,
    BuildingStorefrontIcon,
    FunnelIcon,
    SparklesIcon,
    ShoppingBagIcon,
    XMarkIcon,
    ChevronRightIcon,
    StarIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarSolidIcon } from '@heroicons/vue/24/solid';

interface Business {
    id: number;
    name: string;
    slug: string;
    logo_url: string | null;
    category: string;
    city: string | null;
    description: string | null;
    tagline: string | null;
    products_count: number;
}

interface PaginatedBusinesses {
    data: Business[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    businesses: PaginatedBusinesses;
    featuredBusinesses: Business[];
    categories: string[];
    categoryCounts: Record<string, number>;
    cities: string[];
    filters: {
        search: string;
        category: string;
        city: string;
    };
    totalBusinesses: number;
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search);
const selectedCategory = ref(props.filters.category);
const selectedCity = ref(props.filters.city);
const showFilters = ref(false);

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

const applyFilters = () => {
    router.get('/marketplace', {
        search: searchQuery.value || undefined,
        category: selectedCategory.value || undefined,
        city: selectedCity.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedCity.value = '';
    router.get('/marketplace');
};

const hasActiveFilters = computed(() => 
    searchQuery.value || selectedCategory.value || selectedCity.value
);

// Category icons mapping
const categoryIcons: Record<string, string> = {
    'Retail': '🛍️',
    'Food & Beverage': '🍽️',
    'Services': '🔧',
    'Health & Beauty': '💆',
    'Fashion': '👗',
    'Technology': '💻',
    'Agriculture': '🌾',
    'Manufacturing': '🏭',
    'Education': '📚',
    'Other': '📦',
};

const getCategoryIcon = (category: string) => categoryIcons[category] || '📦';
</script>

<template>
    <Head title="MyGrowNet Marketplace - Discover Local Businesses" />

    <div class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-50">
        <!-- Hero Section -->
        <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-violet-700 to-indigo-800">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)" />
                </svg>
            </div>
            
            <!-- Floating Elements -->
            <div class="absolute top-20 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute bottom-10 right-20 w-32 h-32 bg-pink-500/20 rounded-full blur-2xl"></div>
            <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-indigo-400/20 rounded-full blur-xl"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
                <div class="text-center">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
                        <SparklesIcon class="h-4 w-4 text-amber-300" aria-hidden="true" />
                        <span class="text-sm font-medium text-white">{{ totalBusinesses }} Businesses Listed</span>
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 tracking-tight">
                        MyGrowNet
                        <span class="bg-gradient-to-r from-amber-300 to-pink-300 bg-clip-text text-transparent">
                            Marketplace
                        </span>
                    </h1>
                    <p class="text-lg md:text-xl text-violet-100 max-w-2xl mx-auto mb-8">
                        Discover amazing local businesses, connect with entrepreneurs, and support your community
                    </p>

                    <!-- Search Bar -->
                    <div class="max-w-2xl mx-auto">
                        <div class="relative flex items-center bg-white rounded-2xl shadow-2xl shadow-violet-900/30 p-2">
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search businesses, products, services..."
                                    class="w-full pl-12 pr-4 py-3 border-0 focus:ring-0 text-gray-900 placeholder-gray-400 rounded-xl"
                                />
                            </div>
                            <button
                                @click="showFilters = !showFilters"
                                :class="[
                                    'p-3 rounded-xl transition-colors mr-2',
                                    hasActiveFilters ? 'bg-violet-100 text-violet-600' : 'hover:bg-gray-100 text-gray-500'
                                ]"
                                aria-label="Toggle filters"
                            >
                                <FunnelIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                            <button
                                @click="applyFilters"
                                class="px-6 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-violet-700 hover:to-indigo-700 transition-all shadow-lg shadow-violet-500/30"
                            >
                                Search
                            </button>
                        </div>

                        <!-- Filter Panel -->
                        <div v-if="showFilters" class="mt-4 p-4 bg-white rounded-2xl shadow-xl">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                    <select
                                        v-model="selectedCategory"
                                        @change="applyFilters"
                                        class="w-full rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500"
                                    >
                                        <option value="">All Categories</option>
                                        <option v-for="cat in categories" :key="cat" :value="cat">
                                            {{ getCategoryIcon(cat) }} {{ cat }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                    <select
                                        v-model="selectedCity"
                                        @change="applyFilters"
                                        class="w-full rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500"
                                    >
                                        <option value="">All Cities</option>
                                        <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                                    </select>
                                </div>
                            </div>
                            <button
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="mt-4 text-sm text-violet-600 hover:text-violet-700 font-medium"
                            >
                                Clear all filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wave Decoration -->
            <svg class="absolute bottom-0 left-0 right-0 w-full h-16 text-slate-50" viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path fill="currentColor" d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,64C960,75,1056,85,1152,80C1248,75,1344,53,1392,42.7L1440,32L1440,120L0,120Z"></path>
            </svg>
        </div>

        <!-- Categories Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Browse by Category</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="selectedCategory = cat; applyFilters()"
                    :class="[
                        'group relative p-4 rounded-2xl border-2 transition-all duration-300 text-left',
                        selectedCategory === cat
                            ? 'border-violet-500 bg-violet-50 shadow-lg shadow-violet-500/20'
                            : 'border-gray-100 bg-white hover:border-violet-200 hover:shadow-md'
                    ]"
                >
                    <span class="text-3xl mb-2 block">{{ getCategoryIcon(cat) }}</span>
                    <span class="font-semibold text-gray-900 block">{{ cat }}</span>
                    <span class="text-sm text-gray-500">{{ categoryCounts[cat] || 0 }} businesses</span>
                </button>
            </div>
        </div>

        <!-- Featured Businesses -->
        <div v-if="featuredBusinesses.length > 0 && !hasActiveFilters" class="bg-gradient-to-r from-violet-50 to-indigo-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Featured Businesses</h2>
                        <p class="text-gray-600 mt-1">Top businesses with the most products</p>
                    </div>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <a
                        v-for="business in featuredBusinesses"
                        :key="business.id"
                        :href="`/biz/${business.slug}`"
                        class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100"
                    >
                        <!-- Header with gradient -->
                        <div class="relative h-32 bg-gradient-to-br from-violet-500 to-indigo-600 p-4">
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-white/20 backdrop-blur-sm text-white text-xs font-medium">
                                    <StarSolidIcon class="h-3 w-3 text-amber-300" aria-hidden="true" />
                                    Featured
                                </span>
                            </div>
                            <!-- Logo -->
                            <div class="absolute -bottom-8 left-4">
                                <div class="w-20 h-20 rounded-2xl bg-white shadow-lg overflow-hidden ring-4 ring-white">
                                    <img
                                        v-if="business.logo_url"
                                        :src="business.logo_url"
                                        :alt="business.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full bg-gradient-to-br from-violet-100 to-indigo-100 flex items-center justify-center">
                                        <span class="text-2xl font-bold text-violet-600">{{ business.name[0] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-12 pb-5 px-4">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="font-bold text-gray-900 text-lg group-hover:text-violet-600 transition-colors">
                                    {{ business.name }}
                                </h3>
                            </div>
                            
                            <p v-if="business.tagline" class="text-sm text-gray-500 line-clamp-1 mb-3">
                                {{ business.tagline }}
                            </p>

                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-violet-100 text-violet-700 text-xs font-medium">
                                    {{ business.category }}
                                </span>
                                <div class="flex items-center gap-3 text-sm text-gray-500">
                                    <span v-if="business.city" class="flex items-center gap-1">
                                        <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ business.city }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <ShoppingBagIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ business.products_count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- All Businesses Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <!-- Active Filters Display -->
            <div v-if="hasActiveFilters" class="mb-6 flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-500">Filters:</span>
                <span
                    v-if="selectedCategory"
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-sm"
                >
                    {{ getCategoryIcon(selectedCategory) }} {{ selectedCategory }}
                    <button @click="selectedCategory = ''; applyFilters()" class="ml-1 hover:text-violet-900">
                        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                </span>
                <span
                    v-if="selectedCity"
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm"
                >
                    <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                    {{ selectedCity }}
                    <button @click="selectedCity = ''; applyFilters()" class="ml-1 hover:text-indigo-900">
                        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                </span>
                <span
                    v-if="searchQuery"
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm"
                >
                    "{{ searchQuery }}"
                    <button @click="searchQuery = ''; applyFilters()" class="ml-1 hover:text-gray-900">
                        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                </span>
                <button @click="clearFilters" class="text-sm text-violet-600 hover:text-violet-700 font-medium ml-2">
                    Clear all
                </button>
            </div>

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ hasActiveFilters ? 'Search Results' : 'All Businesses' }}
                    </h2>
                    <p class="text-gray-600 mt-1">{{ businesses.total }} businesses found</p>
                </div>
            </div>

            <!-- Business Grid -->
            <div v-if="businesses.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <a
                    v-for="business in businesses.data"
                    :key="business.id"
                    :href="`/biz/${business.slug}`"
                    class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100"
                >
                    <!-- Logo/Header -->
                    <div class="aspect-[4/3] bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center relative overflow-hidden">
                        <img
                            v-if="business.logo_url"
                            :src="business.logo_url"
                            :alt="business.name"
                            class="w-24 h-24 object-contain group-hover:scale-110 transition-transform duration-300"
                        />
                        <div v-else class="w-24 h-24 rounded-2xl bg-gradient-to-br from-violet-100 to-indigo-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-3xl font-bold text-violet-600">{{ business.name[0] }}</span>
                        </div>
                        
                        <!-- Category Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm text-gray-700 text-xs font-medium shadow-sm">
                                {{ getCategoryIcon(business.category) }} {{ business.category }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-gray-900 group-hover:text-violet-600 transition-colors mb-1">
                            {{ business.name }}
                        </h3>
                        
                        <p v-if="business.tagline || business.description" class="text-sm text-gray-500 line-clamp-2 mb-3">
                            {{ business.tagline || business.description }}
                        </p>

                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span v-if="business.city" class="flex items-center gap-1">
                                <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                                {{ business.city }}
                            </span>
                            <span class="flex items-center gap-1">
                                <ShoppingBagIcon class="h-4 w-4" aria-hidden="true" />
                                {{ business.products_count }} products
                            </span>
                        </div>

                        <!-- View Button -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <span class="inline-flex items-center gap-2 text-sm font-medium text-violet-600 group-hover:text-violet-700">
                                View Business
                                <ArrowRightIcon class="h-4 w-4 group-hover:translate-x-1 transition-transform" aria-hidden="true" />
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-white rounded-2xl border border-gray-100">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-violet-100 flex items-center justify-center">
                    <BuildingStorefrontIcon class="h-10 w-10 text-violet-600" aria-hidden="true" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No businesses found</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    We couldn't find any businesses matching your criteria. Try adjusting your filters or search terms.
                </p>
                <button
                    @click="clearFilters"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-violet-600 text-white font-semibold rounded-xl hover:bg-violet-700 transition-colors"
                >
                    Clear all filters
                </button>
            </div>

            <!-- Pagination -->
            <div v-if="businesses.last_page > 1" class="mt-12 flex justify-center">
                <nav class="flex items-center gap-2">
                    <template v-for="link in businesses.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm font-medium transition-colors',
                                link.active
                                    ? 'bg-violet-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-violet-50 border border-gray-200'
                            ]"
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span
                            v-else
                            class="px-4 py-2 text-sm text-gray-400"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-violet-600 to-indigo-600 py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">
                    Ready to grow your business?
                </h2>
                <p class="text-lg text-violet-100 mb-8 max-w-2xl mx-auto">
                    Join MyGrowNet and get your business listed on the marketplace. Reach thousands of potential customers.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a
                        href="/register"
                        class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-violet-600 font-semibold rounded-xl hover:bg-violet-50 transition-colors shadow-lg"
                    >
                        Get Started Free
                        <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                    </a>
                    <a
                        href="/login"
                        class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/10 text-white font-semibold rounded-xl hover:bg-white/20 transition-colors border border-white/30"
                    >
                        Sign In
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center">
                            <SparklesIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <div>
                            <span class="font-bold text-white">MyGrowNet</span>
                            <span class="text-gray-500 ml-2">Marketplace</span>
                        </div>
                    </div>
                    <p class="text-sm">
                        © {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
