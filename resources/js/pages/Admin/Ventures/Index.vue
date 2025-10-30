<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { MagnifyingGlassIcon, PlusIcon, FunnelIcon } from '@heroicons/vue/24/outline';

interface Venture {
    id: number;
    title: string;
    slug: string;
    status: string;
    funding_target: number;
    total_raised: number;
    investor_count: number;
    is_featured: boolean;
    created_at: string;
    category: {
        name: string;
    };
    creator: {
        name: string;
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
        status?: string;
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const searchVentures = () => {
    router.get(route('admin.ventures.index'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    router.get(route('admin.ventures.index'));
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800',
        review: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        funding: 'bg-green-100 text-green-800',
        funded: 'bg-indigo-100 text-indigo-800',
        active: 'bg-emerald-100 text-emerald-800',
        completed: 'bg-purple-100 text-purple-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getFundingProgress = (venture: Venture) => {
    if (venture.funding_target <= 0) return 0;
    return Math.min(100, (venture.total_raised / venture.funding_target) * 100);
};
</script>

<template>
    <Head title="All Ventures" />

    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">All Ventures</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage all business ventures
                        </p>
                    </div>
                    <Link
                        :href="route('admin.ventures.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Create Venture
                    </Link>
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Search -->
                        <div class="relative">
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search ventures..."
                                class="w-full rounded-md border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500"
                                @keyup.enter="searchVentures"
                            />
                        </div>

                        <!-- Status Filter -->
                        <select
                            v-model="statusFilter"
                            class="rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            @change="searchVentures"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="review">Review</option>
                            <option value="approved">Approved</option>
                            <option value="funding">Funding</option>
                            <option value="funded">Funded</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <button
                                @click="searchVentures"
                                class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                <FunnelIcon class="inline h-4 w-4 mr-1" />
                                Filter
                            </button>
                            <button
                                @click="clearFilters"
                                class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ventures Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Venture
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Funding Progress
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Investors
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="venture in ventures.data" :key="venture.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <Link
                                                    :href="route('admin.ventures.edit', venture.id)"
                                                    class="font-medium text-gray-900 hover:text-blue-600"
                                                >
                                                    {{ venture.title }}
                                                </Link>
                                                <span
                                                    v-if="venture.is_featured"
                                                    class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800"
                                                >
                                                    Featured
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                by {{ venture.creator.name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ venture.category.name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ formatCurrency(venture.total_raised) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        of {{ formatCurrency(venture.funding_target) }}
                                    </div>
                                    <div class="mt-1 h-2 w-full rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full bg-green-600"
                                            :style="{ width: `${getFundingProgress(venture)}%` }"
                                        ></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ venture.investor_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                            getStatusColor(venture.status),
                                        ]"
                                    >
                                        {{ venture.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(venture.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link
                                        :href="route('admin.ventures.edit', venture.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="ventures.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                    No ventures found. Create your first venture to get started.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="ventures.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ (ventures.current_page - 1) * ventures.per_page + 1 }}</span>
                                to
                                <span class="font-medium">{{ Math.min(ventures.current_page * ventures.per_page, ventures.total) }}</span>
                                of
                                <span class="font-medium">{{ ventures.total }}</span>
                                results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-if="ventures.current_page > 1"
                                    :href="route('admin.ventures.index', { page: ventures.current_page - 1, search: search, status: statusFilter })"
                                    class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="ventures.current_page < ventures.last_page"
                                    :href="route('admin.ventures.index', { page: ventures.current_page + 1, search: search, status: statusFilter })"
                                    class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
