<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
import { MagnifyingGlassIcon, FunnelIcon, BriefcaseIcon } from '@heroicons/vue/24/outline';

interface Venture {
    id: number;
    title: string;
    slug: string;
    description: string;
    funding_target: number;
    total_raised: number;
    minimum_investment: number;
    investor_count: number;
    is_featured: boolean;
    featured_image: string | null;
    expected_roi_months: number | null;
    category: {
        name: string;
        icon: string;
    };
}

interface Pagination {
    data: Venture[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    ventures: Pagination;
    filters: {
        category?: string;
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');

const searchVentures = () => {
    router.get(route('ventures.index'), {
        search: search.value,
        category: categoryFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    router.get(route('ventures.index'));
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getFundingProgress = (venture: Venture) => {
    if (venture.funding_target <= 0) return 0;
    return Math.min(100, (venture.total_raised / venture.funding_target) * 100);
};

const truncateText = (text: string, length: number = 150) => {
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};
</script>

<template>
    <Head title="Venture Marketplace" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />
        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Venture Marketplace</h1>
                            <p class="mt-2 text-sm text-gray-600">
                                Invest in vetted business projects and become a shareholder
                            </p>
                        </div>
                        <Link
                            :href="route('ventures.about')"
                            class="flex items-center space-x-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 transition"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>How It Works</span>
                        </Link>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Search -->
                        <div class="relative md:col-span-2">
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search ventures..."
                                class="w-full rounded-md border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500"
                                @keyup.enter="searchVentures"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <button
                                @click="searchVentures"
                                class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                <FunnelIcon class="inline h-4 w-4 mr-1" />
                                Search
                            </button>
                            <button
                                v-if="search || categoryFilter"
                                @click="clearFilters"
                                class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ventures Grid -->
                <div v-if="ventures.data.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="venture in ventures.data"
                        :key="venture.id"
                        class="overflow-hidden rounded-lg bg-white shadow transition-shadow hover:shadow-lg"
                    >
                        <!-- Image/Icon -->
                        <div class="relative h-48 bg-gradient-to-br from-blue-500 to-blue-600">
                            <div v-if="venture.is_featured" class="absolute right-2 top-2">
                                <span class="inline-flex items-center rounded-full bg-yellow-400 px-3 py-1 text-xs font-semibold text-yellow-900">
                                    Featured
                                </span>
                            </div>
                            <div class="flex h-full items-center justify-center">
                                <BriefcaseIcon class="h-20 w-20 text-white opacity-50" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Category -->
                            <div class="mb-2">
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                    {{ venture.category.name }}
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 class="mb-2 text-lg font-semibold text-gray-900">
                                {{ venture.title }}
                            </h3>

                            <!-- Description -->
                            <p class="mb-4 text-sm text-gray-600">
                                {{ truncateText(venture.description) }}
                            </p>

                            <!-- Stats -->
                            <div class="mb-4 space-y-2">
                                <!-- Funding Progress -->
                                <div>
                                    <div class="mb-1 flex justify-between text-sm">
                                        <span class="text-gray-600">Funding Progress</span>
                                        <span class="font-semibold text-gray-900">
                                            {{ Math.round(getFundingProgress(venture)) }}%
                                        </span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full bg-green-600 transition-all"
                                            :style="{ width: `${getFundingProgress(venture)}%` }"
                                        ></div>
                                    </div>
                                    <div class="mt-1 flex justify-between text-xs text-gray-500">
                                        <span>{{ formatCurrency(venture.total_raised) }}</span>
                                        <span>{{ formatCurrency(venture.funding_target) }}</span>
                                    </div>
                                </div>

                                <!-- Additional Info -->
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <span class="text-gray-600">Min. Investment:</span>
                                        <span class="ml-1 font-semibold text-gray-900">
                                            {{ formatCurrency(venture.minimum_investment) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Investors:</span>
                                        <span class="ml-1 font-semibold text-gray-900">
                                            {{ venture.investor_count }}
                                        </span>
                                    </div>
                                </div>

                                <!-- ROI -->
                                <div v-if="venture.expected_roi_months" class="text-sm">
                                    <span class="text-gray-600">Expected ROI:</span>
                                    <span class="ml-1 font-semibold text-green-600">
                                        {{ venture.expected_roi_months }} months
                                    </span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <Link
                                :href="route('ventures.show', venture.slug)"
                                class="block w-full rounded-md bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-lg bg-white p-12 text-center shadow">
                    <BriefcaseIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No ventures available</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Check back soon for new investment opportunities.
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="ventures.last_page > 1" class="mt-6 flex items-center justify-between rounded-lg bg-white px-4 py-3 shadow">
                    <div class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ (ventures.current_page - 1) * ventures.per_page + 1 }}</span>
                        to
                        <span class="font-medium">{{ Math.min(ventures.current_page * ventures.per_page, ventures.total) }}</span>
                        of
                        <span class="font-medium">{{ ventures.total }}</span>
                        ventures
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="ventures.current_page > 1"
                            :href="route('ventures.index', { page: ventures.current_page - 1, search: search, category: categoryFilter })"
                            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="ventures.current_page < ventures.last_page"
                            :href="route('ventures.index', { page: ventures.current_page + 1, search: search, category: categoryFilter })"
                            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        >
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        <Footer />
    </div>
</template>
