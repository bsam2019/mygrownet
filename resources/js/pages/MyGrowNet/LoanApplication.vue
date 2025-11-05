<template>
    <Head title="Loan Application" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Member Loan Application</h1>
                    <p class="mt-1 text-sm text-gray-600">Apply for a short-term loan to support your business growth</p>
                </div>

                <!-- Loan Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-sm font-medium text-gray-500">Loan Limit</div>
                        <div class="mt-2 text-2xl font-bold text-gray-900">
                            K{{ Number(loanLimit).toFixed(2) }}
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-sm font-medium text-gray-500">Current Balance</div>
                        <div class="mt-2 text-2xl font-bold" :class="loanBalance > 0 ? 'text-red-600' : 'text-green-600'">
                            K{{ Number(loanBalance).toFixed(2) }}
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-sm font-medium text-gray-500">Available Credit</div>
                        <div class="mt-2 text-2xl font-bold text-blue-600">
                            K{{ Number(availableCredit).toFixed(2) }}
                        </div>
                    </div>
                </div>

                <!-- Active Loan Banner -->
                <div v-if="hasActiveLoan" class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-700">
                                You have an active loan of <strong>K{{ Number(loanBalance).toFixed(2) }}</strong>. 
                                You can apply for additional credit up to your available limit.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Eligibility Check -->
                <div v-if="!eligibility.eligible" class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Not Eligible</h3>
                            <p class="mt-1 text-sm text-red-700">{{ eligibility.reason }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Applications -->
                <div v-if="pendingApplications && pendingApplications.length > 0" class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800">Pending Application</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <div v-for="app in pendingApplications" :key="app.id" class="mt-1">
                                    <strong>K{{ Number(app.amount).toFixed(2) }}</strong> - Submitted {{ formatDate(app.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Form -->
                <div v-if="eligibility.eligible && (!pendingApplications || pendingApplications.length === 0)" class="bg-white rounded-lg shadow">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Apply for a Loan</h2>
                    </div>

                    <form @submit.prevent="submitApplication" class="p-6 space-y-6">
                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Loan Amount (K) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="form.amount"
                                type="number"
                                min="100"
                                :max="availableCredit"
                                step="50"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="500"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Min: K100 | Max: K{{ Number(availableCredit).toFixed(2) }} (your available credit)
                            </p>
                            <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                        </div>

                        <!-- Repayment Plan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Repayment Plan <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.repayment_plan"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="30_days">30 Days (10% monthly deduction)</option>
                                <option value="60_days">60 Days (5% monthly deduction)</option>
                                <option value="90_days">90 Days (3.33% monthly deduction)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Automatic repayment from your monthly earnings
                            </p>
                        </div>

                        <!-- Purpose -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Purpose of Loan <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="form.purpose"
                                rows="4"
                                required
                                minlength="20"
                                maxlength="500"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                placeholder="Explain how you will use this loan (minimum 20 characters)..."
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500 text-right">
                                {{ form.purpose.length }}/500 characters
                            </p>
                            <p v-if="errors.purpose" class="mt-1 text-sm text-red-600">{{ errors.purpose }}</p>
                        </div>

                        <!-- Terms -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Loan Terms</h3>
                            <ul class="text-xs text-gray-700 space-y-1">
                                <li>• Interest-free loan for premium members</li>
                                <li>• Automatic repayment from monthly earnings</li>
                                <li>• Early repayment allowed without penalty</li>
                                <li>• Loan must be used for business purposes</li>
                                <li>• Approval typically within 24-48 hours</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <Link
                                :href="route('mygrownet.wallet.index')"
                                class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="processing"
                                class="px-6 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ processing ? 'Submitting...' : 'Submit Application' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Loan History -->
                <div v-if="loanHistory && loanHistory.length > 0" class="mt-6 bg-white rounded-lg shadow">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Application History</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="app in loanHistory" :key="app.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(app.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        K{{ Number(app.amount).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusClass(app.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ app.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ app.rejection_reason || app.admin_notes || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import MemberLayout from '@/Layouts/MemberLayout.vue';

interface Props {
    hasActiveLoan: boolean;
    loanBalance: number;
    loanLimit: number;
    availableCredit: number;
    totalIssued: number;
    totalRepaid: number;
    pendingApplications: any[];
    loanHistory: any[];
    eligibility: {
        eligible: boolean;
        reason: string | null;
    };
}

const props = defineProps<Props>();
const page = usePage();
const processing = ref(false);

const form = useForm({
    amount: 500,
    purpose: '',
    repayment_plan: '30_days',
});

const errors = ref<Record<string, string>>({});

// Debounce timer
let lastSubmitTime = 0;

const submitApplication = () => {
    // Clear previous errors
    errors.value = {};
    
    // Prevent double submission
    if (processing.value) {
        return;
    }
    
    // Prevent rapid submissions
    const now = Date.now();
    if (now - lastSubmitTime < 3000) {
        errors.value.amount = 'Please wait a moment before submitting again.';
        return;
    }
    lastSubmitTime = now;
    
    processing.value = true;
    
    form.post(route('mygrownet.loans.apply'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            setTimeout(() => {
                processing.value = false;
            }, 2000);
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>
