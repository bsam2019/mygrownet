<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { MagnifyingGlassIcon, CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Investment {
    id: number;
    amount: number;
    shares: number;
    status: string;
    payment_method: string;
    payment_reference: string | null;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    venture: {
        id: number;
        title: string;
        slug: string;
    };
}

interface Stats {
    total_investments: number;
    pending_investments: number;
    confirmed_investments: number;
    total_amount: number;
}

interface Pagination {
    data: Investment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    investments: Pagination;
    stats: Stats;
    filters: {
        status?: string;
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const searchInvestments = () => {
    router.get(route('admin.ventures.investments.index'), {
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
    router.get(route('admin.ventures.investments.index'));
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
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        refunded: 'bg-red-100 text-red-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    if (status === 'confirmed') return CheckCircleIcon;
    if (status === 'refunded') return XCircleIcon;
    return ClockIcon;
};
</script>

<template>
    <Head title="All Investments" />

    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">All Investments</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage all venture investments across the platform
                    </p>
                </div>

                <!-- Stats -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="text-sm font-medium text-gray-600">Total Investments</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_investments }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="text-sm font-medium text-gray-600">Pending</div>
                        <div class="mt-2 text-3xl font-bold text-yellow-600">{{ stats.pending_investments }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="text-sm font-medium text-gray-600">Confirmed</div>
                        <div class="mt-2 text-3xl font-bold text-green-600">{{ stats.confirmed_investments }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="text-sm font-medium text-gray-600">Total Amount</div>
                        <div class="mt-2 text-3xl font-bold text-blue-600">{{ formatCurrency(stats.total_amount) }}</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <!-- Search -->
                        <div class="relative md:col-span-2">
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by member name or email..."
                                class="w-full rounded-md border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500"
                                @keyup.enter="searchInvestments"
                            />
                        </div>

                        <!-- Status Filter -->
                        <select
                            v-model="statusFilter"
                            class="rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="refunded">Refunded</option>
                        </select>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <button
                                @click="searchInvestments"
                                class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                Search
                            </button>
                            <button
                                v-if="search || statusFilter"
                                @click="clearFilters"
                                class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Investments Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Member
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Venture
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Shares
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="investment in investments.data" :key="investment.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ investment.user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ investment.user.email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="route('admin.ventures.edit', investment.venture.id)"
                                        class="text-sm font-medium text-blue-600 hover:text-blue-500"
                                    >
                                        {{ investment.venture.title }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(investment.amount) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                    {{ investment.shares }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="getStatusColor(investment.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        <component :is="getStatusIcon(investment.status)" class="mr-1 h-4 w-4" />
                                        {{ investment.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(investment.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="investments.data.length === 0" class="p-12 text-center">
                        <p class="text-gray-500">No investments found</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="investments.last_page > 1" class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ (investments.current_page - 1) * investments.per_page + 1 }}</span>
                        to
                        <span class="font-medium">{{ Math.min(investments.current_page * investments.per_page, investments.total) }}</span>
                        of
                        <span class="font-medium">{{ investments.total }}</span>
                        investments
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="investments.current_page > 1"
                            :href="route('admin.ventures.investments.index', { page: investments.current_page - 1, search: search, status: statusFilter })"
                            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="investments.current_page < investments.last_page"
                            :href="route('admin.ventures.investments.index', { page: investments.current_page + 1, search: search, status: statusFilter })"
                            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        >
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
