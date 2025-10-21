<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { CheckCircleIcon, ClockIcon, XCircleIcon, PlusIcon } from 'lucide-vue-next';

interface Payment {
    id: number;
    amount: number;
    payment_method: string;
    payment_reference: string;
    phone_number: string;
    account_name: string;
    payment_type: string;
    status: 'pending' | 'verified' | 'rejected';
    notes?: string;
    admin_notes?: string;
    verified_at?: string;
    verified_by?: string;
    created_at: string;
}

const props = defineProps<{
    payments: Payment[];
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
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

const getStatusColor = (status: string) => {
    switch (status) {
        case 'verified':
            return 'text-green-700 bg-green-50 border-green-200';
        case 'pending':
            return 'text-yellow-700 bg-yellow-50 border-yellow-200';
        case 'rejected':
            return 'text-red-700 bg-red-50 border-red-200';
        default:
            return 'text-gray-700 bg-gray-50 border-gray-200';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'verified':
            return CheckCircleIcon;
        case 'pending':
            return ClockIcon;
        case 'rejected':
            return XCircleIcon;
        default:
            return ClockIcon;
    }
};

const getPaymentMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        mtn_momo: 'MTN MoMo',
        airtel_money: 'Airtel Money',
        bank_transfer: 'Bank Transfer',
    };
    return labels[method] || method;
};

const getPaymentTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        wallet_topup: 'Wallet Top-Up',
        subscription: 'Monthly Subscription',
        workshop: 'Workshop/Training',
        product: 'Product Purchase',
        learning_pack: 'Learning Pack',
        coaching: 'Coaching/Mentorship',
        upgrade: 'Tier Upgrade',
        other: 'Other',
    };
    return labels[type] || type;
};
</script>

<template>
    <MemberLayout>
        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Payment History</h1>
                        <p class="mt-2 text-sm text-gray-600">View all your submitted payments and their verification status</p>
                    </div>
                    <Link
                        :href="route('mygrownet.payments.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <PlusIcon class="h-5 w-5" />
                        <span class="hidden sm:inline">Submit Payment</span>
                    </Link>
                </div>

                <!-- Payments List -->
                <div v-if="payments.length > 0" class="space-y-4">
                    <div
                        v-for="payment in payments"
                        :key="payment.id"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <!-- Payment Details -->
                            <div class="flex-1">
                                <div class="flex items-start gap-3">
                                    <component
                                        :is="getStatusIcon(payment.status)"
                                        class="h-6 w-6 mt-1"
                                        :class="{
                                            'text-green-600': payment.status === 'verified',
                                            'text-yellow-600': payment.status === 'pending',
                                            'text-red-600': payment.status === 'rejected',
                                        }"
                                    />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ formatCurrency(payment.amount) }}
                                            </h3>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full border capitalize"
                                                :class="getStatusColor(payment.status)"
                                            >
                                                {{ payment.status }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ getPaymentTypeLabel(payment.payment_type) }}
                                        </p>
                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                            <div>
                                                <span class="text-gray-500">Method:</span>
                                                <span class="ml-2 font-medium text-gray-900">
                                                    {{ getPaymentMethodLabel(payment.payment_method) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Reference:</span>
                                                <span class="ml-2 font-medium text-gray-900">
                                                    {{ payment.payment_reference }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Phone:</span>
                                                <span class="ml-2 font-medium text-gray-900">
                                                    {{ payment.phone_number }}
                                                </span>
                                            </div>
                                        </div>
                                        <div v-if="payment.notes" class="mt-3 text-sm">
                                            <span class="text-gray-500">Notes:</span>
                                            <p class="mt-1 text-gray-700">{{ payment.notes }}</p>
                                        </div>
                                        <div v-if="payment.admin_notes" class="mt-3 p-3 bg-blue-50 rounded-lg text-sm">
                                            <span class="font-medium text-blue-900">Admin Notes:</span>
                                            <p class="mt-1 text-blue-800">{{ payment.admin_notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Info -->
                            <div class="text-sm text-gray-500 sm:text-right">
                                <p>Submitted</p>
                                <p class="font-medium text-gray-900">{{ formatDate(payment.created_at) }}</p>
                                <p v-if="payment.verified_at" class="mt-2">
                                    Verified
                                </p>
                                <p v-if="payment.verified_at" class="font-medium text-gray-900">
                                    {{ formatDate(payment.verified_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <ClockIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Payment History</h3>
                    <p class="text-gray-600 mb-6">You haven't submitted any payments yet.</p>
                    <Link
                        :href="route('mygrownet.payments.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Submit Your First Payment
                    </Link>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
