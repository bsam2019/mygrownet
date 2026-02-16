<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, inject } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, BanknotesIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';

defineOptions({
  layout: CMSLayout
})

interface Expense {
    id: number;
    expense_number: string;
    description: string;
    amount: number;
    payment_method: string;
    expense_date: string;
    approval_status: string;
    receipt_path: string | null;
    category: {
        id: number;
        name: string;
    };
    job: {
        id: number;
        job_number: string;
    } | null;
    recordedBy: {
        user: {
            name: string;
        };
    };
}

interface Props {
    expenses: {
        data: Expense[];
        links: any[];
        meta: any;
    };
    categories: Array<{ id: number; name: string }>;
    summary: {
        total_expenses: number;
        pending_approval: number;
        this_month: number;
    };
    filters: {
        category_id: number | null;
        approval_status: string;
        search: string;
    };
}

const props = defineProps<Props>();

const slideOver: any = inject('slideOver');

const search = ref(props.filters.search);
const selectedCategory = ref(props.filters.category_id);
const selectedStatus = ref(props.filters.approval_status);

const applyFilters = () => {
    router.get('/cms/expenses', {
        category_id: selectedCategory.value,
        approval_status: selectedStatus.value,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const approveExpense = (id: number) => {
    if (confirm('Approve this expense?')) {
        router.post(route('cms.expenses.approve', id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success message handled by backend
            }
        });
    }
};

const rejectExpense = (id: number) => {
    const reason = prompt('Enter rejection reason:');
    if (reason) {
        router.post(route('cms.expenses.reject', id), { reason }, {
            preserveScroll: true,
        });
    }
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getPaymentMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        cash: 'Cash',
        bank_transfer: 'Bank Transfer',
        mtn_momo: 'MTN MoMo',
        airtel_money: 'Airtel Money',
        company_card: 'Company Card',
    };
    return labels[method] || method;
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'amber',
        approved: 'green',
        rejected: 'red',
    };
    return colors[status] || 'gray';
};
</script>

<template>
    <Head title="Expenses - CMS" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
                <p class="mt-1 text-sm text-gray-600">Track and manage business expenses</p>
            </div>
            <button
                @click="slideOver?.open('expense')"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Record Expense
            </button>
        </div>

        <!-- Summary Stats -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Total Expenses</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(summary.total_expenses) }}</div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">This Month</div>
                <div class="mt-1 text-2xl font-bold text-blue-600">{{ formatCurrency(summary.this_month) }}</div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Pending Approval</div>
                <div class="mt-1 text-2xl font-bold text-amber-600">{{ summary.pending_approval }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="flex-1">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search expenses..."
                            class="w-full px-4 py-2.5 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>

                <div class="sm:w-48">
                    <select
                        v-model="selectedCategory"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        @change="applyFilters"
                    >
                        <option :value="null">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>

                <div class="sm:w-48">
                    <select
                        v-model="selectedStatus"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        @change="applyFilters"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <button
                    @click="applyFilters"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                >
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Expense #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Method
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="expense in expenses.data" :key="expense.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ expense.expense_number }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ formatDate(expense.expense_date) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                            {{ expense.category.name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate">{{ expense.description }}</div>
                            <div v-if="expense.job" class="text-xs text-gray-500">
                                Job: {{ expense.job.job_number }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ formatCurrency(expense.amount) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ getPaymentMethodLabel(expense.payment_method) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                    getStatusColor(expense.approval_status) === 'amber' && 'bg-amber-100 text-amber-800',
                                    getStatusColor(expense.approval_status) === 'green' && 'bg-green-100 text-green-800',
                                    getStatusColor(expense.approval_status) === 'red' && 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{ expense.approval_status.charAt(0).toUpperCase() + expense.approval_status.slice(1) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            <div class="flex justify-end gap-2">
                                <button
                                    v-if="expense.approval_status === 'pending'"
                                    @click="approveExpense(expense.id)"
                                    class="text-green-600 hover:text-green-800"
                                    title="Approve"
                                >
                                    <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                                <button
                                    v-if="expense.approval_status === 'pending'"
                                    @click="rejectExpense(expense.id)"
                                    class="text-red-600 hover:text-red-800"
                                    title="Reject"
                                >
                                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                                <a
                                    v-if="expense.receipt_path"
                                    :href="expense.receipt_path"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-800"
                                >
                                    Receipt
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="expenses.data.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center">
                            <BanknotesIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                            <p class="mt-2 text-sm text-gray-600">No expenses found</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="expenses.data.length > 0" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ expenses.meta.from }} to {{ expenses.meta.to }} of {{ expenses.meta.total }} results
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in expenses.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : link.url
                                    ? 'bg-white text-gray-700 hover:bg-gray-50'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed',
                            ]"
                            :disabled="!link.url"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
