<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { ref } from 'vue';
import { ArrowLeftIcon, FilterIcon } from 'lucide-vue-next';

interface Transaction {
    id: number;
    source: string;
    lp_amount: number;
    map_amount: number;
    description: string;
    created_at: string;
}

interface Props {
    transactions: {
        data: Transaction[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    filters: {
        source?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const sourceFilter = ref(props.filters.source || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const applyFilters = () => {
    router.get(route('points.transactions'), {
        source: sourceFilter.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    sourceFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('points.transactions'));
};

const getSourceBadgeColor = (source: string) => {
    const colors: Record<string, string> = {
        registration: 'bg-purple-100 text-purple-800',
        direct_referral: 'bg-blue-100 text-blue-800',
        course_completion: 'bg-green-100 text-green-800',
        product_sale: 'bg-yellow-100 text-yellow-800',
        monthly_renewal: 'bg-indigo-100 text-indigo-800',
        workshop_attendance: 'bg-pink-100 text-pink-800',
    };
    return colors[source] || 'bg-gray-100 text-gray-800';
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
</script>

<template>
    <Head title="Points Transactions" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <Link
                            :href="route('points.index')"
                            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-2"
                        >
                            <ArrowLeftIcon class="h-4 w-4 mr-1" />
                            Back to Points Dashboard
                        </Link>
                        <h1 class="text-2xl font-bold text-gray-900">Points Transactions</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            View your complete points history
                        </p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <FilterIcon class="h-5 w-5 text-gray-400" />
                        <h3 class="text-sm font-medium text-gray-900">Filters</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                            <select
                                v-model="sourceFilter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">All Sources</option>
                                <option value="registration">Registration</option>
                                <option value="direct_referral">Direct Referral</option>
                                <option value="course_completion">Course Completion</option>
                                <option value="product_sale">Product Sale</option>
                                <option value="monthly_renewal">Monthly Renewal</option>
                                <option value="workshop_attendance">Workshop Attendance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                            <input
                                v-model="dateFrom"
                                type="date"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                            <input
                                v-model="dateTo"
                                type="date"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                @click="applyFilters"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                            >
                                Apply
                            </button>
                            <button
                                @click="clearFilters"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Source
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        LP
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        MAP
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No transactions found
                                    </td>
                                </tr>
                                <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(transaction.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getSourceBadgeColor(transaction.source)]">
                                            {{ transaction.source.replace(/_/g, ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ transaction.description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-blue-600">
                                        +{{ transaction.lp_amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">
                                        +{{ transaction.map_amount }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ (transactions.current_page - 1) * transactions.per_page + 1 }} to 
                            {{ Math.min(transactions.current_page * transactions.per_page, transactions.total) }} of 
                            {{ transactions.total }} results
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-for="link in transactions.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 border rounded-md text-sm',
                                    link.active
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : link.url
                                        ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                        : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                                ]"
                                :disabled="!link.url"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
