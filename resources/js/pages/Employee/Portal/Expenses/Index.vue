<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    CurrencyDollarIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    PlusIcon,
    ReceiptPercentIcon,
} from '@heroicons/vue/24/outline';

interface Expense {
    id: number;
    title: string;
    description: string | null;
    category: string;
    amount: number;
    currency: string;
    expense_date: string;
    status: string;
    submitted_at: string | null;
    approved_at: string | null;
    approver?: { full_name: string };
}

interface Props {
    expenses: Expense[];
    stats: {
        total_submitted: number;
        total_approved: number;
        total_reimbursed: number;
        pending_count: number;
        pending_amount: number;
        by_category: Record<string, { count: number; amount: number }>;
    };
    categories: Record<string, string>;
    filters: { status?: string; category?: string; year?: string };
}

const props = defineProps<Props>();

const formatCurrency = (amount: number, currency = 'ZMW') => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-700',
        submitted: 'bg-blue-100 text-blue-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        reimbursed: 'bg-purple-100 text-purple-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getStatusIcon = (status: string) => {
    const icons: Record<string, any> = {
        draft: ClockIcon,
        submitted: ClockIcon,
        approved: CheckCircleIcon,
        rejected: XCircleIcon,
        reimbursed: CurrencyDollarIcon,
    };
    return icons[status] || ClockIcon;
};
</script>

<template>
    <Head title="Expenses" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Expense Claims</h1>
                    <p class="text-gray-500 mt-1">Submit and track your expense reimbursements</p>
                </div>
                <Link :href="route('employee.portal.expenses.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    New Expense
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Submitted</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_submitted) }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <ReceiptPercentIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pending</p>
                            <p class="text-2xl font-bold text-amber-600">{{ formatCurrency(stats.pending_amount) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ stats.pending_count }} claims</p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Approved</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.total_approved) }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg">
                            <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Reimbursed</p>
                            <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats.total_reimbursed) }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <CurrencyDollarIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Expense History</h2>
                    <select class="border-gray-300 rounded-lg text-sm">
                        <option value="">All Categories</option>
                        <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>

                <div class="divide-y divide-gray-100">
                    <div v-for="expense in expenses" :key="expense.id" class="p-5 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 rounded-lg" :class="getStatusColor(expense.status).replace('text-', 'bg-').replace('-700', '-100')">
                                    <component :is="getStatusIcon(expense.status)" class="h-6 w-6" :class="getStatusColor(expense.status).split(' ')[1]" aria-hidden="true" />
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ expense.title }}</h3>
                                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                        <span>{{ categories[expense.category] || expense.category }}</span>
                                        <span>•</span>
                                        <span>{{ new Date(expense.expense_date).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <p class="text-lg font-bold text-gray-900">{{ formatCurrency(expense.amount, expense.currency) }}</p>
                                <span :class="getStatusColor(expense.status)" class="px-3 py-1 text-xs font-medium rounded-full capitalize">
                                    {{ expense.status }}
                                </span>
                                <Link :href="route('employee.portal.expenses.show', expense.id)"
                                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    View
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-if="expenses.length === 0" class="p-8 text-center text-gray-500">
                        <ReceiptPercentIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                        <p>No expenses submitted yet</p>
                        <Link :href="route('employee.portal.expenses.create')"
                            class="inline-flex items-center mt-4 text-blue-600 hover:text-blue-700">
                            <PlusIcon class="h-5 w-5 mr-1" aria-hidden="true" />
                            Submit your first expense
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
