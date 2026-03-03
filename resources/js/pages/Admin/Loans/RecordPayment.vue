<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Loan {
    id: number;
    loan_number: string;
    user: {
        name: string;
        email: string;
    };
    outstanding_balance: number;
    monthly_payment: number;
    next_payment_date: string | null;
}

interface Props {
    loan: Loan;
}

const props = defineProps<Props>();

const form = useForm({
    payment_amount: props.loan.monthly_payment.toString(),
    payment_method: 'wallet',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const paymentMethods = [
    { value: 'wallet', label: 'Wallet' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'mobile_money', label: 'Mobile Money' },
    { value: 'cash', label: 'Cash' },
];

// Calculate principal and interest portions
const principalPortion = computed(() => {
    const amount = parseFloat(form.payment_amount) || 0;
    const outstanding = props.loan.outstanding_balance;
    
    // Simple calculation - in reality, this should come from the backend
    // based on the amortization schedule
    const interestRate = 0.15 / 12; // Assuming 15% annual rate
    const interest = outstanding * interestRate;
    const principal = Math.max(0, amount - interest);
    
    return principal;
});

const interestPortion = computed(() => {
    const amount = parseFloat(form.payment_amount) || 0;
    return Math.max(0, amount - principalPortion.value);
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const submit = () => {
    form.post(route('admin.platform-loans.payment.store', props.loan.id), {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};
</script>

<template>
    <Head title="Record Payment" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.platform-loans.show', loan.id)"
                        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        Back to Loan Details
                    </Link>
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        Record Payment
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Loan {{ loan.loan_number }} - {{ loan.user.name }}
                    </p>
                </div>

                <!-- Loan Summary -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Loan Summary</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Outstanding Balance</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                {{ formatCurrency(loan.outstanding_balance) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Monthly Payment</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                {{ formatCurrency(loan.monthly_payment) }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Payment Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Details</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label for="payment_amount" class="block text-sm font-medium text-gray-700">
                                    Payment Amount (ZMW) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.payment_amount"
                                    type="number"
                                    id="payment_amount"
                                    step="0.01"
                                    min="0"
                                    :max="loan.outstanding_balance"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-300': form.errors.payment_amount }"
                                    required
                                />
                                <p v-if="form.errors.payment_amount" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.payment_amount }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    Maximum: {{ formatCurrency(loan.outstanding_balance) }}
                                </p>
                            </div>

                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700">
                                    Payment Method <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.payment_method"
                                    id="payment_method"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                    required
                                >
                                    <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                        {{ method.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="payment_date" class="block text-sm font-medium text-gray-700">
                                    Payment Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.payment_date"
                                    type="date"
                                    id="payment_date"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">
                                    Notes
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    id="notes"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Optional payment notes"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Payment Breakdown -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Breakdown</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Payment Amount</dt>
                                <dd class="text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(parseFloat(form.payment_amount) || 0) }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Interest Portion</dt>
                                <dd class="text-sm text-gray-900">
                                    {{ formatCurrency(interestPortion) }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Principal Portion</dt>
                                <dd class="text-sm text-gray-900">
                                    {{ formatCurrency(principalPortion) }}
                                </dd>
                            </div>
                            <div class="pt-3 border-t border-green-300 flex justify-between">
                                <dt class="text-sm font-medium text-gray-900">New Outstanding Balance</dt>
                                <dd class="text-sm font-bold text-gray-900">
                                    {{ formatCurrency(loan.outstanding_balance - principalPortion) }}
                                </dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs text-gray-500">
                            * Interest is calculated first, then the remainder is applied to principal
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="route('admin.platform-loans.show', loan.id)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Recording...' : 'Record Payment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
