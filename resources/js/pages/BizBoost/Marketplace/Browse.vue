<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    MagnifyingGlassIcon,
    MapPinIcon,
    BuildingStorefrontIcon,
    ArrowLeftIcon,
    ArrowTopRightOnSquareIcon,
    ShoppingBagIcon,
    GlobeAltIcon,
} from '@heroicons/vue/24/outline';

interface Business {
    id: number;
    name: string;
    slug: string;
    logo_url: string | null;
    category: string;
    city: string | null;
    description: string | null;
    products_count: number;
}

interface Props {
    businesses: Business[];
    categories: string[];
    cities: string[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const selectedCategory = ref('');
const selectedCity = ref('');

const filteredBusinesses = computed(() => {
    return props.businesses.filter(b => {
        const matchesSearch = !searchQuery.value || 
            b.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            b.description?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = !selectedCategory.value || b.category === selectedCategory.value;
        const matchesCity = !selectedCity.value || b.city === selectedCity.value;
        return matchesSearch && matchesCategory && matchesCity;
    });
});

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedCity.value = '';
};

// Redirect to public marketplace on mount
onMounted(() => {
    // Optional: Auto-redirect to public marketplace
    // window.location.href = '/marketplace';
});
</script>

<template>
    <Head title="Browse Marketplace - BizBoost" />
    <BizBoostLayout title="Browse Marketplace">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link
                        href="/bizboost/marketplace"
                        class="p-2 rounded-xl hover:bg-gray-100 transition-colors"
                        aria-label="Back to marketplace settings"
                    >
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Browse Marketplace</h1>
                        <p class="text-sm text-gray-500">{{ filteredBusinesses.length }} businesses found</p>
                    </div>
                </div>
                <a
                    href="/marketplace"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-xl text-sm font-semibold hover:bg-violet-700 transition-colors"
                >
                    <GlobeAltIcon class="h-5 w-5" aria-hidden="true" />
                    Open Public Marketplace
                    <ArrowTopRightOnSquareIcon class="h-4 w-4" aria-hidden="true" />
                </a>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1 relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search businesses..."
                            class="w-full pl-10 rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500"
                        />
                    </div>

                    <!-- Category Filter -->
                    <select
                        v-model="selectedCategory"
                        class="rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500"
                    >
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>

                    <!-- City Filter -->
                    <select
                        v-model="selectedCity"
                        class="rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500"
                    >
                        <option value="">All Cities</option>
                        <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                    </select>

                    <!-- Clear Filters -->
                    <button
                        v-if="searchQuery || selectedCategory || selectedCity"
                        @click="clearFilters"
                        class="px-4 py-2 text-sm text-violet-600 hover:text-violet-700 font-medium"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Business Grid -->
            <div v-if="filteredBusinesses.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <a
                    v-for="business in filteredBusinesses"
                    :key="business.id"
                    :href="`/biz/${business.slug}`"
                    target="_blank"
                    class="group bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden hover:shadow-lg hover:ring-violet-200 transition-all"
                >
                    <!-- Logo/Header -->
                    <div class="aspect-[3/2] bg-gradient-to-br from-violet-50 to-indigo-50 flex items-center justify-center">
                        <img
                            v-if="business.logo_url"
                            :src="business.logo_url"
                            :alt="business.name"
                            class="w-20 h-20 object-contain group-hover:scale-110 transition-transform"
                        />
                        <div v-else class="w-20 h-20 rounded-2xl bg-white shadow-sm flex items-center justify-center">
                            <span class="text-2xl font-bold text-violet-600">{{ business.name[0] }}</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-gray-900 group-hover:text-violet-600 transition-colors">
                                {{ business.name }}
                            </h3>
                            <span class="text-xs bg-violet-100 text-violet-700 px-2 py-1 rounded-full font-medium">
                                {{ business.category }}
                            </span>
                        </div>

                        <p v-if="business.description" class="text-sm text-gray-500 line-clamp-2 mb-3">
                            {{ business.description }}
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
                    </div>
                </a>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-white rounded-2xl ring-1 ring-gray-100">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-violet-100 flex items-center justify-center">
                    <BuildingStorefrontIcon class="h-8 w-8 text-violet-600" aria-hidden="true" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No businesses found</h3>
                <p class="text-gray-500 mb-4">Try adjusting your search or filters.</p>
                <button
                    @click="clearFilters"
                    class="text-sm text-violet-600 hover:text-violet-700 font-medium"
                >
                    Clear all filters
                </button>
            </div>
        </div>
    </BizBoostLayout>
</template>
