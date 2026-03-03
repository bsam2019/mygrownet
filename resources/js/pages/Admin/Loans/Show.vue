<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, BanknotesIcon, CalendarIcon, UserIcon } from '@heroicons/vue/24/outline';

interface Loan {
    id: number;
    loan_number: string;
    user: {
        id: number;
        name: string;
        email: string;
        phone: string;
    };
    principal_amount: number;
    interest_rate: number;
    total_amount: number;
    amount_paid: number;
    principal_paid: number;
    interest_paid: number;
    outstanding_balance: number;
    term_months: number;
    monthly_payment: number;
    disbursement_date: string;
    due_date: string;
    next_payment_date: string | null;
    last_payment_date: string | null;
    status: string;
    risk_category: string;
    days_overdue: number;
    loan_type: string;
    purpose: string;
    notes: string;
    approved_by: string;
    disbursed_by: string;
}

interface Payment {
    id: number;
    payment_reference: string;
    payment_amount: number;
    principal_portion: number;
    interest_portion: number;
    penalty_portion: number;
    payment_date: string;
    payment_method: string;
    notes: string;
}

interface Schedule {
    id: number;
    installment_number: number;
    due_date: string;
    installment_amount: number;
    principal_portion: number;
    interest_portion: number;
    amount_paid: number;
    status: string;
    paid_date: string | null;
}

interface Props {
    loan: Loan;
    payments: Payment[];
    schedule: Schedule[];
}

const props = defineProps<Props>();

const activeTab = ref<'overview' | 'schedule' | 'payments'>('overview');

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

const getScheduleStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-gray-100 text-gray-800',
        paid: 'bg-green-100 text-green-800',
        overdue: 'bg-red-100 text-red-800',
        partial: 'bg-yellow-100 text-yellow-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
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

const paymentProgress = ((props.loan.amount_paid / props.loan.total_amount) * 100).toFixed(1);

</script>

<template>
    <Head :title="`Loan ${loan.loan_number}`" />

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
                                Loan {{ loan.loan_number }}
                            </h2>
                            <div class="mt-2 flex items-center space-x-4">
                                <span :class="[getStatusColor(loan.status), 'px-3 py-1 text-sm font-semibold rounded-full']">
                                    {{ loan.status.replace('_', ' ').toUpperCase() }}
                                </span>
                                <span :class="[getRiskColor(loan.risk_category), 'px-3 py-1 text-sm font-semibold rounded-full']">
                                    {{ loan.risk_category.replace('_', ' ').toUpperCase() }}
                                </span>
                                <span v-if="loan.days_overdue > 0" class="text-sm text-red-600 font-medium">
                                    {{ loan.days_overdue }} days overdue
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 flex md:mt-0 md:ml-4">
                            <Link
                                v-if="loan.status === 'active'"
                                :href="route('admin.platform-loans.payment', loan.id)"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700"
                            >
                                <BanknotesIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Record Payment
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">💰</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Principal Amount</dt>
                                        <dd class="text-lg font-semibold text-gray-900">
                                            {{ formatCurrency(loan.principal_amount) }}
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
                                    <span class="text-2xl">📊</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
                                        <dd class="text-lg font-semibold text-gray-900">
                                            {{ formatCurrency(loan.total_amount) }}
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
                                    <span class="text-2xl">✅</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Amount Paid</dt>
                                        <dd class="text-lg font-semibold text-green-600">
                                            {{ formatCurrency(loan.amount_paid) }}
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
                                    <span class="text-2xl">⏳</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Outstanding</dt>
                                        <dd class="text-lg font-semibold text-red-600">
                                            {{ formatCurrency(loan.outstanding_balance) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Progress -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Progress</h3>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                                    {{ paymentProgress }}% Complete
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-gray-600">
                                    {{ formatCurrency(loan.amount_paid) }} / {{ formatCurrency(loan.total_amount) }}
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                            <div
                                :style="{ width: paymentProgress + '%' }"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white shadow rounded-lg">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                            <button
                                @click="activeTab = 'overview'"
                                :class="[
                                    activeTab === 'overview'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Overview
                            </button>
                            <button
                                @click="activeTab = 'schedule'"
                                :class="[
                                    activeTab === 'schedule'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Payment Schedule
                            </button>
                            <button
                                @click="activeTab = 'payments'"
                                :class="[
                                    activeTab === 'payments'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Payment History ({{ payments.length }})
                            </button>
                        </nav>
                    </div>

                    <!-- Overview Tab -->
                    <div v-show="activeTab === 'overview'" class="p-6">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <!-- Borrower Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                    <UserIcon class="h-5 w-5 mr-2 text-gray-400" aria-hidden="true" />
                                    Borrower Information
                                </h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ loan.user.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ loan.user.email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ loan.user.phone || 'N/A' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Loan Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                    <CalendarIcon class="h-5 w-5 mr-2 text-gray-400" aria-hidden="true" />
                                    Loan Information
                                </h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Loan Type</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ loan.loan_type.replace('_', ' ') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Interest Rate</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ loan.interest_rate }}% per year</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Term</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ loan.term_months }} months</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Monthly Payment</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(loan.monthly_payment) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Dates -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Important Dates</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Disbursement Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(loan.disbursement_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(loan.due_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Next Payment</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(loan.next_payment_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Last Payment</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(loan.last_payment_date) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Payment Breakdown -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Breakdown</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Principal Paid</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(loan.principal_paid) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Interest Paid</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(loan.interest_paid) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Total Paid</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ formatCurrency(loan.amount_paid) }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Purpose and Notes -->
                        <div class="mt-6 space-y-4">
                            <div v-if="loan.purpose">
                                <h4 class="text-sm font-medium text-gray-500">Purpose</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ loan.purpose }}</p>
                            </div>
                            <div v-if="loan.notes">
                                <h4 class="text-sm font-medium text-gray-500">Notes</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ loan.notes }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Approved By</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ loan.approved_by || 'N/A' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Disbursed By</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ loan.disbursed_by || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Schedule Tab -->
                    <div v-show="activeTab === 'schedule'" class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Principal
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Interest
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Paid
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in schedule" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ item.installment_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(item.due_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(item.installment_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(item.principal_portion) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(item.interest_portion) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(item.amount_paid) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[getScheduleStatusColor(item.status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                                {{ item.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment History Tab -->
                    <div v-show="activeTab === 'payments'" class="p-6">
                        <div v-if="payments.length === 0" class="text-center py-12">
                            <p class="text-gray-500">No payments recorded yet</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reference
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Principal
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Interest
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Method
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="payment in payments" :key="payment.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ payment.payment_reference }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(payment.payment_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ formatCurrency(payment.payment_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(payment.principal_portion) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(payment.interest_portion) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ payment.payment_method }}
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
