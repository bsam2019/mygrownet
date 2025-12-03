<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    DocumentTextIcon,
    CalendarIcon,
    BanknotesIcon,
    BuildingOfficeIcon,
    TagIcon,
} from '@heroicons/vue/24/outline';

interface Expense {
    id: number;
    expense_number: string;
    vendor_id: number | null;
    vendor?: {
        id: number;
        name: string;
    };
    account_id: number;
    account?: {
        id: number;
        name: string;
        code: string;
    };
    category: string;
    description: string;
    amount: number;
    tax_amount: number;
    total_amount: number;
    expense_date: string;
    payment_method: string;
    reference_number: string | null;
    receipt_path: string | null;
    notes: string | null;
    is_recurring: boolean;
    recurring_frequency: string | null;
    status: string;
    created_at: string;
}

defineProps<{
    expense: Expense;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        paid: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <GrowFinanceLayout>
        <Head :title="`Expense ${expense.expense_number}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('growfinance.expenses.index')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ expense.expense_number }}</h1>
                        <p class="text-sm text-gray-500">{{ expense.category }}</p>
                    </div>
                    <span :class="[getStatusColor(expense.status), 'px-3 py-1 rounded-full text-sm font-medium capitalize']">
                        {{ expense.status }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('growfinance.expenses.edit', expense.id)"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <PencilIcon class="h-4 w-4" aria-hidden="true" />
                        Edit
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 rounded-lg text-sm font-medium text-white hover:bg-red-700"
                    >
                        <TrashIcon class="h-4 w-4" aria-hidden="true" />
                        Delete
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Expense Details Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Expense Details</h2>
                        
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                    <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                                    Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(expense.expense_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                    <TagIcon class="h-4 w-4" aria-hidden="true" />
                                    Category
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ expense.category }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                    <BuildingOfficeIcon class="h-4 w-4" aria-hidden="true" />
                                    Vendor
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <Link
                                        v-if="expense.vendor"
                                        :href="route('growfinance.vendors.show', expense.vendor.id)"
                                        class="text-blue-600 hover:text-blue-800"
                                    >
                                        {{ expense.vendor.name }}
                                    </Link>
                                    <span v-else class="text-gray-400">No vendor</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 flex items-center gap-2">
                                    <BanknotesIcon class="h-4 w-4" aria-hidden="true" />
                                    Payment Method
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 capitalize">{{ expense.payment_method.replace('_', ' ') }}</dd>
                            </div>
                            <div class="col-span-2" v-if="expense.account">
                                <dt class="text-sm font-medium text-gray-500">Account</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ expense.account.code }} - {{ expense.account.name }}</dd>
                            </div>
                            <div class="col-span-2" v-if="expense.reference_number">
                                <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ expense.reference_number }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Description -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <DocumentTextIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            Description
                        </h2>
                        <p class="text-gray-700">{{ expense.description }}</p>
                        <p v-if="expense.notes" class="mt-4 text-sm text-gray-500">
                            <span class="font-medium">Notes:</span> {{ expense.notes }}
                        </p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Amount Summary -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Amount</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="text-gray-900">{{ formatCurrency(expense.amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tax</span>
                                <span class="text-gray-900">{{ formatCurrency(expense.tax_amount) }}</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between">
                                <span class="font-semibold text-gray-900">Total</span>
                                <span class="font-bold text-xl text-gray-900">{{ formatCurrency(expense.total_amount) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recurring Info -->
                    <div v-if="expense.is_recurring" class="bg-blue-50 rounded-xl border border-blue-200 p-6">
                        <h2 class="text-lg font-semibold text-blue-900 mb-2">Recurring Expense</h2>
                        <p class="text-sm text-blue-700">
                            This expense recurs <span class="font-medium">{{ expense.recurring_frequency }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
