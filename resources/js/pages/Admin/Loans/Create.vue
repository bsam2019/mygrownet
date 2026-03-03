<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    ArrowLeftIcon, 
    UserCircleIcon, 
    BanknotesIcon, 
    CalculatorIcon,
    ClockIcon,
    DocumentTextIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Props {
    users: User[];
    preselectedUserId?: number;
}

const props = defineProps<Props>();

const form = useForm({
    user_id: props.preselectedUserId || null as number | null,
    principal_amount: '',
    interest_rate: '15',
    term_months: '12',
    loan_type: 'member_loan',
    purpose: '',
    notes: '',
});

const loanTypes = [
    { value: 'member_loan', label: 'Member Loan' },
    { value: 'business_loan', label: 'Business Loan' },
    { value: 'emergency_loan', label: 'Emergency Loan' },
];

const termOptions = [
    { value: '3', label: '3 Months' },
    { value: '6', label: '6 Months' },
    { value: '12', label: '12 Months' },
    { value: '18', label: '18 Months' },
    { value: '24', label: '24 Months' },
    { value: '36', label: '36 Months' },
];

const totalAmount = computed(() => {
    const principal = parseFloat(form.principal_amount) || 0;
    const rate = parseFloat(form.interest_rate) || 0;
    const months = parseInt(form.term_months) || 0;
    
    if (principal && rate && months) {
        const monthlyRate = rate / 100 / 12;
        const interest = principal * monthlyRate * months;
        return principal + interest;
    }
    return 0;
});

const monthlyPayment = computed(() => {
    const months = parseInt(form.term_months) || 0;
    if (totalAmount.value && months) {
        return totalAmount.value / months;
    }
    return 0;
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const submit = () => {
    form.post(route('admin.platform-loans.store'), {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};
</script>

<template>
    <Head title="Create Loan" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.platform-loans.index')"
                        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        Back to Loans
                    </Link>
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        Create Formal Loan
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Issue a formal loan with interest, terms, and repayment schedule
                    </p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <InformationCircleIcon class="h-5 w-5 text-blue-400" aria-hidden="true" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Formal Loan vs Quick Advance
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p class="mb-2">
                                    <strong>Formal Loans</strong> (this form): Professional loans with interest, amortization schedules, and balance sheet tracking. Best for business loans > K1,000.
                                </p>
                                <p>
                                    <strong>Quick Advances</strong> (User Actions menu): Simple wallet credits with automatic 100% repayment from earnings. No interest. Best for emergency assistance < K1,000.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Borrower Selection -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Borrower Information</h3>
                        
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">
                                Select Member <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.user_id"
                                id="user_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                :class="{ 'border-red-300': form.errors.user_id }"
                                required
                            >
                                <option :value="null">Select a member...</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                            <p v-if="form.errors.user_id" class="mt-2 text-sm text-red-600">
                                {{ form.errors.user_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Loan Details -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Loan Details</h3>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="loan_type" class="block text-sm font-medium text-gray-700">
                                    Loan Type <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.loan_type"
                                    id="loan_type"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                    required
                                >
                                    <option v-for="type in loanTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="principal_amount" class="block text-sm font-medium text-gray-700">
                                    Principal Amount (ZMW) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.principal_amount"
                                    type="number"
                                    id="principal_amount"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-300': form.errors.principal_amount }"
                                    required
                                />
                                <p v-if="form.errors.principal_amount" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.principal_amount }}
                                </p>
                            </div>

                            <div>
                                <label for="interest_rate" class="block text-sm font-medium text-gray-700">
                                    Interest Rate (% per year) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.interest_rate"
                                    type="number"
                                    id="interest_rate"
                                    step="0.1"
                                    min="0"
                                    max="100"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-300': form.errors.interest_rate }"
                                    required
                                />
                                <p v-if="form.errors.interest_rate" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.interest_rate }}
                                </p>
                            </div>

                            <div>
                                <label for="term_months" class="block text-sm font-medium text-gray-700">
                                    Loan Term <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.term_months"
                                    id="term_months"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                    required
                                >
                                    <option v-for="term in termOptions" :key="term.value" :value="term.value">
                                        {{ term.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="purpose" class="block text-sm font-medium text-gray-700">
                                    Purpose
                                </label>
                                <input
                                    v-model="form.purpose"
                                    type="text"
                                    id="purpose"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="e.g., Business expansion, Emergency medical"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">
                                    Notes
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    id="notes"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Additional notes or conditions"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Loan Summary -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Loan Summary</h3>
                        
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Principal Amount</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ formatCurrency(parseFloat(form.principal_amount) || 0) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Amount (with interest)</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ formatCurrency(totalAmount) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Monthly Payment</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ formatCurrency(monthlyPayment) }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="route('admin.platform-loans.index')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Loan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
