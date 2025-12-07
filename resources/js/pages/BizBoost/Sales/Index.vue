<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

interface Sale {
    id: number;
    customer_name: string | null;
    total_amount: number;
    payment_method: string;
    status: string;
    items_count: number;
    created_at: string;
}

interface Props {
    sales: {
        data: Sale[];
        links: any;
        meta: any;
    };
    stats: {
        today: number;
        this_week: number;
        this_month: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/bizboost/sales', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Sales - BizBoost" />
    <BizBoostLayout title="Sales">
        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Today</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">K{{ stats.today.toLocaleString() }}</p>
                </div>
                <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">This Week</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">K{{ stats.this_week.toLocaleString() }}</p>
                </div>
                <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">This Month</p>
                    <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">K{{ stats.this_month.toLocaleString() }}</p>
                </div>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sales</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Track your sales and revenue</p>
                </div>
                <Link
                    href="/bizboost/sales/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Record Sale
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="relative flex-1">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search sales..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-10 focus:border-violet-500 focus:ring-violet-500"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <select
                    v-model="status"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                    @change="applyFilters"
                >
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Sales List -->
            <div v-if="sales.data.length" class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sale #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">#{{ sale.id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ sale.customer_name || 'Walk-in' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ sale.items_count }} items</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">K{{ sale.total_amount.toLocaleString() }}</td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'text-xs px-2 py-1 rounded-full',
                                        sale.status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                        sale.status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                    ]"
                                >
                                    {{ sale.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(sale.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link
                                    :href="`/bizboost/sales/${sale.id}`"
                                    class="p-2 text-gray-400 hover:text-violet-600 dark:hover:text-violet-400"
                                    aria-label="View sale"
                                >
                                    <EyeIcon class="h-5 w-5" aria-hidden="true" />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-xl bg-white dark:bg-gray-800 p-12 text-center shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                <CurrencyDollarIcon class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No sales yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Record your first sale to start tracking revenue.</p>
                <Link
                    href="/bizboost/sales/create"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Record Sale
                </Link>
            </div>
        </div>
    </BizBoostLayout>
</template>
