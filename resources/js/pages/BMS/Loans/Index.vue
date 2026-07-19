<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { MagnifyingGlassIcon, PlusIcon, FunnelIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';
import { ExclamationTriangleIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/solid';

// Reuse the same types from Admin
interface Loan {
    id: number;
    loan_number: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    principal_amount: number;
    interest_rate: number;
    outstanding_balance: number;
    monthly_payment: number;
    next_payment_date: string | null;
    status: 'active' | 'paid' | 'defaulted' | 'written_off';
    risk_category: 'current' | '30_days' | '60_days' | '90_days' | 'default';
    days_overdue: number;
    disbursement_date: string;
}

interface Props {
    loans?: {
        data: Loan[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters?: {
        search?: string;
        status?: string;
        risk_category?: string;
    };
    summary?: {
        total_loans: number;
        active_loans: number;
        total_outstanding: number;
        overdue_loans: number;
        defaulted_loans: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    loans: () => ({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0,
    }),
    summary: () => ({
        total_loans: 0,
        active_loans: 0,
        total_outstanding: 0,
        overdue_loans: 0,
        defaulted_loans: 0,
    }),
    filters: () => ({
        search: '',
        status: '',
        risk_category: '',
    }),
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const riskFilter = ref(props.filters?.risk_category || '');

// Safe summary with defaults
const safeSummary = computed(() => ({
    total_loans: props.summary?.total_loans ?? 0,
    active_loans: props.summary?.active_loans ?? 0,
    total_outstanding: props.summary?.total_outstanding ?? 0,
    overdue_loans: props.summary?.overdue_loans ?? 0,
    defaulted_loans: props.summary?.defaulted_loans ?? 0,
}));

const applyFilters = () => {
    router.get(route('cms.loans.index'), {
        search: search.value,
        status: statusFilter.value,
        risk_category: riskFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    riskFilter.value = '';
    applyFilters();
};

const getStatusColor = (status: string) => {
    const colors = {
        active: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        defaulted: 'bg-red-100 text-red-800',
        written_off: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getRiskColor = (risk: string) => {
    const colors = {
        current: 'bg-green-100 text-green-800',
        '30_days': 'bg-yellow-100 text-yellow-800',
        '60_days': 'bg-orange-100 text-orange-800',
        '90_days': 'bg-red-100 text-red-800',
        default: 'bg-red-100 text-red-800',
    };
    return colors[risk as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Loans Receivable" />

    <CMSLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="md:flex md:items-center md:justify-between mb-6">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Loans Receivable
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Manage loans given to customers
                        </p>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                        <Link
                            :href="route('cms.loans.reports.aging')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            <DocumentArrowDownIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            Aging Report
                        </Link>
                        <Link
                            :href="route('cms.loans.create')"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                        >
                            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            New Loan
                        </Link>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ClockIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Loans</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ safeSummary.total_loans }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <CheckCircleIcon class="h-6 w-6 text-blue-400" aria-hidden="true" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Active Loans</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ safeSummary.active_loans }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">💰</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Outstanding</dt>
                                        <dd class="text-lg font-semibold text-gray-900">
                                            {{ formatCurrency(summary.total_outstanding) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ExclamationTriangleIcon class="h-6 w-6 text-yellow-400" aria-hidden="true" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Overdue</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ safeSummary.overdue_loans }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ExclamationTriangleIcon class="h-6 w-6 text-red-400" aria-hidden="true" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Defaulted</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ safeSummary.defaulted_loans }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white shadow rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div class="sm:col-span-2">
                            <label for="search" class="sr-only">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input
                                    v-model="search"
                                    type="text"
                                    id="search"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Search by loan number or customer name"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="status" class="sr-only">Status</label>
                            <select
                                v-model="statusFilter"
                                id="status"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                            >
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="paid">Paid</option>
                                <option value="defaulted">Defaulted</option>
                                <option value="written_off">Written Off</option>
                            </select>
                        </div>

                        <div>
                            <label for="risk" class="sr-only">Risk Category</label>
                            <select
                                v-model="riskFilter"
                                id="risk"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                            >
                                <option value="">All Risk Levels</option>
                                <option value="current">Current</option>
                                <option value="30_days">30 Days</option>
                                <option value="60_days">60 Days</option>
                                <option value="90_days">90 Days</option>
                                <option value="default">Default</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-3">
                        <button
                            @click="clearFilters"
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Clear
                        </button>
                        <button
                            @click="applyFilters"
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <FunnelIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                            Apply Filters
                        </button>
                    </div>
                </div>

                <!-- Loans Table -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loan Details
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Borrower
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Outstanding
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Risk
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Next Payment
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="loan in loans.data" :key="loan.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ loan.loan_number }}</div>
                                    <div class="text-sm text-gray-500">{{ formatDate(loan.disbursement_date) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ loan.user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ loan.user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatCurrency(loan.principal_amount) }}</div>
                                    <div class="text-sm text-gray-500">{{ loan.interest_rate }}% APR</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ formatCurrency(loan.outstanding_balance) }}</div>
                                    <div class="text-sm text-gray-500">{{ formatCurrency(loan.monthly_payment) }}/mo</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[getStatusColor(loan.status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                        {{ loan.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[getRiskColor(loan.risk_category), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                        {{ loan.risk_category.replace('_', ' ') }}
                                    </span>
                                    <div v-if="loan.days_overdue > 0" class="text-xs text-red-600 mt-1">
                                        {{ loan.days_overdue }} days overdue
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(loan.next_payment_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link
                                        :href="route('cms.loans.show', loan.id)"
                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        v-if="loan.status === 'active'"
                                        :href="route('cms.loans.payment', loan.id)"
                                        class="text-green-600 hover:text-green-900"
                                    >
                                        Record Payment
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="loans.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="loans.current_page > 1"
                                :href="route('cms.loans.index', { ...filters, page: loans.current_page - 1 })"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="loans.current_page < loans.last_page"
                                :href="route('cms.loans.index', { ...filters, page: loans.current_page + 1 })"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ (loans.current_page - 1) * loans.per_page + 1 }}</span>
                                    to
                                    <span class="font-medium">{{ Math.min(loans.current_page * loans.per_page, loans.total) }}</span>
                                    of
                                    <span class="font-medium">{{ loans.total }}</span>
                                    results
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
