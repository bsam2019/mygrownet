<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid';

interface AgingBucket {
    category: string;
    label: string;
    count: number;
    total_outstanding: number;
    percentage: number;
    risk_level: string;
}

interface LoanSummary {
    loan_number: string;
    user_name: string;
    principal_amount: number;
    outstanding_balance: number;
    days_overdue: number;
    risk_category: string;
    next_payment_date: string | null;
}

interface Props {
    aging: {
        buckets: AgingBucket[];
        total_loans: number;
        total_outstanding: number;
        provision_required: number;
    };
    loans_by_category: {
        current: LoanSummary[];
        '30_days': LoanSummary[];
        '60_days': LoanSummary[];
        '90_days': LoanSummary[];
        default: LoanSummary[];
    };
}

const props = defineProps<Props>();

const getRiskColor = (risk: string) => {
    const colors = {
        low: 'bg-green-100 text-green-800 border-green-200',
        medium: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        high: 'bg-orange-100 text-orange-800 border-orange-200',
        critical: 'bg-red-100 text-red-800 border-red-200',
        severe: 'bg-red-200 text-red-900 border-red-300',
    };
    return colors[risk as keyof typeof colors] || 'bg-gray-100 text-gray-800 border-gray-200';
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
    <Head title="Loan Aging Report" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.platform-loans.index')"
                        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        Back to Loans
                    </Link>
                    <div class="md:flex md:items-center md:justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                                Loan Aging Report
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Track loan portfolio by days overdue and risk category
                            </p>
                        </div>
                        <div class="mt-4 flex md:mt-0 md:ml-4">
                            <button
                                type="button"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                <DocumentArrowDownIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Export PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">📊</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Loans</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ aging.total_loans }}</dd>
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
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Outstanding</dt>
                                        <dd class="text-lg font-semibold text-gray-900">
                                            {{ formatCurrency(aging.total_outstanding) }}
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
                                    <ExclamationTriangleIcon class="h-6 w-6 text-red-400" aria-hidden="true" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Provision Required</dt>
                                        <dd class="text-lg font-semibold text-red-600">
                                            {{ formatCurrency(aging.provision_required) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aging Buckets -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Aging Analysis</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="bucket in aging.buckets"
                                :key="bucket.category"
                                :class="[getRiskColor(bucket.risk_level), 'border rounded-lg p-4']"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <h4 class="text-lg font-semibold">{{ bucket.label }}</h4>
                                        <p class="text-sm opacity-75">{{ bucket.count }} loans</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold">{{ formatCurrency(bucket.total_outstanding) }}</div>
                                        <div class="text-sm opacity-75">{{ bucket.percentage.toFixed(1) }}% of portfolio</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="w-full bg-white bg-opacity-50 rounded-full h-2">
                                        <div
                                            class="bg-current h-2 rounded-full opacity-50"
                                            :style="{ width: bucket.percentage + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Breakdown by Category -->
                <div class="space-y-6">
                    <div
                        v-for="(loans, category) in loans_by_category"
                        :key="category"
                        v-show="loans.length > 0"
                        class="bg-white shadow rounded-lg"
                    >
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ category.replace('_', ' ').toUpperCase() }} ({{ loans.length }})
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Loan Number
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Borrower
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Principal
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Outstanding
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Days Overdue
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Next Payment
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="loan in loans" :key="loan.loan_number">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ loan.loan_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ loan.user_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(loan.principal_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ formatCurrency(loan.outstanding_balance) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                            {{ loan.days_overdue > 0 ? loan.days_overdue : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(loan.next_payment_date) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
